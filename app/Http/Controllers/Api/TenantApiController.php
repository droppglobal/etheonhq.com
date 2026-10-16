<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\AgreementController;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\InvoiceToolsController;
use App\Http\Controllers\MaintenanceRequestController;
use App\Http\Controllers\PollController;
use App\Http\Controllers\VisitorController;
use App\Models\Agreement;
use App\Models\AssistantMessage;
use App\Models\Comments;
use App\Models\Facility;
use App\Models\FacilityBooking;
use App\Models\Invoice;
use App\Models\MaintenanceRequest;
use App\Models\NoticeBoard;
use App\Models\Poll;
use App\Models\PollVote;
use App\Models\Type;
use App\Models\VisitorPass;
use App\Support\DashboardData;
use App\Support\PayLink;
use Illuminate\Http\Request;

/**
 * Mobile API for tenants. Reads are shaped here; actions reuse the website controllers through bridge().
 */
class TenantApiController extends BaseController
{
    private function tenant()
    {
        $tenant = \Auth::user()->tenants;
        abort_if(!$tenant, 403, __('Your tenancy details have not been set up yet. Please contact your property manager.'));
        return $tenant;
    }

    // ------------------------------------------------------------------ home

    public function home()
    {
        $tenant = $this->tenant();
        $extra = DashboardData::tenant($tenant);
        $unit = $tenant->units;
        $nd = $extra['nextDue'];
        return $this->ok([
            'tenant' => [
                'id' => $tenant->id,
                'property' => optional($tenant->properties)->name,
                'unit' => optional($unit)->name,
                'rent' => $unit ? $this->money($unit->rent) : null,
                'rent_change' => !empty($extra['rentChange']) ? ['new_rent' => $this->money($extra['rentChange']->new_rent), 'effective' => $this->date($extra['rentChange']->effective_date)] : null,
                'lease_start' => $this->date($tenant->lease_start_date),
                'lease_end' => $this->date($tenant->lease_end_date),
                'lease_days_left' => $extra['leaseDaysLeft'],
                'lease_progress' => $extra['leaseProgress'],
                'address' => $this->address($tenant->properties),
                'rent_type' => $unit ? (\App\Models\PropertyUnit::rentTypes()[$unit->rent_type] ?? $unit->rent_type) : null,
                'bedrooms' => $unit ? (int) $unit->bedroom : null,
                'baths' => $unit ? (int) $unit->baths : null,
                'kitchens' => $unit ? (int) $unit->kitchen : null,
                'family_members' => $tenant->family_member,
            ],
            'paid_this_year' => $this->money(\App\Models\InvoicePayment::join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
                ->where('invoices.tenant', $tenant->id)->whereYear('invoice_payments.payment_date', date('Y'))->sum('invoice_payments.amount')),
            'documents_expiring' => \App\Models\TenantDocument::where('tenant_id', $tenant->id)->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '<=', now()->addDays(30)->toDateString())->count(),
            'next_payment' => $nd ? ['amount' => $this->money($nd['amount']), 'date' => $this->date($nd['date']), 'overdue' => (bool) $nd['overdue']] : null,
            'amount_due' => $this->money($extra['amountDue']),
            'unpaid_invoices' => $extra['openInvoices']->count(),
            'deposit_held' => $this->money($extra['depositHeld']),
            'autopay' => (bool) $extra['autopay'],
            'open_repairs' => $extra['openRequests'],
            'open_polls' => $extra['openPolls'],
            'inspection_to_sign' => $extra['inspectionToSign'] ? ['id' => $extra['inspectionToSign']->id, 'type' => $extra['inspectionToSign']->type] : null,
            'parking' => collect($extra['parking'])->map(fn($p) => ['slot' => optional($p->slot)->code, 'vehicle' => $p->vehicle_number])->values(),
            'upcoming' => collect($extra['upcoming'])->map(fn($i) => [
                'date' => $this->date($i['date'] ?? null), 'time' => !empty($i['time']) ? substr($i['time'], 0, 5) : null,
                'title' => $i['title'] ?? '', 'subtitle' => $i['sub'] ?? null, 'icon' => $i['icon'] ?? null, 'screen' => \App\Support\Notify::screenFor($i['url'] ?? ''),
            ])->values(),
        ]);
    }

    // ------------------------------------------------------------------ invoices

    private function invoiceSummary(Invoice $invoice)
    {
        $due = $invoice->getInvoiceDueAmount();
        return [
            'id' => $invoice->id,
            'number' => invoicePrefix() . $invoice->invoice_id,
            'month' => $this->date($invoice->invoice_month),
            'month_label' => date('F Y', strtotime($invoice->invoice_month)),
            'due_date' => $this->date($invoice->end_date),
            'status' => $invoice->status,
            'status_label' => Invoice::status()[$invoice->status] ?? $invoice->status,
            'total' => $this->money($invoice->getInvoiceSubTotalAmount()),
            'due' => $this->money(max(0, $due)),
            'overdue' => $due > 0 && strtotime($invoice->end_date) < strtotime('today'),
        ];
    }

    public function invoices(Request $request)
    {
        $tenant = $this->tenant();
        $query = Invoice::with(['types', 'payments', 'creditNotes'])->where('parent_id', $tenant->parent_id)->where('tenant', $tenant->id);
        if ($request->status == 'open') {
            $query->whereIn('status', ['unpaid', 'partial_paid']);
        } elseif ($request->status == 'paid') {
            $query->where('status', 'paid');
        }
        return $this->paginated($query->orderByDesc('invoice_month')->orderByDesc('id')->paginate(20), fn($i) => $this->invoiceSummary($i));
    }

    public function invoice($id)
    {
        $tenant = $this->tenant();
        $invoice = Invoice::with(['types.types', 'payments', 'creditNotes', 'properties', 'units'])->where('parent_id', $tenant->parent_id)->where('tenant', $tenant->id)->findOrFail($id);
        $due = $invoice->getInvoiceDueAmount();
        return $this->ok($this->invoiceSummary($invoice) + [
            'property' => optional($invoice->properties)->name,
            'unit' => optional($invoice->units)->name,
            'notes' => $invoice->notes,
            'items' => $invoice->types->map(fn($item) => ['type' => optional($item->types)->title, 'description' => $item->description, 'amount' => $this->money($item->amount)])->values(),
            'credits' => $this->money($invoice->getCreditTotal()),
            'paid' => $this->money($invoice->getPaidTotal()),
            'payments' => $invoice->payments->map(fn($p) => [
                'id' => $p->id, 'date' => $this->date($p->payment_date), 'amount' => $this->money($p->amount), 'method' => $p->payment_type,
                'reference' => $p->transaction_id, 'is_refund' => $p->amount < 0,
                'receipt_url' => $p->amount > 0 ? url('/api/v1/tenant/invoices/' . $invoice->id . '/payments/' . $p->id . '/receipt') : null,
            ])->values(),
            'credit_notes' => $invoice->creditNotes->map(fn($n) => ['number' => $n->displayNumber(), 'type' => $n->type, 'amount' => $this->money($n->amount), 'date' => $this->date($n->date), 'reason' => $n->reason])->values(),
            // opens the secure payment page (Stripe / Razorpay) in the phone's browser
            'pay_url' => $due > 0 && PayLink::canPayOnline($invoice->parent_id) ? PayLink::url($invoice) : null,
        ]);
    }

    public function receipt($invoiceId, $paymentId)
    {
        $this->tenant();
        return $this->bridge(app(InvoiceToolsController::class)->receipt($invoiceId, $paymentId));
    }

    // ------------------------------------------------------------------ repairs

    private function repairSummary(MaintenanceRequest $m)
    {
        return [
            'id' => $m->id,
            'title' => optional($m->types)->title ?: __('Repair request'),
            'notes' => $m->notes,
            'status' => $m->status,
            'status_label' => MaintenanceRequest::status()[$m->status] ?? $m->status,
            'priority' => $m->ai_priority,
            'priority_label' => $m->ai_priority ? (\App\Support\MaintenanceTriage::priorities()[$m->ai_priority] ?? null) : null,
            'request_date' => $this->date($m->request_date),
            'preferred_visit' => $m->preferredVisitText(),
            'on_the_way_at' => $this->dateTime($m->on_the_way_at),
            'maintainer' => $m->maintainers ? ['name' => $m->maintainers->name, 'phone' => $m->maintainers->phone_number] : null,
            'rating' => $m->tenant_rating ?: null,
        ];
    }

    public function repairs(Request $request)
    {
        $tenant = $this->tenant();
        $query = MaintenanceRequest::with(['types', 'maintainers'])->where('parent_id', $tenant->parent_id)
            ->where('property_id', $tenant->property)->where('unit_id', $tenant->unit);
        if (in_array($request->status, ['pending', 'in_progress', 'completed'])) {
            $query->where('status', $request->status);
        } elseif ($request->status == 'open') {
            $query->whereIn('status', ['pending', 'in_progress']);
        }
        return $this->paginated($query->orderByDesc('id')->paginate(20), fn($m) => $this->repairSummary($m));
    }

    public function repair($id)
    {
        $tenant = $this->tenant();
        $m = MaintenanceRequest::with(['types', 'maintainers'])->where('parent_id', $tenant->parent_id)
            ->where('property_id', $tenant->property)->where('unit_id', $tenant->unit)->findOrFail($id);
        $comments = Comments::where('maintenance_id', $m->id)->orderBy('id')->get();
        $users = \App\Models\User::whereIn('id', $comments->pluck('user_id'))->get()->keyBy('id');
        return $this->ok($this->repairSummary($m) + [
            'photo' => $this->file($m->issue_attachment, 'upload/issue_attachment/'),
            'after_photo' => $this->file($m->after_photo, 'upload/issue_attachment/'),
            'fixed_date' => $this->date($m->fixed_date),
            'feedback' => $m->tenant_feedback,
            'can_rate' => $m->status == 'completed' && !$m->tenant_rating,
            'comments' => $comments->map(fn($c) => [
                'id' => $c->id, 'comment' => $c->comment, 'mine' => $c->user_id == \Auth::id(),
                'author' => optional($users->get($c->user_id))->name, 'at' => $this->dateTime($c->created_at),
            ])->values(),
        ]);
    }

    public function repairTypes()
    {
        $tenant = $this->tenant();
        return $this->ok([
            'issue_types' => Type::where('parent_id', $tenant->parent_id)->where('type', 'issue')->orderBy('title')->get(['id', 'title']),
            'slots' => collect(MaintenanceRequest::slots())->map(fn($label, $key) => ['key' => $key, 'label' => $label])->values(),
        ]);
    }

    public function createRepair(Request $request)
    {
        $tenant = $this->tenant();
        $request->validate([
            'issue_type' => 'required|integer',
            'notes' => 'required|string|max:2000',
            'preferred_date' => 'nullable|date',
            'preferred_slot' => 'nullable|string',
            'issue_attachment' => 'nullable|file|max:10240',
        ]);
        if (!Type::where('parent_id', $tenant->parent_id)->where('type', 'issue')->where('id', $request->issue_type)->exists()) {
            return $this->fail(__('Please choose an issue type.'));
        }
        // the website form sends the tenant's own property and unit
        $request->merge(['property_id' => $tenant->property, 'unit_id' => $tenant->unit]);
        $before = (int) MaintenanceRequest::max('id');
        return $this->bridge(app(MaintenanceRequestController::class)->store($request), function () use ($tenant, $before) {
            $m = MaintenanceRequest::where('id', '>', $before)->where('tenant_id', $tenant->id)->latest('id')->first();
            return $m ? $this->repairSummary($m) : null;
        });
    }

    public function comment(Request $request, $id)
    {
        $this->tenant();
        $request->validate(['comment' => 'required|string|max:2000']);
        return $this->bridge(app(MaintenanceRequestController::class)->comment($request, $id));
    }

    public function rate(Request $request, $id)
    {
        $tenant = $this->tenant();
        $request->validate(['tenant_rating' => 'required|integer|min:1|max:5', 'tenant_feedback' => 'nullable|string|max:1000']);
        $m = MaintenanceRequest::where('parent_id', $tenant->parent_id)->where('property_id', $tenant->property)->where('unit_id', $tenant->unit)->findOrFail($id);
        if ($m->status != 'completed') {
            return $this->fail(__('You can rate a repair once it is completed.'));
        }
        if ($m->tenant_rating > 0) {
            return $this->fail(__('You have already rated this repair.'));
        }
        return $this->bridge(app(MaintenanceRequestController::class)->rate($request, $id));
    }

    // ------------------------------------------------------------------ notices

    public function notices()
    {
        $tenant = $this->tenant();
        $notices = NoticeBoard::where('parent_id', $tenant->parent_id)->orderByDesc('id')->paginate(20);
        return $this->paginated($notices, fn($n) => [
            'id' => $n->id, 'title' => $n->title, 'description' => strip_tags((string) $n->description),
            'attachment' => $this->file($n->attachment, 'upload/note/'), 'date' => $this->date($n->created_at),
        ]);
    }

    // ------------------------------------------------------------------ assistant chat

    public function chat()
    {
        $tenant = $this->tenant();
        $messages = AssistantMessage::where('tenant_id', $tenant->id)->orderByDesc('id')->limit(50)->get()->reverse()->values();
        return $this->ok($messages->map(fn($m) => ['id' => $m->id, 'role' => $m->role, 'content' => $m->content, 'channel' => $m->channel, 'at' => $this->dateTime($m->created_at)]));
    }

    public function sendChat(Request $request)
    {
        $this->tenant();
        $request->validate(['message' => 'required|string|max:1000']);
        return $this->bridge(app(AssistantController::class)->message($request));
    }

    public function chatRequest(Request $request)
    {
        $this->tenant();
        $request->validate(['description' => 'required|string|max:1000']);
        return $this->bridge(app(AssistantController::class)->createRequest($request));
    }

    // ------------------------------------------------------------------ agreements

    public function agreements()
    {
        $tenant = $this->tenant();
        $agreements = Agreement::where('parent_id', $tenant->parent_id)->where('tenant_id', $tenant->id)->orderByDesc('id')->get();
        return $this->ok($agreements->map(fn($a) => [
            'id' => $a->id, 'number' => agreementPrefix() . $a->agreement_id, 'status' => $a->status,
            'start' => $this->date($a->agreement_start_date), 'end' => $this->date($a->agreement_end_date),
            'signed' => !empty($a->tenant_signature), 'signed_at' => $this->dateTime($a->tenant_signature_date),
            'can_sign' => empty($a->tenant_signature) && !empty(getSettingsValByName('landlord_signature')),
            'pdf_url' => url('/api/v1/tenant/agreements/' . $a->id . '/pdf'),
            'terms' => strip_tags((string) $a->terms_condition),
        ]));
    }

    public function agreementPdf($id)
    {
        $tenant = $this->tenant();
        $agreement = Agreement::where('parent_id', $tenant->parent_id)->where('tenant_id', $tenant->id)->findOrFail($id);
        return $this->bridge(app(AgreementController::class)->downloadPdf(\Crypt::encrypt($agreement->id)));
    }

    public function signAgreement(Request $request, $id)
    {
        $tenant = $this->tenant();
        $request->validate(['tenant_signature' => ['required', 'string', 'regex:/^data:image\/png;base64,/']]);
        $agreement = Agreement::where('parent_id', $tenant->parent_id)->where('tenant_id', $tenant->id)->findOrFail($id);
        return $this->bridge(app(AgreementController::class)->saveTenantSignature($request, $agreement->id), null);
    }

    // ------------------------------------------------------------------ facility bookings

    public function facilities()
    {
        $tenant = $this->tenant();
        $facilities = Facility::where('parent_id', $tenant->parent_id)->where('is_active', 1)->whereIn('property_id', [0, $tenant->property])->orderBy('name')->get();
        return $this->ok($facilities->map(fn($f) => [
            'id' => $f->id, 'name' => $f->name, 'description' => $f->description, 'open' => substr($f->open_time, 0, 5), 'close' => substr($f->close_time, 0, 5),
            'slot_minutes' => $f->slot_minutes, 'max_per_week' => $f->max_bookings_per_week, 'requires_approval' => (bool) $f->requires_approval,
        ]));
    }

    public function slots(Request $request, $id)
    {
        $this->tenant();
        return $this->bridge(app(FacilityController::class)->slots($request, $id));
    }

    public function book(Request $request, $id)
    {
        $this->tenant();
        $request->validate(['booking_date' => 'required|date', 'start_time' => 'required|date_format:H:i']);
        return $this->bridge(app(FacilityController::class)->book($request, $id));
    }

    public function bookings()
    {
        $tenant = $this->tenant();
        $bookings = FacilityBooking::with('facility')->where('parent_id', $tenant->parent_id)->where('tenant_id', $tenant->id)
            ->orderByDesc('booking_date')->orderByDesc('start_time')->paginate(20);
        return $this->paginated($bookings, fn($b) => [
            'id' => $b->id, 'facility' => optional($b->facility)->name, 'date' => $this->date($b->booking_date),
            'start' => substr($b->start_time, 0, 5), 'end' => substr($b->end_time, 0, 5),
            'status' => $b->status, 'status_label' => FacilityBooking::statuses()[$b->status] ?? $b->status,
            'can_cancel' => in_array($b->status, ['pending', 'approved']) && $b->booking_date >= date('Y-m-d'),
        ]);
    }

    public function cancelBooking($id)
    {
        $this->tenant();
        return $this->bridge(app(FacilityController::class)->cancel($id));
    }

    // ------------------------------------------------------------------ visitor passes

    public function visitors()
    {
        $tenant = $this->tenant();
        $passes = VisitorPass::where('parent_id', $tenant->parent_id)->where('tenant_id', $tenant->id)->orderByDesc('visit_date')->orderByDesc('id')->paginate(20);
        return $this->paginated($passes, fn($p) => [
            'id' => $p->id, 'visitor_name' => $p->visitor_name, 'visitor_phone' => $p->visitor_phone, 'purpose' => $p->purpose,
            'vehicle_number' => $p->vehicle_number, 'visit_date' => $this->date($p->visit_date), 'code' => $p->code,
            'status' => $p->status, 'status_label' => VisitorPass::statuses()[$p->status] ?? $p->status,
            'checked_in_at' => $this->dateTime($p->checked_in_at), 'can_cancel' => $p->status == 'active',
        ]);
    }

    public function createVisitor(Request $request)
    {
        $tenant = $this->tenant();
        $before = (int) VisitorPass::max('id');
        return $this->bridge(app(VisitorController::class)->store($request), function () use ($tenant, $before) {
            $pass = VisitorPass::where('id', '>', $before)->where('tenant_id', $tenant->id)->latest('id')->first();
            return $pass ? ['id' => $pass->id, 'code' => $pass->code, 'visitor_name' => $pass->visitor_name, 'visit_date' => $this->date($pass->visit_date)] : null;
        });
    }

    public function cancelVisitor($id)
    {
        $this->tenant();
        return $this->bridge(app(VisitorController::class)->cancel($id));
    }

    // ------------------------------------------------------------------ payments, deposit, documents

    public function payments(Request $request)
    {
        $tenant = $this->tenant();
        $query = \App\Models\InvoicePayment::join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
            ->where('invoices.parent_id', $tenant->parent_id)->where('invoices.tenant', $tenant->id)
            ->select('invoice_payments.*', 'invoices.invoice_id as invoice_number', 'invoices.invoice_month');
        if (preg_match('/^\d{4}$/', (string) $request->year)) {
            $query->whereYear('invoice_payments.payment_date', $request->year);
        }
        $page = $query->orderByDesc('invoice_payments.payment_date')->orderByDesc('invoice_payments.id')->paginate(20);
        $years = \App\Models\InvoicePayment::join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')->where('invoices.tenant', $tenant->id)
            ->selectRaw('YEAR(invoice_payments.payment_date) as y, SUM(invoice_payments.amount) as total')->groupBy('y')->orderByDesc('y')->get();
        return response()->json([
            'data' => collect($page->items())->map(fn($p) => [
                'id' => $p->id, 'invoice_id' => $p->invoice_id, 'invoice_number' => invoicePrefix() . $p->invoice_number,
                'month_label' => $p->invoice_month ? date('F Y', strtotime($p->invoice_month)) : null,
                'date' => $this->date($p->payment_date), 'amount' => $this->money($p->amount), 'method' => $p->payment_type,
                'reference' => $p->transaction_id, 'is_refund' => $p->amount < 0,
                'receipt_url' => $p->amount > 0 ? url('/api/v1/tenant/invoices/' . $p->invoice_id . '/payments/' . $p->id . '/receipt') : null,
            ])->values(),
            'meta' => ['page' => $page->currentPage(), 'per_page' => $page->perPage(), 'total' => $page->total(), 'last_page' => $page->lastPage(),
                'years' => $years->map(fn($y) => ['year' => (int) $y->y, 'total' => $this->money($y->total)])->values()],
        ]);
    }

    public function deposit()
    {
        $tenant = $this->tenant();
        $rows = \App\Models\DepositTransaction::where('parent_id', $tenant->parent_id)->where('tenant_id', $tenant->id)->orderByDesc('date')->orderByDesc('id')->get();
        $received = $rows->where('type', 'received')->sum('amount');
        $deducted = $rows->where('type', 'deduction')->sum('amount');
        $refunded = $rows->where('type', 'refund')->sum('amount');
        $types = \App\Models\DepositTransaction::types();
        return $this->ok([
            'held' => $this->money($received - $deducted - $refunded),
            'received' => $this->money($received),
            'deducted' => $this->money($deducted),
            'refunded' => $this->money($refunded),
            'required' => $tenant->units ? $this->money($tenant->units->deposit_type == 'fixed' ? $tenant->units->deposit_amount : $tenant->units->rent * $tenant->units->deposit_amount / 100) : null,
            'entries' => $rows->map(fn($r) => [
                'type' => $r->type, 'type_label' => $types[$r->type] ?? $r->type, 'date' => $this->date($r->date),
                'amount' => $this->money($r->type == 'received' ? $r->amount : -$r->amount), 'notes' => $r->notes,
            ])->values(),
        ]);
    }

    public function documents()
    {
        $tenant = $this->tenant();
        $docs = \App\Models\TenantDocument::where('tenant_id', $tenant->id)->orderByDesc('id')->get();
        return $this->ok($docs->map(function ($d) {
            $days = $d->expiry_date ? (int) now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($d->expiry_date), false) : null;
            return [
                'id' => $d->id, 'title' => $d->title ?: basename((string) $d->document),
                'type' => \App\Models\TenantDocument::documentTypes()[$d->document_type] ?? ucfirst(str_replace('_', ' ', (string) $d->document_type)),
                'expiry' => $this->date($d->expiry_date), 'expired' => $days !== null && $days < 0, 'expires_soon' => $days !== null && $days >= 0 && $days <= 30,
                'url' => $this->file($d->document, 'upload/tenant/'),
                'is_image' => (bool) preg_match('/\.(jpe?g|png|gif|webp)$/i', (string) $d->document),
            ];
        })->values());
    }

    // ------------------------------------------------------------------ inspections

    public function inspections()
    {
        $tenant = $this->tenant();
        $list = \App\Models\Inspection::where('parent_id', $tenant->parent_id)->where('tenant_id', $tenant->id)->where('status', 'completed')
            ->orderByDesc('inspection_date')->get();
        return $this->ok($list->map(fn($i) => [
            'id' => $i->id, 'type' => $i->type, 'type_label' => \App\Models\Inspection::types()[$i->type] ?? $i->type,
            'date' => $this->date($i->inspection_date), 'signed' => !empty($i->tenant_signed_at), 'signed_at' => $this->dateTime($i->tenant_signed_at),
        ])->values());
    }

    public function inspection($id)
    {
        $tenant = $this->tenant();
        $i = \App\Models\Inspection::where('parent_id', $tenant->parent_id)->where('tenant_id', $tenant->id)->where('status', 'completed')->findOrFail($id);
        $conditions = \App\Models\Inspection::conditions();
        $items = \App\Models\InspectionItem::where('inspection_id', $i->id)->orderBy('sort_order')->orderBy('id')->get();
        return $this->ok([
            'id' => $i->id, 'type' => $i->type, 'type_label' => \App\Models\Inspection::types()[$i->type] ?? $i->type,
            'date' => $this->date($i->inspection_date), 'notes' => $i->notes,
            'signed' => !empty($i->tenant_signed_at), 'signed_name' => $i->tenant_signed_name, 'signed_at' => $this->dateTime($i->tenant_signed_at),
            'areas' => $items->groupBy('area')->map(fn($group, $area) => [
                'area' => $area,
                'items' => $group->map(fn($it) => [
                    'item' => $it->item, 'condition' => $it->condition, 'condition_label' => $conditions[$it->condition] ?? $it->condition,
                    'notes' => $it->notes, 'photo' => $this->file($it->photo, 'upload/inspection/'),
                ])->values(),
            ])->values(),
        ]);
    }

    public function signInspection(Request $request, $id)
    {
        $tenant = $this->tenant();
        $request->validate(['signed_name' => 'required|string|max:191', 'agree' => 'accepted']);
        $inspection = \App\Models\Inspection::where('parent_id', $tenant->parent_id)->where('tenant_id', $tenant->id)->findOrFail($id);
        if ($inspection->tenant_signed_at) {
            return $this->fail(__('You have already signed this inspection.'));
        }
        return $this->bridge(app(\App\Http\Controllers\InspectionController::class)->sign($request, $id));
    }

    // ------------------------------------------------------------------ utility meter readings

    public function meterReadings()
    {
        $tenant = $this->tenant();
        $types = \App\Models\MeterReading::utilityTypes();
        $page = \App\Models\MeterReading::where('parent_id', $tenant->parent_id)->where('unit_id', $tenant->unit)->orderByDesc('reading_date')->orderByDesc('id')->paginate(20);
        return $this->paginated($page, fn($r) => [
            'id' => $r->id, 'utility' => $r->utility_type, 'utility_label' => $types[$r->utility_type] ?? $r->utility_type,
            'date' => $this->date($r->reading_date), 'previous' => (float) $r->previous_reading, 'current' => (float) $r->current_reading,
            'usage' => round((float) $r->current_reading - (float) $r->previous_reading, 2), 'rate' => (float) $r->rate,
            'amount' => $this->money($r->amount), 'invoice_id' => $r->invoice_id,
        ]);
    }

    // ------------------------------------------------------------------ polls

    public function polls()
    {
        $tenant = $this->tenant();
        $polls = Poll::with('options')->where('parent_id', $tenant->parent_id)->whereIn('property_id', [0, $tenant->property])->orderByDesc('id')->limit(50)->get();
        $myVotes = PollVote::where('tenant_id', $tenant->id)->whereIn('poll_id', $polls->pluck('id'))->pluck('option_id', 'poll_id');
        return $this->ok($polls->map(function ($poll) use ($myVotes) {
            $voted = $myVotes->get($poll->id);
            $showResults = $voted && $poll->show_results || !$poll->isOpen();
            $results = $showResults ? $poll->results() : collect();
            $total = $results->sum();
            return [
                'id' => $poll->id, 'question' => $poll->question, 'description' => $poll->description,
                'closes_on' => $this->date($poll->closes_on), 'open' => $poll->isOpen(), 'my_vote' => $voted,
                'options' => $poll->options->sortBy('sort_order')->map(fn($o) => [
                    'id' => $o->id, 'label' => $o->label,
                    'votes' => $showResults ? (int) ($results[$o->id] ?? 0) : null,
                    'percent' => $showResults && $total ? round(($results[$o->id] ?? 0) / $total * 100) : null,
                ])->values(),
            ];
        }));
    }

    public function vote(Request $request, $id)
    {
        $this->tenant();
        $request->validate(['option_id' => 'required|integer']);
        return $this->bridge(app(PollController::class)->vote($request, $id));
    }
}
