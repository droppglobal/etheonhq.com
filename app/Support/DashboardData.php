<?php

namespace App\Support;

use App\Models\DepositTransaction;
use App\Models\FacilityBooking;
use App\Models\Inspection;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Lead;
use App\Models\LeadViewing;
use App\Models\MaintenanceRequest;
use App\Models\MaintenanceSchedule;
use App\Models\PackageTransaction;
use App\Models\ParkingAssignment;
use App\Models\Poll;
use App\Models\PollVote;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Models\VisitorPass;
use Carbon\Carbon;

/**
 * Extra dashboard figures and lists for each role. Every query is scoped to the account
 * (or, for tenants and maintainers, to their own records).
 */
class DashboardData
{
    // ---------------------------------------------------------------- super admin

    public static function superAdmin()
    {
        $today = Carbon::today();
        $owners = User::where('type', 'owner');

        // server problems the platform operator should fix (installer requirements can drift after hosting changes)
        $d['serverIssues'] = [];
        foreach (['gd' => __('PHP GD extension is missing: agreement PDFs cannot include signatures and images.'),
                  'curl' => __('PHP cURL extension is missing: payments, AI and messaging cannot connect.'),
                  'openssl' => __('PHP OpenSSL extension is missing: payments and push notifications will not work.')] as $ext => $message) {
            if (!extension_loaded($ext)) {
                $d['serverIssues'][] = $message;
            }
        }
        if (!str_starts_with(url('/'), 'https://') && !in_array(parse_url(url('/'), PHP_URL_HOST), ['localhost', '127.0.0.1'])) {
            $d['serverIssues'][] = __('The site is not served over HTTPS: browsers only allow push notifications and installing the app on HTTPS.');
        }

        $d['activeOwners'] = (clone $owners)->where(function ($q) use ($today) {
            $q->whereNull('subscription_expire_date')->orWhereDate('subscription_expire_date', '>=', $today);
        })->count();
        $d['expiredOwners'] = (clone $owners)->whereNotNull('subscription_expire_date')->whereDate('subscription_expire_date', '<', $today)->count();
        $d['expiringSoon'] = (clone $owners)->whereNotNull('subscription_expire_date')
            ->whereBetween('subscription_expire_date', [$today->toDateString(), $today->copy()->addDays(7)->toDateString()])
            ->orderBy('subscription_expire_date')->get();
        $d['onTrial'] = PackageTransaction::where('free_trial', 1)
            ->whereIn('user_id', (clone $owners)->whereDate('subscription_expire_date', '>=', $today)->pluck('id'))->distinct('user_id')->count('user_id');
        $d['pendingTransfers'] = PackageTransaction::where('payment_status', 'Pending')->count();
        $d['revenueThisMonth'] = PackageTransaction::whereIn('payment_status', ['Success', 'succeeded'])
            ->whereMonth('created_at', $today->month)->whereYear('created_at', $today->year)->sum('amount');
        $d['newOwnersThisMonth'] = (clone $owners)->whereMonth('created_at', $today->month)->whereYear('created_at', $today->year)->count();

        $d['platformProperties'] = Property::count();
        $d['platformUnits'] = PropertyUnit::count();
        $d['platformTenants'] = Tenant::whereNull('exit_date')->count();

        $plans = Subscription::pluck('title', 'id');
        $d['ownersPerPlan'] = (clone $owners)->selectRaw('subscription, COUNT(*) as total')->groupBy('subscription')->pluck('total', 'subscription')
            ->mapWithKeys(function ($total, $planId) use ($plans) {
                return [($plans[$planId] ?? __('No plan')) => $total];
            })->sortDesc();
        $d['recentOwners'] = (clone $owners)->orderBy('id', 'desc')->limit(5)->get();
        $d['plans'] = $plans;
        return $d;
    }

    // ---------------------------------------------------------------- owner / manager

