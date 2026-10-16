<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agreement extends Model
{
    use HasFactory;

    protected $fillable = [
        'agreement_id',
        'property_id',
        'unit_id',
        'date',
        'terms_condition',
        'description',
        'status',
        'attachment',
        'parent_id',
    ];


    public static function status()
    {
        return [
            'Draft' => __('Draft'),
            'Pending' => __('Pending'),
            'Completed' => __('Completed'),
            'Active' => __('Active'),
            'Cancelled' => __('Cancelled'),
            'Confirmed' => __('Confirmed'),

        ];
    }

    public function properties()
    {
        return $this->hasOne(Property::class, 'id', 'property_id');
    }

    public function units()
    {
        return $this->hasOne(PropertyUnit::class, 'id', 'unit_id');
    }

    public function tenants()
    {
        return $this->hasOne(Tenant::class, 'id', 'tenant_id');
    }
}
