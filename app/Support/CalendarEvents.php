<?php

namespace App\Support;

use App\Models\FacilityBooking;
use App\Models\Inspection;
use App\Models\Invoice;
use App\Models\MaintenanceRequest;
use App\Models\RentIncrease;
use App\Models\Tenant;
use App\Models\VisitorPass;

/**
 * Every dated thing in an owner's account on one calendar: rent due, repairs, move-ins and lease ends,
 * viewings, preventive tasks, inspections, facility bookings, visitors and rent changes.
 */
class CalendarEvents
{
    public static function types()
    {
        return [
            'invoice' => ['label' => __('Rent / invoice due'), 'color' => '#4f46e5'],
            'maintenance' => ['label' => __('Repair visit'), 'color' => '#f59e0b'],
            'move_in' => ['label' => __('Move-in'), 'color' => '#14b8a6'],
            'lease_end' => ['label' => __('Lease end'), 'color' => '#10b981'],
            'viewing' => ['label' => __('Viewing'), 'color' => '#0ea5e9'],
            'schedule' => ['label' => __('Preventive task'), 'color' => '#8b5cf6'],
            'inspection' => ['label' => __('Inspection'), 'color' => '#ec4899'],
            'booking' => ['label' => __('Facility booking'), 'color' => '#06b6d4'],
            'visitor' => ['label' => __('Visitor'), 'color' => '#64748b'],
            'rent_change' => ['label' => __('Rent change'), 'color' => '#ef4444'],
        ];
    }

    private static function event($type, $id, $title, $start, $url, $end = null)
    {
        $color = self::types()[$type]['color'];
        return ['id' => $type . '-' . $id, 'title' => $title, 'start' => $start, 'end' => $end ?: $start, 'type' => $type,
            'redirectUrl' => $url, 'color' => $color, 'textColor' => '#ffffff'];
    }

    public static function forAccount($parentId)
    {
        $user = \Auth::user();
        $events = [];
        $tenantNames = Tenant::with('user')->where('parent_id', $parentId)->get()->mapWithKeys(fn($t) => [$t->id => optional($t->user)->name ?: '#' . $t->id]);

        // unpaid invoices only: paid ones are history, not things to act on
        foreach (Invoice::where('parent_id', $parentId)->whereIn('status', ['unpaid', 'partial_paid'])->whereNotNull('end_date')->get() as $invoice) {
            $events[] = self::event('invoice', $invoice->id, invoicePrefix() . $invoice->invoice_id . ' · ' . ($tenantNames[$invoice->tenant] ?? ''), date('Y-m-d', strtotime($invoice->end_date)), route('invoice.show', encrypt($invoice->id)));
        }

        // open repairs on the tenant's preferred visit date, otherwise the request date
        foreach (MaintenanceRequest::with('types')->where('parent_id', $parentId)->whereIn('status', ['pending', 'in_progress'])->get() as $request) {
            $date = $request->preferred_date ?: $request->request_date;
            if ($date) {
                $events[] = self::event('maintenance', $request->id, (optional($request->types)->title ?: __('Repair')) . ($request->preferred_slot && $request->preferred_slot != 'any' ? ' (' . MaintenanceRequest::slots()[$request->preferred_slot] . ')' : ''), date('Y-m-d', strtotime($date)), route('maintenance-request.show', encrypt($request->id)));
            }
        }

        foreach (Tenant::where('parent_id', $parentId)->whereNull('exit_date')->get() as $tenant) {
            if ($tenant->lease_start_date) {
                $events[] = self::event('move_in', $tenant->id, __('Move-in') . ': ' . $tenantNames[$tenant->id], $tenant->lease_start_date, route('tenant.show', encrypt($tenant->id)));
            }
            if ($tenant->lease_end_date) {
                $events[] = self::event('lease_end', $tenant->id, __('Lease ends') . ': ' . $tenantNames[$tenant->id], $tenant->lease_end_date, route('tenant.show', encrypt($tenant->id)));
            }
        }

        if ($user->can('manage lead')) {
            foreach (\App\Models\LeadViewing::with('lead')->where('parent_id', $parentId)->whereIn('status', ['requested', 'scheduled'])->get() as $viewing) {
                $start = $viewing->viewing_date . (!empty($viewing->viewing_time) ? 'T' . $viewing->viewing_time : '');
                $events[] = self::event('viewing', $viewing->id, __('Viewing') . ': ' . optional($viewing->lead)->name, $start, route('lead.show', $viewing->lead_id));
            }
        }
        if ($user->can('manage maintenance schedule')) {
            foreach (\App\Models\MaintenanceSchedule::where('parent_id', $parentId)->where('is_active', 1)->get() as $schedule) {
                $events[] = self::event('schedule', $schedule->id, $schedule->title, $schedule->next_date, route('maintenance-schedule.index'));
            }
        }
        if ($user->can('manage inspection')) {
            foreach (Inspection::where('parent_id', $parentId)->whereNotNull('inspection_date')->get() as $inspection) {
                $events[] = self::event('inspection', $inspection->id, (Inspection::types()[$inspection->type] ?? __('Inspection')) . ' ' . __('inspection') . ': ' . ($tenantNames[$inspection->tenant_id] ?? ''), $inspection->inspection_date, route('inspection.show', $inspection->id));
            }
        }
        if ($user->can('manage facility')) {
            foreach (FacilityBooking::with('facility')->where('parent_id', $parentId)->whereIn('status', ['pending', 'approved'])->get() as $booking) {
                $events[] = self::event('booking', $booking->id, optional($booking->facility)->name . ' · ' . ($tenantNames[$booking->tenant_id] ?? ''),
                    $booking->booking_date . 'T' . $booking->start_time, route('facility.index'), $booking->booking_date . 'T' . $booking->end_time);
            }
        }
        if ($user->can('manage visitor')) {
            foreach (VisitorPass::where('parent_id', $parentId)->where('status', 'active')->whereDate('visit_date', '>=', now()->subDays(1))->get() as $pass) {
                $events[] = self::event('visitor', $pass->id, $pass->visitor_name . ' → ' . ($tenantNames[$pass->tenant_id] ?? ''), $pass->visit_date, route('visitor.index'));
            }
        }
        foreach (RentIncrease::where('parent_id', $parentId)->where('status', 'scheduled')->get() as $increase) {
            $events[] = self::event('rent_change', $increase->id, __('Rent :new', ['new' => priceFormat($increase->new_rent)]) . ' · ' . ($tenantNames[$increase->tenant_id] ?? ''), $increase->effective_date, route('tenant.show', encrypt($increase->tenant_id)) . '#rent');
        }
        return $events;
    }
}