    public static function owner($parentId, User $user)
    {
        $today = Carbon::today();
        $week = $today->copy()->addDays(7)->toDateString();
        $upcoming = collect();

        if ($user->can('manage lead')) {
            foreach (LeadViewing::where('parent_id', $parentId)->whereIn('status', ['requested', 'scheduled'])
                ->whereBetween('viewing_date', [$today->toDateString(), $week])->get() as $v) {
                $upcoming->push(['date' => $v->viewing_date, 'time' => $v->viewing_time, 'icon' => 'eye', 'color' => 'info',
                    'title' => __('Viewing') . ': ' . optional($v->lead)->name, 'sub' => optional($v->properties)->name,
                    'url' => route('lead.show', $v->lead_id)]);
            }
        }
        if ($user->can('manage facility')) {
            foreach (FacilityBooking::where('parent_id', $parentId)->whereIn('status', ['pending', 'approved'])
                ->whereBetween('booking_date', [$today->toDateString(), $week])->get() as $b) {
                $upcoming->push(['date' => $b->booking_date, 'time' => $b->start_time, 'icon' => 'calendar-event', 'color' => $b->status == 'pending' ? 'warning' : 'success',
                    'title' => optional($b->facility)->name . ($b->status == 'pending' ? ' (' . __('needs approval') . ')' : ''),
                    'sub' => optional(optional($b->tenants)->user)->name, 'url' => route('facility.index')]);
            }
        }
        if ($user->can('manage maintenance schedule')) {
            foreach (MaintenanceSchedule::where('parent_id', $parentId)->where('is_active', 1)->whereBetween('next_date', [$today->toDateString(), $week])->get() as $s) {
                $upcoming->push(['date' => $s->next_date, 'time' => null, 'icon' => 'tool', 'color' => 'secondary',
                    'title' => __('Preventive') . ': ' . $s->title, 'sub' => optional($s->properties)->name, 'url' => route('maintenance-schedule.index')]);
            }
        }
        if ($user->can('manage tenant')) {
            foreach (Tenant::where('parent_id', $parentId)->whereNull('exit_date')
                ->whereBetween('lease_end_date', [$today->toDateString(), $today->copy()->addDays(14)->toDateString()])->get() as $t) {
                $upcoming->push(['date' => $t->lease_end_date, 'time' => null, 'icon' => 'calendar-minus', 'color' => 'danger',
                    'title' => __('Lease ends') . ': ' . optional($t->user)->name, 'sub' => optional($t->properties)->name . ' / ' . optional($t->units)->name,
                    'url' => route('tenant.show', \Crypt::encrypt($t->id))]);
            }
        }
        $d['upcoming'] = $upcoming->sortBy(function ($item) {
            return $item['date'] . ' ' . ($item['time'] ?? '23:59');
        })->values()->take(12);

        $d['recentPayments'] = $user->can('manage invoice')
            ? InvoicePayment::join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
                ->where('invoices.parent_id', $parentId)->orderBy('invoice_payments.id', 'desc')->limit(6)
                ->select('invoice_payments.*', 'invoices.invoice_id as invoice_number', 'invoices.tenant as tenant_id')->get()
            : collect();

        if ($user->can('manage maintenance request')) {
            $open = MaintenanceRequest::where('parent_id', $parentId)->whereIn('status', ['pending', 'in_progress']);
            $d['openByPriority'] = (clone $open)->selectRaw("COALESCE(ai_priority, 'unrated') as p, COUNT(*) as total")->groupBy('p')->pluck('total', 'p');
            $oldest = (clone $open)->orderBy('request_date')->first();
            $d['oldestOpenDays'] = $oldest ? Carbon::parse($oldest->request_date)->diffInDays($today) : null;
            $d['unassigned'] = (clone $open)->where(function ($q) {
                $q->whereNull('maintainer_id')->orWhere('maintainer_id', 0);
            })->count();
        }

        if ($user->can('manage property')) {
            $d['occupancy'] = Property::where('parent_id', $parentId)->get()->map(function ($property) {
                $units = PropertyUnit::where('property_id', $property->id)->get();
                return ['name' => $property->name, 'units' => $units->count(), 'occupied' => $units->where('is_occupied', 1)->count()];
            })->filter(function ($row) {
                return $row['units'] > 0;
            })->sortByDesc('units')->values();
        }
        return $d;
    }

