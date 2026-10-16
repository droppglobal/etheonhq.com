<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParkingAssignment extends Model
{
    protected $fillable = ['slot_id', 'tenant_id', 'vehicle_number', 'vehicle_model', 'start_date', 'end_date', 'parent_id'];

    public function slot()
    {
        return $this->hasOne(ParkingSlot::class, 'id', 'slot_id');
    }

    public function tenants()
    {
        return $this->hasOne(Tenant::class, 'id', 'tenant_id');
    }
}
