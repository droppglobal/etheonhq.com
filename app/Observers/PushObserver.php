<?php

namespace App\Observers;

use App\Models\Lead;
use App\Models\MaintenanceRequest;
use App\Models\RentalApplication;
use App\Models\VisitorPass;
use App\Support\Notify;

/**
 * Sends push notifications when records change, whichever screen, cron job, assistant or webhook changed them.
 */
class PushObserver
{
    public function created($model)
    {
        if ($model instanceof MaintenanceRequest) {
            $what = optional($model->types)->title ?: __('Repair request');
            $where = trim(optional($model->properties)->name . ' / ' . optional($model->units)->name, ' /');
            Notify::push(Notify::staffOf($model->parent_id, 'manage maintenance request'), __('New repair request'), $what . ($where ? ' · ' . $where : ''), route('maintenance-request.index'));
            if ($model->maintainer_id) {
                $this->notifyMaintainer($model);
            }
        } elseif ($model instanceof Lead) {
            Notify::push(Notify::staffOf($model->parent_id, 'manage lead'), __('New enquiry'), $model->name ?? __('Someone is interested in a property'), route('lead.index'));
        } elseif ($model instanceof \App\Models\NoticeBoard) {
            Notify::push($this->tenantUsers($model->parent_id), __('New notice') . ': ' . $model->title, strip_tags((string) $model->description), route('note.index'));
        } elseif ($model instanceof \App\Models\Poll) {
            Notify::push($this->tenantUsers($model->parent_id, $model->property_id), __('New poll'), $model->question, route('poll.index'));
        } elseif ($model instanceof \App\Models\Agreement) {
            if ($tenant = \App\Models\Tenant::find($model->tenant_id)) {
                Notify::push($tenant->user_id, __('Your rental agreement is ready'), __('Open it to read and sign.'), route('agreement.index'));
            }
        } elseif ($model instanceof RentalApplication) {
            Notify::push(Notify::staffOf($model->parent_id, 'manage rental application'), __('New rental application'), trim($model->first_name . ' ' . $model->last_name), route('rental-application.index', ['status' => 'pending']));
        }
    }

    public function updated($model)
    {
        if ($model instanceof MaintenanceRequest) {
            if ($model->wasChanged('maintainer_id') && $model->maintainer_id) {
                $this->notifyMaintainer($model);
            }
            if ($model->wasChanged('status') && $tenant = $model->tenetData) {
                $status = MaintenanceRequest::status()[$model->status] ?? $model->status;
                Notify::push($tenant->user_id, __('Repair update') . ': ' . $status, optional($model->types)->title ?: __('Your repair request'), route('maintenance-request.show', \Crypt::encrypt($model->id)));
            }
        } elseif ($model instanceof \App\Models\FacilityBooking) {
            if ($model->wasChanged('status') && in_array($model->status, ['approved', 'rejected']) && $tenant = \App\Models\Tenant::find($model->tenant_id)) {
                $what = optional($model->facility)->name . ' · ' . dateFormat($model->booking_date) . ' ' . substr($model->start_time, 0, 5);
                Notify::push($tenant->user_id, $model->status == 'approved' ? __('Booking approved') : __('Booking not approved'), $what, route('facility.index'));
            }
        } elseif ($model instanceof \App\Models\Inspection) {
            if ($model->wasChanged('status') && $model->status == 'completed' && empty($model->tenant_signed_at) && $tenant = \App\Models\Tenant::find($model->tenant_id)) {
                Notify::push($tenant->user_id, __('Inspection report ready to sign'), (\App\Models\Inspection::types()[$model->type] ?? '') . ' · ' . dateFormat($model->inspection_date), route('inspection.index'));
            }
        } elseif ($model instanceof VisitorPass) {
            if ($model->wasChanged('status') && $model->status == 'checked_in' && $tenant = $model->tenants) {
                Notify::push($tenant->user_id, __('Your visitor has arrived'), $model->visitor_name . ($model->purpose ? ' · ' . $model->purpose : ''), route('visitor.index'));
            }
        }
    }

    private function notifyMaintainer(MaintenanceRequest $model)
    {
        $where = trim(optional($model->properties)->name . ' / ' . optional($model->units)->name, ' /');
        Notify::push($model->maintainer_id, __('New job assigned'), (optional($model->types)->title ?: __('Repair request')) . ($where ? ' · ' . $where : ''), route('maintenance-request.show', \Crypt::encrypt($model->id)));
    }

    // current tenants of an owner (optionally of one property; 0 = all properties)
    private function tenantUsers($parentId, $propertyId = 0)
    {
        return \App\Models\Tenant::where('parent_id', $parentId)->whereNull('exit_date')
            ->when($propertyId, fn($q) => $q->where('property', $propertyId))
            ->pluck('user_id')->all();
    }
}
