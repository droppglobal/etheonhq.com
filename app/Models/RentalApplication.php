<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentalApplication extends Model
{
    protected $fillable = [
        'lead_id', 'property_id', 'unit_id', 'first_name', 'last_name', 'email', 'phone', 'current_address',
        'employer', 'monthly_income', 'family_member', 'move_in_date', 'id_document', 'income_document',
        'message', 'status', 'review_notes', 'reviewed_at', 'tenant_id', 'parent_id',
    ];

    public static function statuses()
    {
        return [
            'pending' => __('Pending'),
            'approved' => __('Approved'),
            'rejected' => __('Rejected'),
        ];
    }

    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
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
