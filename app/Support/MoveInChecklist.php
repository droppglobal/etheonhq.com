<?php

namespace App\Support;

use App\Models\Agreement;
use App\Models\DepositTransaction;
use App\Models\Inspection;
use App\Models\Invoice;
use App\Models\MeterReading;
use App\Models\Tenant;
use App\Models\TenantChecklistItem;

/**
 * Move-in checklist for a tenant. Items backed by records (signed agreement, deposit, inspection,
 * first invoice, meter readings) tick themselves; the rest are ticked by staff. Staff can also tick
 * an automatic item by hand when it was handled outside the system.
 */
class MoveInChecklist
{
    public static function definitions()
    {
        return [
            'agreement_signed' => ['label' => __('Agreement signed by the tenant'), 'auto' => true],
            'deposit_received' => ['label' => __('Security deposit received'), 'auto' => true],
            'move_in_inspection' => ['label' => __('Move-in inspection done and signed'), 'auto' => true],
            'meter_readings' => ['label' => __('Opening meter readings recorded'), 'auto' => true],
            'first_invoice' => ['label' => __('First rent invoice issued'), 'auto' => true],
            'keys_handed' => ['label' => __('Keys / access cards handed over'), 'auto' => false],
            'utilities_transferred' => ['label' => __('Utilities transferred to the tenant'), 'auto' => false],
            'emergency_contact' => ['label' => __('Emergency contact collected'), 'auto' => false],
            'welcome_pack' => ['label' => __('Welcome pack and house rules shared'), 'auto' => false],
        ];
    }

    public static function items(Tenant $tenant)
    {
        $manual = TenantChecklistItem::with('doneBy')->where('tenant_id', $tenant->id)->get()->keyBy('item');
        $auto = self::automatic($tenant);
        $items = [];
        foreach (self::definitions() as $key => $def) {
            $row = $manual->get($key);
            $autoDone = $auto[$key] ?? null;
            $items[] = (object) [
                'key' => $key,
                'label' => $def['label'],
                'auto' => $def['auto'],
                'done' => (bool) ($autoDone || ($row && $row->done_at)),
                'detail' => $autoDone ?: ($row && $row->done_at ? trim(dateFormat($row->done_at) . ' · ' . optional($row->doneBy)->name . ($row->note ? ' · ' . $row->note : '')) : null),
                'manual' => (bool) ($row && $row->done_at),
            ];
        }
        return $items;
    }

    public static function progress(array $items)
    {
        $done = count(array_filter($items, fn($i) => $i->done));
        return ['done' => $done, 'total' => count($items), 'percent' => count($items) ? (int) round($done / count($items) * 100) : 0];
    }

    // returns a short "how we know" text for each automatic item that is satisfied
    private static function automatic(Tenant $tenant)
    {
        $result = [];
        $agreement = Agreement::where('tenant_id', $tenant->id)->whereNotNull('tenant_signature_date')->latest('tenant_signature_date')->first();
        if ($agreement) {
            $result['agreement_signed'] = __('Signed :date', ['date' => dateFormat($agreement->tenant_signature_date)]);
        }
        $deposit = DepositTransaction::where('tenant_id', $tenant->id)->where('type', 'received')->sum('amount');
        if ($deposit > 0) {
            $result['deposit_received'] = priceFormat($deposit) . ' ' . __('received');
        }
        $inspection = Inspection::where('tenant_id', $tenant->id)->where('type', 'move_in')->whereNotNull('tenant_signed_at')->latest('tenant_signed_at')->first();
        if ($inspection) {
            $result['move_in_inspection'] = __('Signed :date', ['date' => dateFormat($inspection->tenant_signed_at)]);
        }
        if ($tenant->unit && $reading = MeterReading::where('unit_id', $tenant->unit)->orderBy('reading_date')->first()) {
            $result['meter_readings'] = __('First reading :date', ['date' => dateFormat($reading->reading_date)]);
        }
        if ($invoice = Invoice::where('tenant', $tenant->id)->orderBy('id')->first()) {
            $result['first_invoice'] = invoicePrefix() . $invoice->invoice_id;
        }
        return $result;
    }
}
