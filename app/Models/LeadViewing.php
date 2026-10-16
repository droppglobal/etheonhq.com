<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadViewing extends Model
{
    protected $fillable = [
        'lead_id', 'property_id', 'unit_id', 'viewing_date', 'viewing_time', 'status', 'notes', 'parent_id',
    ];

    public static function statuses()
    {
        return [
            'requested' => __('Requested'),
            'scheduled' => __('Scheduled'),
            'completed' => __('Completed'),
            'cancelled' => __('Cancelled'),
            'no_show' => __('No Show'),
        ];
    }

    public function lead()
    {
        return $this->hasOne(Lead::class, 'id', 'lead_id');
    }

    public function properties()
    {
        return $this->hasOne(Property::class, 'id', 'property_id');
    }

    public function units()
    {
        return $this->hasOne(PropertyUnit::class, 'id', 'unit_id');
    }
}
