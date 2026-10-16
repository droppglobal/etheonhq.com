<?php

namespace App\Http\Controllers;

use App\Models\CreditNote;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use App\Models\Tenant;
use App\Models\Type;
use App\Support\Notify;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Invoice extras: generate a month's rent for all tenants at once, payment receipts (PDF),
 * credit notes and refunds.
 */
class InvoiceToolsController extends Controller
{
    private function isStaff()
    {
        return !in_array(\Auth::user()->type, ['tenant', 'maintainer', 'landlord']);
    }

    private function findInvoice($id)
    {
        $query = Invoice::where('parent_id', parentId())->where('id', (int) $id);
        if (\Auth::user()->type == 'tenant') {
            $query->where('tenant', optional(\Auth::user()->tenants)->id);
        } elseif (\Auth::user()->type == 'maintainer') {
            return null;
        }
        return scopeLandlord($query)->first();
    }

    // ------------------------------------------------------------------ bulk monthly rent

    private function rentType()
    {
        $type = Type::where('parent_id', parentId())->where('type', 'invoice')->where('title', 'like', '%rent%')->first();
        if (!$type) {
            $type = Type::create(['title' => 'Rent', 'type' => 'invoice', 'parent_id' => parentId()]);
        }
        return $type;
    }

    private function candidates(Carbon $month)
    {
        $start = $month->copy()->startOfMonth();
        $tenants = Tenant::with(['user', 'properties', 'units'])
            ->where('parent_id', parentId())->whereNull('exit_date')->where('unit', '>', 0)
            ->whereHas('user', fn($q) => $q->where('is_active', 1))
            ->get();
        $invoiced = Invoice::where('parent_id', parentId())->whereDate('invoice_month', $start->toDateString())
            ->whereIn('tenant', $tenants->pluck('id'))->get()->groupBy('tenant');

        return $tenants->map(function ($tenant) use ($invoiced, $start) {
            $unit = $tenant->units;
            $existing = $invoiced->get($tenant->id);
            $leaseEnded = $tenant->lease_end_date && Carbon::parse($tenant->lease_end_date)->lt($start);
            return (object) [
                'tenant' => $tenant,
                'unit' => $unit,
                'rent' => (float) optional($unit)->rent,
                'rentType' => optional($unit)->rent_type ?: 'monthly',
                'existing' => $existing ? $existing->first() : null,
                'leaseEnded' => $leaseEnded,
                // ticked by default: monthly rent, nothing invoiced for this month yet
                'checked' => !$existing && (optional($unit)->rent_type ?: 'monthly') == 'monthly' && optional($unit)->rent > 0,
            ];
        })->sortBy(fn($row) => optional($row->tenant->properties)->name . ' ' . optional($row->unit)->name)->values();
    }

    public function generate(Request $request)
    {
        if (!\Auth::user()->can('create invoice') || !$this->isStaff()) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $month = preg_match('/^\d{4}-\d{2}$/', (string) $request->query('month')) ? Carbon::parse($request->query('month') . '-01') : Carbon::today()->startOfMonth();
        $rows = $this->candidates($month);
        $dueDay = (int) ($request->query('due_day') ?: 5);
        return view('invoice.generate', compact('rows', 'month', 'dueDay'));
    }

    public function generateStore(Request $request)
    {
        if (!\Auth::user()->can('create invoice') || !$this->isStaff()) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        if (!preg_match('/^\d{4}-\d{2}$/', (string) $request->month) || empty($request->tenants) || !is_array($request->tenants)) {
            return redirect()->back()->with('error', __('Choose a month and at least one tenant.'));
        }
        $month = Carbon::parse($request->month . '-01');
        $dueDay = max(1, min(28, (int) $request->due_day));
        $dueDate = $month->copy()->day($dueDay)->toDateString();
        $rentType = $this->rentType();
        $amounts = (array) $request->amount;
        $rows = $this->candidates($month)->keyBy(fn($row) => $row->tenant->id);

        $created = 0;
        $skipped = 0;
        foreach ($request->tenants as $tenantId) {
            $row = $rows->get((int) $tenantId);
            $amount = round((float) ($amounts[$tenantId] ?? 0), 2);
            // only this account's tenants, never a second invoice for the same month, never a zero invoice
            if (!$row || $row->existing || $amount <= 0) {
                $skipped++;
                continue;
            }
            $invoice = \DB::transaction(function () use ($row, $month, $dueDate, $rentType, $amount, $request) {
                $invoice = new Invoice();
                $invoice->invoice_id = invoiceNumber();
                $invoice->property_id = $row->tenant->property;
                $invoice->unit_id = $row->tenant->unit;
                $invoice->tenant = $row->tenant->id;
                $invoice->invoice_month = $month->toDateString();
                $invoice->end_date = $dueDate;
                $invoice->notes = mb_substr((string) $request->notes, 0, 500);
                $invoice->status = 'unpaid';
                $invoice->is_recurring = 0;
                $invoice->recurring_date = 0;
                $invoice->parent_id = parentId();
                $invoice->save();

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'invoice_type' => $rentType->id,
                    'amount' => $amount,
                    'description' => __('Rent for :month', ['month' => $month->format('F Y')]),
                ]);
                return $invoice;
            });
            $created++;

