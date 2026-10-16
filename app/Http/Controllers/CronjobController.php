<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Notification;
use App\Models\PropertyUnit;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;

class CronjobController extends Controller
{

    public function runRecurringInvoices()
    {
        $today = Carbon::today();
        $endOfMonth = $today->copy()->endOfMonth()->day;
        $currentDay = $today->day;

        $invoices = Invoice::where('is_recurring', 1)
            ->whereNotNull('recurring_date')
            ->where('recurring_date', '!=', 0)
            ->get();

        foreach ($invoices as $baseInvoice) {

            $parentId = $baseInvoice->parent_id;
            $recurringDay = (int) $baseInvoice->recurring_date;
            $shouldRun = false;

            if ($recurringDay <= $endOfMonth) {
                $shouldRun = ($currentDay == $recurringDay);
            } else {
                $shouldRun = ($currentDay == $endOfMonth);
            }

            if (!$shouldRun) {
                continue;
            }

            $alreadyExists = Invoice::where('parent_id', $parentId)
                ->where('tenant', $baseInvoice->tenant)
                ->where('property_id', $baseInvoice->property_id)
                ->where('unit_id', $baseInvoice->unit_id)
                ->where('invoice_month', $today->format('Y-m-01'))
                ->exists();

            if ($alreadyExists) {
                continue;
            }

            $activeTenant = Tenant::where('parent_id', $parentId)
                ->where('id', $baseInvoice->tenant)
                ->where('unit', $baseInvoice->unit_id)
                ->where('lease_end_date', '>=', $today->format('Y-m-d'))
                ->first();

            if (!$activeTenant) {
                continue;
            }

            $unitOccupied = PropertyUnit::where('id', $baseInvoice->unit_id)
                ->where('is_occupied', 1)
                ->exists();

            if (!$unitOccupied) {
                continue;
            }

            $newInvoice = new Invoice();
            $newInvoice->invoice_id = invoiceNumber($parentId);
            $newInvoice->property_id = $baseInvoice->property_id;
            $newInvoice->unit_id = $baseInvoice->unit_id;

            $newInvoice->invoice_month = $today->format('Y-m-01');

            $baseDate = $today->copy()->startOfMonth()->addDays($recurringDay - 1);
            if ($recurringDay > $today->daysInMonth) {
                $baseDate = $today->copy()->endOfMonth();
            }
            $newInvoice->end_date = $baseDate->copy()->addDays(7)->format('Y-m-d');

            $newInvoice->notes = $baseInvoice->notes;
            $newInvoice->tenant = $baseInvoice->tenant;
            $newInvoice->status = 'unpaid';
            $newInvoice->parent_id = $parentId;
            $newInvoice->is_recurring = 0;
            $newInvoice->recurring_date = 0;
            $newInvoice->save();

            // a rent increase applied after the base invoice was made replaces the old rent amount
            $increase = \App\Models\RentIncrease::where('tenant_id', $baseInvoice->tenant)->where('status', 'applied')
                ->where('applied_at', '>', $baseInvoice->created_at)->latest('applied_at')->first();
            $rentTypeIds = $increase ? \App\Models\Type::where('parent_id', $parentId)->where('type', 'invoice')->where('title', 'like', '%rent%')->pluck('id')->all() : [];

            foreach ($baseInvoice->types as $item) {
                $newItem = new InvoiceItem();
                $newItem->invoice_id = $newInvoice->id;
                $newItem->invoice_type = $item->invoice_type;
                $newItem->amount = $increase && in_array($item->invoice_type, $rentTypeIds) ? $increase->new_rent : $item->amount;
                $newItem->description = $item->description;
                $newItem->save();
            }
        }
    }

    // puts scheduled rent increases into effect on their date
    public function applyRentIncreases()
    {
        $due = \App\Models\RentIncrease::where('status', 'scheduled')->whereDate('effective_date', '<=', Carbon::today())->get();
        foreach ($due as $increase) {
            $increase->apply();
        }
        return $due->count();
    }

