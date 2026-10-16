<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParkingSlot extends Model
{
    protected $fillable = ['property_id', 'code', 'vehicle_type', 'monthly_fee', 'is_active', 'parent_id'];

    public static function vehicleTypes()
    {
        return ['car' => __('Car'), 'bike' => __('Two-wheeler'), 'ev' => __('EV (charging)')];
    }

    public function properties()
    {
        return $this->hasOne(Property::class, 'id', 'property_id');
    }

    // the assignment that is current today (no end date, or ending in the future)
    public function currentAssignment()
    {
        return $this->hasOne(ParkingAssignment::class, 'slot_id', 'id')
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', now()->toDateString());
            })->orderBy('id', 'desc');
    }
}