            Notify::push($row->tenant->user_id, __('New invoice'), invoicePrefix() . $invoice->invoice_id . ' · ' . priceFormat($amount) . ' · ' . __('due') . ' ' . dateFormat($dueDate), route('invoice.show', \Crypt::encrypt($invoice->id)));
            if ($request->boolean('notify')) {
                $this->sendInvoiceNotice($invoice);
            }
        }

        $message = $created == 1 ? __('1 invoice created.') : __(':count invoices created.', ['count' => $created]);
        if ($skipped) {
            $message .= ' ' . __(':count skipped (already invoiced or no amount).', ['count' => $skipped]);
        }
        return redirect()->route('invoice.index')->with('success', $message);
    }

    // the owner's "New Invoice" email / SMS / WhatsApp template, with the pay link
    private function sendInvoiceNotice(Invoice $invoice)
    {
        $notification = \App\Models\Notification::where('parent_id', parentId())->where('module', 'invoice_create')->first();
        $user = optional($invoice->tenants)->user;
        if (!$notification || !$user) {
            return;
        }
        $text = MessageReplace($notification, $invoice->id);
        $short = \App\Support\PayLink::appendTo($text['sms_message'], $invoice);
        if ($notification->enabled_email == 1) {
            commonEmailSend($user->email, ['subject' => $text['subject'], 'message' => $text['message'], 'module' => 'invoice_create', 'logo' => settings()['company_logo'] ?? '', 'parent_id' => parentId()]);
        }
        if ($notification->enabled_sms == 1) {
            send_twilio_msg($user->phone_number, $short, parentId());
        }
        if ($notification->enabled_whatsapp == 1) {
            sendTwilioWhatsappMsg(['to' => $user->phone_number, 'body' => $short, 'parent_id' => parentId()]);
        }
    }

    // ------------------------------------------------------------------ receipts

    public function receipt($invoiceId, $paymentId)
    {
        $invoice = $this->findInvoice($invoiceId);
        $payment = $invoice ? InvoicePayment::where('invoice_id', $invoice->id)->where('id', (int) $paymentId)->where('amount', '>', 0)->first() : null;
        if (!$invoice || !$payment || !\Auth::user()->can('show invoice')) {
            return redirect()->back()->with('error', __('Payment not found.'));
        }
        $settings = settings();
        $paidBefore = (float) InvoicePayment::where('invoice_id', $invoice->id)->where('id', '<', $payment->id)->sum('amount');
        $balanceAfter = round($invoice->getInvoiceSubTotalAmount() - $invoice->getCreditTotal() - $paidBefore - $payment->amount, 2);
        $number = 'RC-' . str_pad($payment->id, 5, '0', STR_PAD_LEFT);
        return \Barryvdh\DomPDF\Facade\Pdf::loadView('invoice.receipt_pdf', compact('invoice', 'payment', 'settings', 'balanceAfter', 'number'))
            ->setPaper('a5')->download($number . '.pdf');
    }

    // ------------------------------------------------------------------ credit notes and refunds

    public function creditCreate($invoiceId)
    {
        $invoice = $this->findInvoice($invoiceId);
        if (!$invoice || !\Auth::user()->can('edit invoice') || !$this->isStaff()) {
            return response(__('Permission Denied!'), 403);
        }
        return view('invoice.credit_create', compact('invoice'));
    }

    public function creditStore(Request $request, $invoiceId)
    {
        $invoice = $this->findInvoice($invoiceId);
        if (!$invoice || !\Auth::user()->can('edit invoice') || !$this->isStaff()) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $type = $request->type == 'refund' ? 'refund' : 'credit';
        $amount = round((float) $request->amount, 2);
        $reason = trim((string) $request->reason);

        // a credit can cancel at most what is still owed; a refund at most what was paid
        $limit = $type == 'credit' ? $invoice->getInvoiceDueAmount() : $invoice->getPaidTotal();
        if ($amount <= 0 || $amount > round($limit, 2)) {
            return redirect()->back()->with('error', $type == 'credit'
                ? __('The credit must be more than 0 and no more than the amount still due (:amount).', ['amount' => priceFormat($limit)])
                : __('The refund must be more than 0 and no more than the amount paid (:amount).', ['amount' => priceFormat($limit)]));
        }
        if ($reason === '') {
            return redirect()->back()->with('error', __('Please give a reason.'));
        }

        $note = \DB::transaction(function () use ($invoice, $type, $amount, $reason, $request) {
            $note = CreditNote::create([
                'parent_id' => parentId(),
                'invoice_id' => $invoice->id,
                'number' => CreditNote::nextNumber(parentId()),
                'type' => $type,
                'amount' => $amount,
                'date' => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $request->date) ? $request->date : date('Y-m-d'),
                'method' => $type == 'refund' ? mb_substr((string) $request->method, 0, 100) : null,
                'reason' => mb_substr($reason, 0, 1000),
                'created_by' => \Auth::id(),
            ]);
            if ($type == 'refund') {
                // the money going back out is a negative payment, so income reports net it out
                $payment = new InvoicePayment();
                $payment->invoice_id = $invoice->id;
                $payment->transaction_id = $note->displayNumber();
                $payment->payment_type = 'Refund';
                $payment->amount = -$amount;
                $payment->payment_date = $note->date;
                $payment->receipt = '';
                $payment->notes = trim(__('Refund') . ($note->method ? ' (' . $note->method . ')' : '') . ': ' . $reason);
                $payment->parent_id = parentId();
                $payment->save();
                $note->payment_id = $payment->id;
                $note->save();
            }
            return $note;
        });
        Invoice::refreshStatus($invoice->id);

        if ($tenantUser = optional($invoice->tenants)->user) {
            Notify::push($tenantUser->id, $type == 'refund' ? __('Refund issued') : __('Credit applied'), invoicePrefix() . $invoice->invoice_id . ' · ' . priceFormat($amount), route('invoice.show', \Crypt::encrypt($invoice->id)));
        }
        return redirect()->back()->with('success', $type == 'refund' ? __('Refund :number recorded.', ['number' => $note->displayNumber()]) : __('Credit note :number added.', ['number' => $note->displayNumber()]));
    }

    public function creditDestroy($invoiceId, $noteId)
    {
        $invoice = $this->findInvoice($invoiceId);
        $note = $invoice ? CreditNote::where('invoice_id', $invoice->id)->where('id', (int) $noteId)->first() : null;
        if (!$note || !\Auth::user()->can('edit invoice') || !$this->isStaff()) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        if ($note->payment_id) {
            InvoicePayment::where('id', $note->payment_id)->where('invoice_id', $invoice->id)->where('amount', '<', 0)->delete();
        }
        $note->delete();
        Invoice::refreshStatus($invoice->id);
        return redirect()->back()->with('success', __('Removed.'));
    }

    public function creditPdf($invoiceId, $noteId)
    {
        $invoice = $this->findInvoice($invoiceId);
        $note = $invoice ? CreditNote::where('invoice_id', $invoice->id)->where('id', (int) $noteId)->first() : null;
        if (!$note || !\Auth::user()->can('show invoice')) {
            return redirect()->back()->with('error', __('Not found.'));
        }
        $settings = settings();
        return \Barryvdh\DomPDF\Facade\Pdf::loadView('invoice.credit_pdf', compact('invoice', 'note', 'settings'))
            ->setPaper('a5')->download($note->displayNumber() . '.pdf');
    }
}
