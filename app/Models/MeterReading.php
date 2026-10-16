<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeterReading extends Model
{
    protected $fillable = [
        'property_id', 'unit_id', 'utility_type', 'reading_date', 'previous_reading', 'current_reading',
        'rate', 'amount', 'invoice_id', 'notes', 'parent_id',
    ];

    public static function utilityTypes()
    {
        return [
            'electricity' => __('Electricity'),
            'water' => __('Water'),
            'gas' => __('Gas'),
        ];
    }

    public function getConsumptionAttribute()
    {
        return max(0, $this->current_reading - $this->previous_reading);
    }

    public function properties()
    {
        return $this->hasOne(Property::class, 'id', 'property_id');
    }

    public function units()
    {
        return $this->hasOne(PropertyUnit::class, 'id', 'unit_id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'id', 'invoice_id');
    }
}
