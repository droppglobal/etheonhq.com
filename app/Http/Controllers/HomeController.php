<?php

namespace App\Http\Controllers;
use App\Models\Agreement;
use App\Models\Contact;
use App\Models\Custom;
use App\Models\FAQ;
use App\Models\HomePage;
use App\Models\InvoiceItem;
use App\Models\NoticeBoard;
use App\Models\PackageTransaction;
use App\Models\Page;
use App\Models\Subscription;
use App\Models\Support;
use App\Models\User;
use Carbon\Carbon;

use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Maintainer;
use App\Models\MaintenanceRequest;

use App\Models\Property;
use App\Models\PropertyUnit;

use App\Models\Tenant;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
         if (\Auth::check()) {
            if (\Auth::user()->type == 'super admin') {
                $result['totalOrganization'] = User::where('type', 'owner')->count();
                $result['totalSubscription'] = Subscription::count();
                $result['totalTransaction'] = PackageTransaction::count();
                $result['totalIncome'] = PackageTransaction::sum('amount');
                $result['totalNote'] = NoticeBoard::where('parent_id', parentId())->count();
                $result['totalContact'] = Contact::where('parent_id', parentId())->count();

                $result['organizationByMonth'] = $this->organizationByMonth();
                $result['paymentByMonth'] = $this->paymentByMonth();
                $extra = \App\Support\DashboardData::superAdmin();
                return view('dashboard.super_admin', compact('result', 'extra'));
            } else {
                if (\Auth::user()->type == 'tenant') {
                    $tenant = Tenant::where('user_id', \Auth::user()->id)->first();

                    if (!empty($tenant)) {
                        $result['unit'] = PropertyUnit::find($tenant->unit);
                        $result['property'] = Property::find($tenant->property);
                        $invoices = Invoice::where('parent_id', $tenant->parent_id)
                            ->where('tenant', $tenant->id)
                            ->whereIn('status', ['unpaid', 'partial_paid'])
                            ->get();

                        $result['totalPendingPayment'] = $invoices->sum(function ($invoice) {
                            return $invoice->getInvoiceDueAmount();
                        });
                        $result['overdueInvoices'] = Invoice::where('parent_id', $tenant->parent_id)
                            ->where('tenant', $tenant->id)
                            ->whereIn('status', ['unpaid', 'partial_paid'])
                            ->whereRaw('DATE_SUB(end_date, INTERVAL 1 DAY) <= CURDATE()')
                            ->orderBy('end_date', 'desc')
                            ->get();

                        $result['pendingMaintenance'] = MaintenanceRequest::where('tenant_id', $tenant->id)
                            ->where('status', 'pending')
                            ->count();
                        $result['maintenanceList'] = MaintenanceRequest::where('tenant_id', $tenant->id)
                            ->latest()
                            ->get();
                        $result['agreement'] = Agreement::where('tenant_id', $tenant->id)
                            ->latest()
                            ->first();
                    } else {
                        $result['unit'] = '';
                        $result['property'] = '';
                        $result['pendingPayment'] = 0;
                        $result['overdueInvoices'] = collect();
                        $result['pendingMaintenance'] = 0;
                        $result['maintenanceList'] = collect();
                        $result['agreement'] = null;
                    }

                    $extra = !empty($tenant) ? \App\Support\DashboardData::tenant($tenant) : null;
                    return view('dashboard.tenant', compact('result', 'tenant', 'extra'));
                }

                if (\Auth::user()->type == 'landlord') {
                    [$from, $to, $month] = \App\Support\LandlordStatement::monthRange(request('month'));
                    $statement = \App\Support\LandlordStatement::build(\Auth::user()->id, parentId(), $from, $to);
                    $properties = Property::where('parent_id', parentId())->where('landlord_id', \Auth::user()->id)->get();
                    $extra = \App\Support\DashboardData::landlord(\Auth::user(), parentId());
                    return view('dashboard.landlord', compact('statement', 'month', 'properties', 'extra'));
                }
                if (\Auth::user()->type == 'maintainer') {
                    $maintainer = Maintainer::where('user_id', \Auth::user()->id)->first();
                    $result['totalRequest'] = MaintenanceRequest::where('maintainer_id', \Auth::user()->id)->count();
                    $result['todayRequest'] = MaintenanceRequest::whereDate('request_date', '=', date('Y-m-d'))->where('maintainer_id', \Auth::user()->id)->count();
                    $result['pendingRequest'] = MaintenanceRequest::where('status', 'pending')->where('maintainer_id', \Auth::user()->id)->count();

                    $extra = \App\Support\DashboardData::maintainer(\Auth::user());
                    return view('dashboard.maintainer', compact('result', 'maintainer', 'extra'));
                }

                $result['totalProperty'] = Property::where('parent_id', parentId())->count();
                $result['totalUnit'] = PropertyUnit::where('parent_id', parentId())->count();
                $result['totalTenant'] = Tenant::where('parent_id', parentId())->count();
                $result['totalIncome'] = InvoicePayment::where('parent_id', parentId())->whereMonth('payment_date', Carbon::now()->month)->whereYear('payment_date', Carbon::now()->year)->sum('amount');
                $result['totalExpense'] = Expense::where('parent_id', parentId())->whereMonth('date', Carbon::now()->month)->whereYear('date', Carbon::now()->year)->sum('amount');
                // amount still owed on unpaid and partly paid invoices
                $openInvoices = Invoice::where('parent_id', parentId())
                    ->whereIn('status', ['unpaid', 'partial_paid'])
                    ->with(['types', 'payments'])
                    ->get();
                $result['totalPendingPayment'] = $openInvoices->sum(function ($invoice) {
                    return max(0, $invoice->getInvoiceDueAmount());
                });

                // rent collection for invoices of the current month
                $monthInvoices = Invoice::where('parent_id', parentId())
                    ->whereMonth('invoice_month', Carbon::now()->month)
                    ->whereYear('invoice_month', Carbon::now()->year)
                    ->with(['types', 'payments'])
                    ->get();
                $result['monthInvoiced'] = $monthInvoices->sum(function ($invoice) {
                    return $invoice->getInvoiceSubTotalAmount();
                });
                $result['monthCollected'] = $monthInvoices->sum(function ($invoice) {
                    return $invoice->payments->sum('amount');
                });
                $result['collectionRate'] = $result['monthInvoiced'] > 0
                    ? round(min(100, $result['monthCollected'] / $result['monthInvoiced'] * 100), 1)
                    : 0;

                $depositRows = \App\Models\DepositTransaction::where('parent_id', parentId())->get();
                $result['depositsHeld'] = $depositRows->where('type', 'received')->sum('amount')
                    - $depositRows->whereIn('type', ['deduction', 'refund'])->sum('amount');

                $result['leasesEnding'] = Tenant::where('parent_id', parentId())
                    ->whereNull('exit_date')
                    ->whereBetween('lease_end_date', [Carbon::today()->toDateString(), Carbon::today()->addDays(30)->toDateString()])
                    ->count();

                $result['newLeads'] = \App\Models\Lead::where('parent_id', parentId())->where('created_at', '>=', Carbon::now()->subDays(30))->count();
                $result['followUpsDue'] = \App\Models\Lead::where('parent_id', parentId())
                    ->whereIn('status', \App\Models\Lead::openStatuses())
                    ->whereDate('follow_up_date', '<=', Carbon::today())
                    ->count();
                $result['pendingApplications'] = \App\Models\RentalApplication::where('parent_id', parentId())->where('status', 'pending')->count();

                $result['documentsExpiring'] = \App\Models\TenantDocument::where('parent_id', parentId())
                    ->whereNotNull('expiry_date')
                    ->whereDate('expiry_date', '<=', Carbon::today()->addDays((int) (settings()['document_expiry_reminder_days'] ?? 30)))
                    ->count();
                $result['totalPendingRequest'] = MaintenanceRequest::where('parent_id', parentId())->where('status', 'pending')->count();

                $result['incomeExpenseByMonth'] = $this->incomeByMonth();
                $result['settings'] = settings();
                $totalUnits = PropertyUnit::where('parent_id', parentId())->count();

                $vacantUnits = PropertyUnit::where('parent_id', parentId())
                    ->where('is_occupied', 0)
                    ->count();

                $result['vacancyRate'] = $totalUnits > 0
                    ? round(($vacantUnits / $totalUnits) * 100, 2)
                    : 0;

                $result['revenueAnalytics'] = $this->revenueAnalytics(new Request(['type' => 'monthly']))->getData(true);
                $properties = Property::where('parent_id', parentId())->pluck('name', 'id');
                $result['invoiceDue'] = Invoice::where('parent_id', parentId())
                    ->whereIn('status', ['unpaid', 'partial_paid'])
                    ->whereRaw('DATE_SUB(end_date, INTERVAL 1 DAY) <= CURDATE()')
                    ->orderBy('end_date', 'desc')
                    ->get();
                $maintenanceRequests = MaintenanceRequest::where('parent_id', parentId())
                    ->where('status', 'pending')
                    ->whereNull('maintainer_id')
                    ->get();
                $extra = \App\Support\DashboardData::owner(parentId(), \Auth::user());
                return view('dashboard.index', compact('result', 'properties', 'maintenanceRequests', 'extra'));
            }
        } else {
            if (!file_exists(setup())) {
                header('location:install');
                die;
            } else {
                $landingPage = getSettingsValByName('landing_page');
                if ($landingPage == 'on') {
                    $subscriptions = Subscription::get();
                    $menus = Page::where('enabled', 1)->get();
                    $FAQs = FAQ::where('enabled', 1)->get();
                    return view('layouts.landing', compact('subscriptions', 'menus', 'FAQs'));
                } else {
                    return redirect()->route('login');
                }
            }
        }
    }

    public function organizationByMonth()
    {
        $start = strtotime(date('Y-01'));
        $end = strtotime(date('Y-12'));

        $currentdate = $start;

        $organization = [];
        while ($currentdate <= $end) {
            $organization['label'][] = date('M-Y', $currentdate);

            $month = date('m', $currentdate);
            $year = date('Y', $currentdate);
            $organization['data'][] = User::where('type', 'owner')->whereMonth('created_at', $month)->whereYear('created_at', $year)->count();
            $currentdate = strtotime('+1 month', $currentdate);
        }

        return $organization;
    }

    public function paymentByMonth()
    {
        $start = strtotime(date('Y-01'));
        $end = strtotime(date('Y-12'));

        $currentdate = $start;

        $payment = [];
        while ($currentdate <= $end) {
            $payment['label'][] = date('M-Y', $currentdate);

            $month = date('m', $currentdate);
            $year = date('Y', $currentdate);
            $payment['data'][] = PackageTransaction::whereMonth('created_at', $month)->whereYear('created_at', $year)->sum('amount');
            $currentdate = strtotime('+1 month', $currentdate);
        }

        return $payment;
    }

    public function incomeByMonth()
    {
        $start = strtotime(date('Y-01'));
        $end = strtotime(date('Y-12'));

        $currentdate = $start;

        $payment = [];
        while ($currentdate <= $end) {
            $payment['label'][] = date('M-Y', $currentdate);
            $month = date('m', $currentdate);
            $year = date('Y', $currentdate);
            $payment['income'][] = InvoicePayment::where('parent_id', parentId())->whereMonth('payment_date', $month)->whereYear('payment_date', $year)->sum('amount');
            $payment['expense'][] = Expense::where('parent_id', parentId())->whereMonth('date', $month)->whereYear('date', $year)->sum('amount');
            $currentdate = strtotime('+1 month', $currentdate);
        }

        return $payment;
    }

    public function search(Request $request)
    {
        $search = $request->q;

        if (!$search) {
            return response()->json([]);
        }

        $tenants = User::where('parent_id', parentId())
            ->where('type', 'tenant')
            ->with('tenants')
            ->where(function ($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%")
                    ->orWhereRaw("CONCAT(first_name,' ',last_name) LIKE ?", ["%{$search}%"]);
            })
            ->get()
            ->map(function ($user) {
                $tenant = $user->tenants;

                if (!$tenant)
                    return null;

                if (!empty($tenant->property)) {
                    $property = Property::find($tenant->property);
                    $propertyName = optional($property)->name;
                }

                return [
                    'type' => 'tenant',
                    'label' => $user->first_name . ' ' . $user->last_name,
                    'sub' => 'Tenant',
                    'url' => route('tenant.show', encrypt($tenant->id)),
                ];
            })
            ->filter()
            ->values();

        $properties = Property::where('parent_id', parentId())
            ->where('name', 'LIKE', "%{$search}%")
            ->get()
            ->map(function ($property) {
                return [
                    'type' => 'property',
                    'label' => $property->name,
                    'sub' => 'Property',
                    'url' => route('property.show', encrypt($property->id)),
                ];
            })->filter()
            ->values();

        $maintainers = User::where('parent_id', parentId())
            ->where('type', 'maintainer')
            ->where(function ($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%")
                    ->orWhereRaw("CONCAT(first_name,' ',last_name) LIKE ?", ["%{$search}%"]);
                ;
            })
            ->get()
            ->map(function ($user) {
                return [
                    'type' => 'maintainer',
                    'label' => $user->first_name . ' ' . $user->last_name,
                    'sub' => 'Maintainer',
                    'url' => route('maintainer.show', encrypt($user->id)),
                ];
            })->filter()
            ->values();

        return response()->json(
            collect($tenants)
                ->merge(collect($properties))
                ->merge(collect($maintainers))
                ->filter()
                ->values()
                ->toArray()
        );
    }
    public function revenueAnalytics(Request $request)
    {
        $type = $request->type ?? 'monthly';

        if ($type == 'monthly') {
            $daysInMonth = now()->daysInMonth;
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $date = now()->startOfMonth()->addDays($i - 1);
                $data['label'][] = $date->format('d');
                $data['revenue'][] = InvoicePayment::where('parent_id', parentId())
                    ->whereDate('payment_date', $date)
                    ->sum('amount');
            }

        } elseif ($type == 'yearly') {
            for ($i = 1; $i <= 12; $i++) {
                $data['label'][] = date('M', mktime(0, 0, 0, $i, 1));
                $data['revenue'][] = InvoicePayment::where('parent_id', parentId())
                    ->whereMonth('payment_date', $i)
                    ->whereYear('payment_date', now()->year)
                    ->sum('amount');
            }

        } elseif ($type == 'custom') {
            $start = Carbon::parse($request->start)->startOfDay();
            $end = Carbon::parse($request->end)->endOfDay();
            $diffInDays = $start->diffInDays($end);

            if ($diffInDays <= 31) {
                // Day by day
                $period = CarbonPeriod::create($start, $end);
                foreach ($period as $date) {
                    $data['label'][] = $date->format('d M');
                    $data['revenue'][] = InvoicePayment::where('parent_id', parentId())
                        ->whereDate('payment_date', $date)
                        ->sum('amount');
                }

            } elseif ($diffInDays <= 90) {
                $current = $start->copy();
                while ($current->lte($end)) {
                    $weekEnd = $current->copy()->addDays(6)->gt($end)
                        ? $end->copy()
                        : $current->copy()->addDays(6);

                    $data['label'][] = $current->format('d M') . ' - ' . $weekEnd->format('d M');
                    $data['revenue'][] = InvoicePayment::where('parent_id', parentId())
                        ->whereBetween('payment_date', [
                            $current->toDateString(),
                            $weekEnd->toDateString()
                        ])
                        ->sum('amount');

                    $current->addDays(7);
                }

            } else {
                $current = $start->copy()->startOfMonth();
                while ($current->lte($end)) {
                    $monthStart = $current->copy()->startOfMonth()->lt($start)
                        ? $start->copy()
                        : $current->copy()->startOfMonth();

                    $monthEnd = $current->copy()->endOfMonth()->gt($end)
                        ? $end->copy()
                        : $current->copy()->endOfMonth();

                    $data['label'][] = $current->format('M Y');
                    $data['revenue'][] = InvoicePayment::where('parent_id', parentId())
                        ->whereBetween('payment_date', [
                            $monthStart->toDateString(),
                            $monthEnd->toDateString()
                        ])
                        ->sum('amount');

                    $current->addMonth();
                }
            }
        }

        return response()->json($data);
    }
    public function propertyUnitAnalytics(Request $request)
    {
        $propertyId = $request->property_id;

        $query = PropertyUnit::where('parent_id', parentId());

        if ($propertyId) {
            $query->where('property_id', $propertyId);
        }

        $occupied = (clone $query)->where('is_occupied', 1)->count();
        $vacant = (clone $query)->where('is_occupied', 0)->count();

        return response()->json([
            'occupied' => $occupied,
            'vacant' => $vacant,
        ]);
    }
}