    // ---------------------------------------------------------------- tenant

    public static function tenant(Tenant $tenant)
    {
        $today = Carbon::today();
        $open = Invoice::where('parent_id', $tenant->parent_id)->where('tenant', $tenant->id)
            ->whereIn('status', ['unpaid', 'partial_paid'])->orderBy('end_date')->get();

        $d['openInvoices'] = $open;
        $d['amountDue'] = $open->sum(function ($invoice) {
            return max(0, $invoice->getInvoiceDueAmount());
        });
        $next = $open->first();
        $d['nextDue'] = $next ? ['amount' => $next->getInvoiceDueAmount(), 'date' => $next->end_date, 'overdue' => Carbon::parse($next->end_date)->lt($today)] : null;

        $start = $tenant->lease_start_date ? Carbon::parse($tenant->lease_start_date) : null;
        $end = $tenant->lease_end_date ? Carbon::parse($tenant->lease_end_date) : null;
        if ($start && $end && $end->gt($start)) {
            $d['leaseDaysLeft'] = max(0, $today->diffInDays($end, false));
            $d['leaseProgress'] = (int) round(min(100, max(0, $start->diffInDays($today, false) / $start->diffInDays($end) * 100)));
        } else {
            $d['leaseDaysLeft'] = null;
            $d['leaseProgress'] = null;
        }

        $d['depositHeld'] = DepositTransaction::balance($tenant->id);
        $d['autopay'] = $tenant->autopay_enabled ? ucfirst((string) $tenant->card_brand) . ' **** ' . $tenant->card_last4 : null;
        $d['parking'] = ParkingAssignment::where('parent_id', $tenant->parent_id)->where('tenant_id', $tenant->id)
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', now()->toDateString());
            })->with('slot')->get();

        $upcoming = collect();
        foreach (FacilityBooking::where('parent_id', $tenant->parent_id)->where('tenant_id', $tenant->id)->whereIn('status', ['pending', 'approved'])
            ->whereDate('booking_date', '>=', $today)->orderBy('booking_date')->limit(5)->get() as $b) {
            $upcoming->push(['date' => $b->booking_date, 'time' => $b->start_time, 'icon' => 'calendar-event', 'color' => $b->status == 'pending' ? 'warning' : 'success',
                'title' => optional($b->facility)->name . ($b->status == 'pending' ? ' (' . __('awaiting approval') . ')' : ''), 'sub' => null, 'url' => route('facility.index')]);
        }
        foreach (VisitorPass::where('parent_id', $tenant->parent_id)->where('tenant_id', $tenant->id)->where('status', 'active')
            ->whereDate('visit_date', '>=', $today)->orderBy('visit_date')->limit(5)->get() as $v) {
            $upcoming->push(['date' => $v->visit_date, 'time' => null, 'icon' => 'user-check', 'color' => 'info',
                'title' => __('Visitor') . ': ' . $v->visitor_name, 'sub' => __('Code') . ' ' . $v->code, 'url' => route('visitor.index')]);
        }
        $d['upcoming'] = $upcoming->sortBy(function ($item) {
            return $item['date'] . ' ' . ($item['time'] ?? '23:59');
        })->values();

        $d['inspectionToSign'] = Inspection::where('parent_id', $tenant->parent_id)->where('tenant_id', $tenant->id)
            ->where('status', 'completed')->whereNull('tenant_signed_at')->orderBy('id', 'desc')->first();
        $voted = PollVote::where('tenant_id', $tenant->id)->pluck('poll_id');
        $d['openPolls'] = Poll::where('parent_id', $tenant->parent_id)->where('is_active', 1)->whereIn('property_id', [0, $tenant->property])
            ->where(function ($q) {
                $q->whereNull('closes_on')->orWhereDate('closes_on', '>=', now()->toDateString());
            })->whereNotIn('id', $voted)->count();
        $d['openRequests'] = MaintenanceRequest::where('parent_id', $tenant->parent_id)->where('tenant_id', $tenant->id)->whereIn('status', ['pending', 'in_progress'])->count();
        $d['rentChange'] = \App\Models\RentIncrease::where('tenant_id', $tenant->id)->where('status', 'scheduled')->whereNotNull('notice_sent_at')->first();
        return $d;
    }

    // ---------------------------------------------------------------- maintainer

    public static function maintainer(User $user)
    {
        $today = Carbon::today();
        $mine = MaintenanceRequest::where('maintainer_id', $user->id);

        $d['inProgress'] = (clone $mine)->where('status', 'in_progress')->count();
        $d['completedThisMonth'] = (clone $mine)->where('status', 'completed')
            ->whereMonth('updated_at', $today->month)->whereYear('updated_at', $today->year)->count();
        $d['hoursThisMonth'] = (float) (clone $mine)->where('status', 'completed')
            ->whereMonth('updated_at', $today->month)->whereYear('updated_at', $today->year)->sum('labor_hours');
        $rated = (clone $mine)->where('tenant_rating', '>', 0);
        $d['ratingCount'] = (clone $rated)->count();
        $d['avgRating'] = $d['ratingCount'] ? round((clone $rated)->avg('tenant_rating'), 1) : null;

        $rank = "CASE ai_priority WHEN 'emergency' THEN 0 WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 ELSE 4 END";
        $d['queue'] = (clone $mine)->whereIn('status', ['pending', 'in_progress'])->orderByRaw($rank)->orderBy('request_date')->limit(8)->get();
        $d['urgentOpen'] = (clone $mine)->whereIn('status', ['pending', 'in_progress'])->whereIn('ai_priority', ['emergency', 'high'])->count();
        $d['upcomingPreventive'] = MaintenanceSchedule::where('maintainer_id', $user->id)->where('is_active', 1)
            ->whereBetween('next_date', [$today->toDateString(), $today->copy()->addDays(14)->toDateString()])->orderBy('next_date')->get();
        $d['recentFeedback'] = (clone $rated)->orderBy('updated_at', 'desc')->limit(4)->get();
        return $d;
    }

    // ---------------------------------------------------------------- landlord

    public static function landlord(User $user, $parentId)
    {
        $propertyIds = Property::where('parent_id', $parentId)->where('landlord_id', $user->id)->pluck('id');
        $units = PropertyUnit::whereIn('property_id', $propertyIds)->get();
        $d['units'] = $units->count();
        $d['occupied'] = $units->where('is_occupied', 1)->count();
        $d['vacantUnits'] = $units->where('is_occupied', 0)->values();
        $d['occupancy'] = $d['units'] ? round($d['occupied'] / $d['units'] * 100) : 0;
        $d['collectedThisYear'] = InvoicePayment::join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
            ->where('invoices.parent_id', $parentId)->whereIn('invoices.property_id', $propertyIds)
            ->whereYear('invoice_payments.payment_date', date('Y'))->sum('invoice_payments.amount');
        $d['openRepairs'] = MaintenanceRequest::where('parent_id', $parentId)->whereIn('property_id', $propertyIds)
            ->whereIn('status', ['pending', 'in_progress'])->orderBy('request_date', 'desc')->limit(5)->get();
        $d['openRepairCount'] = MaintenanceRequest::where('parent_id', $parentId)->whereIn('property_id', $propertyIds)->whereIn('status', ['pending', 'in_progress'])->count();
        return $d;
    }
}
