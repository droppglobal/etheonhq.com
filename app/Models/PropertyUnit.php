<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyUnit extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'bedroom',
        'property_id',
        'baths',
        'kitchen',
        'rent',
        'deposit_amount',
        'deposit_type',
        'late_fee_type',
        'late_fee_amount',
        'incident_receipt_amount',
        'rent_type',
        'rent_duration',
        'start_date',
        'end_date',
        'payment_due_date',
        'is_occupied',
        'parent_id',
        'notes',
    ];

    public static function type()
    {
        return [
            'fixed' => __('Fixed'),
            'percentage' => __('Percentage'),
        ];
    }
    public static function rentTypes()
    {

        return [
            'monthly' => __('Monthly'),
            'yearly' => __('Yearly'),
            'custom' => __('Custom'),
        ];
    }
    public function properties()
    {
        return $this->hasOne('App\Models\Property', 'id', 'property_id');
    }

    public function tenants()
    {
        return Tenant::where('unit', $this->id)->first();
    }

    public function tenantRelation()
    {
        return $this->hasOne(Tenant::class, 'unit', 'id');
    }

}