    public function leaseExpiryReminder()
    {
        $today = Carbon::today();
        $module = 'lease_expiry_reminder';

        $notifications = Notification::where('module', $module)->get();

        foreach ($notifications as $notification) {
            $parentId = $notification->parent_id;

            $tenants = Tenant::where('parent_id', $parentId)
                ->whereNotNull('lease_end_date')
                ->get();

            foreach ($tenants as $tenant) {

                if (empty($tenant->user)) {
                    continue;
                }

                $endDate = Carbon::parse($tenant->lease_end_date)->startOfDay();
                $days_left = $today->diffInDays($endDate, false);

                if (!in_array($days_left, [7, 1])) {
                    continue;
                }

                $notification_responce = MessageReplace($notification, $tenant->id);

                $datas['subject'] = $notification_responce['subject'];
                $datas['message'] = $notification_responce['message'];
                $datas['module'] = $module;
                $datas['logo'] = settingsById($parentId)['company_logo'] ?? '';
                $datas['parent_id'] = $parentId;

                if ($notification->enabled_email == 1) {
                    commonEmailSend($tenant->user->email, $datas);
                }
                if ($notification->enabled_sms == 1) {
                    send_twilio_msg($tenant->user->phone_number, $notification_responce['sms_message'], $parentId);
                }
                if ($notification->enabled_whatsapp == 1) {
                    sendTwilioWhatsappMsg(['to' => $tenant->user->phone_number, 'body' => $notification_responce['sms_message'], 'parent_id' => $parentId]);
                }

                \App\Support\Notify::push($tenant->user_id,
                    $days_left == 1 ? __('Your lease ends tomorrow') : __('Your lease ends in :days days', ['days' => $days_left]),
                    __('Lease end date: :date. Please contact the office about renewing or moving out.', ['date' => dateFormat($tenant->lease_end_date)]),
                    route('dashboard'));
            }
        }
    }

    public function paymentReminder()
    {
        $today = Carbon::today();
        $module = 'payment_reminder';

        $notifications = Notification::where('module', $module)->get();

        foreach ($notifications as $notification) {
            $parentId = $notification->parent_id;

            $invoices = Invoice::where('parent_id', $parentId)
                ->whereIn('status', ['unpaid', 'partial_paid'])
                ->whereDate('end_date', '<=', $today)
                ->get();

            if ($invoices->isEmpty()) {
                continue;
            }

            useAccountSettings($parentId);
            foreach ($invoices as $invoice) {
                // the invoice's own tenant; fall back to whoever is in the unit
                $tenant = !empty($invoice->tenants) ? $invoice->tenants : Tenant::where('parent_id', $parentId)
                    ->where('property', $invoice->property_id)
                    ->where('unit', $invoice->unit_id)
                    ->first();

                if (empty($tenant) || empty($tenant->user) || empty($invoice->tenants)) {
                    continue;
                }

                // the payment_reminder template is filled from the invoice id
                $notification_responce = MessageReplace($notification, $invoice->id);

                // one-tap pay link in every channel
                $shortMessage = \App\Support\PayLink::appendTo($notification_responce['sms_message'], $invoice);
                $message = $notification_responce['message'];
                if (\App\Support\PayLink::canPayOnline($parentId)) {
                    $payUrl = \App\Support\PayLink::url($invoice);
                    $message = str_contains($message, '{payment_link}')
                        ? str_replace('{payment_link}', '<a href="' . $payUrl . '">' . $payUrl . '</a>', $message)
                        : $message . '<p><a href="' . $payUrl . '">' . __('Pay now') . '</a></p>';
                } else {
                    $message = str_replace('{payment_link}', '', $message);
                }

                $datas['subject'] = $notification_responce['subject'];
                $datas['message'] = $message;
                $datas['module'] = $module;
                $datas['logo'] = settingsById($parentId)['company_logo'] ?? '';
                $datas['parent_id'] = $parentId;

                $to = $tenant->user->email;

                if ($notification->enabled_email == 1) {
                    commonEmailSend($to, $datas);
                }

                if ($notification->enabled_sms == 1) {
                    send_twilio_msg($tenant->user->phone_number, $shortMessage, $parentId);
                }

                if ($notification->enabled_whatsapp == 1) {
                    $params = [
                        'to' => $tenant->user->phone_number,
                        'body' => $shortMessage,
                        'parent_id' => $parentId,
                    ];
                    sendTwilioWhatsappMsg($params);
                }

                \App\Support\Notify::push($tenant->user_id, __('Rent reminder'), invoicePrefix() . $invoice->invoice_id . ' · ' . priceFormat($invoice->getInvoiceDueAmount()) . ' ' . __('is due'), \App\Support\PayLink::canPayOnline($parentId) ? \App\Support\PayLink::url($invoice) : route('invoice.show', \Crypt::encrypt($invoice->id)));
            }
        }
    }

