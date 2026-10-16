<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacilityBooking extends Model
{
    protected $fillable = ['facility_id', 'tenant_id', 'booking_date', 'start_time', 'end_time', 'status', 'notes', 'parent_id'];

    public static function statuses()
    {
        return ['pending' => __('Pending'), 'approved' => __('Approved'), 'rejected' => __('Rejected'), 'cancelled' => __('Cancelled')];
    }

    public static function statusColor($status)
    {
        return ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', 'cancelled' => 'secondary'][$status] ?? 'secondary';
    }

    public function facility()
    {
        return $this->hasOne(Facility::class, 'id', 'facility_id');
    }

    public function tenants()
    {
        return $this->hasOne(Tenant::class, 'id', 'tenant_id');
    }
}