    public function leaseStatusUpdate()
    {
        $today = Carbon::today();

        $tenants = Tenant::whereDate('lease_end_date', $today)->get();

        foreach ($tenants as $tenant) {

            if (!empty($tenant->exit_date)) {
                continue;
            }

            $tenant->exit_date = $today;
            $tenant->reason = 'Lease Ended';
            $tenant->save();
            if ($tenant->unit) {
                $unit = PropertyUnit::find($tenant->unit);
                if ($unit) {
                    $unit->is_occupied = 0;
                    $unit->save();
                }
            }
            $agreement = Agreement::where('tenant_id', $tenant->id)
                ->latest()
                ->first();

            if ($agreement) {
                $agreement->status = 'Completed';
                $agreement->save();
            }
        }
    }

    /**
     * Adds one late-fee line to each overdue invoice, for owners who turned it on.
     * The amount comes from the unit (fixed, or a percentage of the invoice total).
     */
    public function applyLateFees()
    {
        $today = Carbon::today();
        $ownerIds = Setting::where('name', 'late_fee_enabled')->where('value', 'on')->pluck('parent_id');

        foreach ($ownerIds as $parentId) {
            $graceDays = (int) (settingsById($parentId)['late_fee_grace_days'] ?? 0);
            $lastDueDate = $today->copy()->subDays(max($graceDays, 0));

            $invoices = Invoice::where('parent_id', $parentId)
                ->whereIn('status', ['unpaid', 'partial_paid'])
                ->where('late_fee_applied', 0)
                ->whereDate('end_date', '<', $lastDueDate)
                ->get();

            foreach ($invoices as $invoice) {
                $unit = $invoice->units;
                if (empty($unit) || $unit->late_fee_amount <= 0) {
                    continue;
                }

                if ($unit->late_fee_type == 'percentage') {
                    $amount = round($invoice->getInvoiceSubTotalAmount() * $unit->late_fee_amount / 100, 2);
                } else {
                    $amount = $unit->late_fee_amount;
                }
                if ($amount <= 0) {
                    continue;
                }

                $type = \App\Models\Type::firstOrCreate(
                    ['parent_id' => $parentId, 'type' => 'invoice', 'title' => 'Late Fee']
                );

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'invoice_type' => $type->id,
                    'amount' => $amount,
                    'description' => __('Automatic late fee'),
                ]);

                $invoice->late_fee_applied = 1;
                $invoice->save();
            }
        }
    }

    /**
     * Emails the owner once when a tenant document is about to expire.
     */
    public function documentExpiryReminder()
    {
        $today = Carbon::today();

        $documents = \App\Models\TenantDocument::whereNotNull('expiry_date')
            ->where('expiry_reminder_sent', 0)
            ->get()
            ->groupBy('parent_id');

        foreach ($documents as $parentId => $ownerDocuments) {
            $days = (int) (settingsById($parentId)['document_expiry_reminder_days'] ?? 30);
            $owner = User::find($parentId);
            if (empty($owner)) {
                continue;
            }

            $due = $ownerDocuments->filter(function ($document) use ($today, $days) {
                return Carbon::parse($document->expiry_date)->lte($today->copy()->addDays($days));
            });
            if ($due->isEmpty()) {
                continue;
            }

            $lines = '';
            foreach ($due as $document) {
                $tenantName = optional(optional(Tenant::find($document->tenant_id))->user)->name;
                $lines .= '<li>' . e($document->title ?: __('Document')) . ' - ' . e($tenantName) . ' - ' . __('expires') . ' ' . dateFormat($document->expiry_date) . '</li>';
            }

            $data = [
                'module' => 'document_expiry_reminder',
                'subject' => __('Tenant documents expiring soon'),
                'message' => '<p>' . __('The following tenant documents are expiring soon:') . '</p><ul>' . $lines . '</ul>',
                'logo' => settingsById($parentId)['company_logo'] ?? '',
                'parent_id' => $parentId,
            ];
            $response = commonEmailSend($owner->email, $data);

            // tell each tenant about their own documents
            foreach ($due->groupBy('tenant_id') as $tenantId => $docs) {
                if ($tenantUser = optional(Tenant::find($tenantId))->user_id) {
                    \App\Support\Notify::push($tenantUser, __('Document expiring soon'),
                        $docs->map(fn($d) => ($d->title ?: __('Document')) . ' · ' . dateFormat($d->expiry_date))->implode(', ') . '. ' . __('Please send the office a new copy.'),
                        route('dashboard'), 'documents');
                }
            }

            // retry tomorrow if the mail could not be sent (e.g. SMTP not configured yet)
            if (($response['status'] ?? '') == 'success') {
                \App\Models\TenantDocument::whereIn('id', $due->pluck('id'))->update(['expiry_reminder_sent' => 1]);
            }
        }
    }

    /**
     * Marks open leads as expired when nothing has happened on them for the owner's lead expiry period.
     */
    public function leadExpiry()
    {
        $ownerIds = \App\Models\Lead::whereIn('status', \App\Models\Lead::openStatuses())->distinct()->pluck('parent_id');

        foreach ($ownerIds as $parentId) {
            $days = (int) (settingsById($parentId)['lead_expiry_days'] ?? 30);
            if ($days <= 0) {
                continue;
            }
            $cutoff = Carbon::now()->subDays($days);

            \App\Models\Lead::where('parent_id', $parentId)
                ->whereIn('status', ['new', 'contacted'])
                ->where(function ($query) use ($cutoff) {
                    $query->where('last_activity_at', '<', $cutoff)
                        ->orWhere(function ($q) use ($cutoff) {
                            $q->whereNull('last_activity_at')->where('created_at', '<', $cutoff);
                        });
                })
                // leads with a future follow-up are still being worked on
                ->where(function ($query) {
                    $query->whereNull('follow_up_date')->orWhereDate('follow_up_date', '<', Carbon::today());
                })
                ->update(['status' => 'expired']);
        }
    }

    /**
     * Creates a maintenance request for every active schedule that is due, then moves it to its next date.
     * Missed runs (cron not running for a while) create one request, not one per missed period.
     */
    public function runMaintenanceSchedules()
    {
        $today = Carbon::today();
        $schedules = \App\Models\MaintenanceSchedule::where('is_active', 1)->whereDate('next_date', '<=', $today)->get();

        foreach ($schedules as $schedule) {
            $request = new \App\Models\MaintenanceRequest();
            $request->property_id = $schedule->property_id;
            $request->unit_id = $schedule->unit_id;
            $request->issue_type = $schedule->issue_type;
            $request->maintainer_id = $schedule->maintainer_id;
            $request->vendor_id = $schedule->vendor_id;
            $request->schedule_id = $schedule->id;
            $request->status = 'pending';
            $request->request_date = $today->toDateString();
            $request->notes = trim(__('Preventive maintenance') . ': ' . $schedule->title . "\n" . $schedule->notes);
            $request->parent_id = $schedule->parent_id;
            $request->save();

            $next = Carbon::parse($schedule->next_date);
            while ($next->lte($today)) {
                $next = \App\Models\MaintenanceSchedule::advance($next, $schedule->frequency);
            }
            $schedule->last_run_date = $today->toDateString();
            $schedule->next_date = $next->toDateString();
            $schedule->save();
        }
    }

    /**
     * Charges saved cards for invoices that are due (one attempt per invoice per day).
     */
    public function chargeAutopay()
    {
        $today = Carbon::today();
        $tenants = Tenant::where('autopay_enabled', 1)
            ->where(function ($query) {
                $query->whereNotNull('stripe_payment_method_id')->orWhereNotNull('razorpay_token_id');
            })
            ->get();

        foreach ($tenants as $tenant) {
            $razorpay = $tenant->autopay_gateway == 'razorpay';
            $gatewayOn = $razorpay ? \App\Support\RazorpayAutopay::available($tenant->parent_id) : \App\Support\StripeAutopay::available($tenant->parent_id);
            if (!$gatewayOn) {
                continue;
            }
            useAccountSettings($tenant->parent_id);
            $invoices = Invoice::where('parent_id', $tenant->parent_id)
                ->where('tenant', $tenant->id)
                ->whereIn('status', ['unpaid', 'partial_paid'])
                ->whereDate('end_date', '<=', $today)
                ->where(function ($query) use ($today) {
                    // a Razorpay charge still being processed is followed up on every run
                    $query->whereNull('autopay_attempted_at')->orWhereDate('autopay_attempted_at', '<', $today)->orWhereNotNull('autopay_pending_id');
                })
                ->get();

            foreach ($invoices as $invoice) {
                $result = $razorpay
                    ? \App\Support\RazorpayAutopay::chargeInvoice($invoice, $tenant)
                    : \App\Support\StripeAutopay::chargeInvoice($invoice, $tenant);
                if ($result['status'] == 'skipped') {
                    continue;
                }
                $invoice->autopay_attempted_at = now();
                $invoice->autopay_error = $result['status'] == 'failed' ? $result['message'] : null;
                $invoice->save();

                if ($result['status'] == 'paid') {
                    \App\Support\Notify::push($tenant->user_id, __('Rent paid automatically'), invoicePrefix() . $invoice->invoice_id . ' · ' . __('Thank you!'), route('invoice.show', \Crypt::encrypt($invoice->id)));
                }
                if ($result['status'] == 'failed' && !empty($tenant->user)) {
                    \App\Support\Notify::push($tenant->user_id, __('Automatic payment failed'), invoicePrefix() . $invoice->invoice_id . ': ' . $result['message'], route('invoice.show', \Crypt::encrypt($invoice->id)));
                    commonEmailSend($tenant->user->email, [
                        'module' => 'autopay_failed',
                        'subject' => __('Automatic payment failed for invoice') . ' ' . invoicePrefix() . $invoice->invoice_id,
                        'message' => '<p>' . __('We could not take the automatic payment for this invoice.') . '</p><p>' . e($result['message']) . '</p><p>'
                            . __('Please pay the invoice online with this link, or update your autopay details:') . ' <a href="' . \App\Support\PayLink::url($invoice) . '">' . \App\Support\PayLink::url($invoice) . '</a></p>',
                        'logo' => settingsById($tenant->parent_id)['company_logo'] ?? '',
                        'parent_id' => $tenant->parent_id,
                    ]);
                }
            }
        }
    }

    public function run(Request $request)
    {
        ini_set('max_execution_time', 0);

        if (empty($request->key)) {
            \Log::warning('Cron Job: Key is required but not provided.', [
                'ip' => $request->ip(),
            ]);
            return;
        }

        $key = getSettingsValByIdName(1, 'cronjob');

        if (empty($key)) {
            \Log::warning('Cron Job: Key is not configured in settings.', [
                'ip' => $request->ip(),
            ]);
            return;
        }

        if ($request->key !== $key) {
            \Log::warning('Cron Job: Invalid key provided.', [
                'ip' => $request->ip(),
                'provided_key' => $request->key,
            ]);
            return;
        }

        try {
            Artisan::call('app:run-daily-tasks');

            Setting::updateSettings('cronjob_last_execution', Carbon::now());

            \Log::info('Cron Job executed successfully.', [
                'executed_at' => Carbon::now()->toDateTimeString(),
            ]);

        } catch (\Exception $e) {
            \Log::error('Cron Job execution failed.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'failed_at' => Carbon::now()->toDateTimeString(),
            ]);
        }
    }
}
