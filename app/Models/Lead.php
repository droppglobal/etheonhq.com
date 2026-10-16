<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'property_id', 'unit_id', 'source', 'status',
        'message', 'notes', 'follow_up_date', 'last_activity_at', 'tenant_id', 'parent_id',
    ];

    public static function statuses()
    {
        return [
            'new' => __('New'),
            'contacted' => __('Contacted'),
            'viewing' => __('Viewing'),
            'application' => __('Application'),
            'won' => __('Won'),
            'lost' => __('Lost'),
            'expired' => __('Expired'),
        ];
    }

    // statuses that still need action; only these can expire
    public static function openStatuses()
    {
        return ['new', 'contacted', 'viewing', 'application'];
    }

    public static function sources()
    {
        return [
            'website' => __('Website'),
            'phone' => __('Phone'),
            'walk_in' => __('Walk-in'),
            'referral' => __('Referral'),
            'manual' => __('Other'),
        ];
    }

    public static function statusColor($status)
    {
        return [
            'new' => 'primary', 'contacted' => 'info', 'viewing' => 'warning', 'application' => 'secondary',
            'won' => 'success', 'lost' => 'danger', 'expired' => 'dark',
        ][$status] ?? 'dark';
    }

    public function properties()
    {
        return $this->hasOne(Property::class, 'id', 'property_id');
    }

    public function units()
    {
        return $this->hasOne(PropertyUnit::class, 'id', 'unit_id');
    }

    public function viewings()
    {
        return $this->hasMany(LeadViewing::class, 'lead_id', 'id')->orderBy('viewing_date', 'desc');
    }

    public function applications()
    {
        return $this->hasMany(RentalApplication::class, 'lead_id', 'id')->orderBy('id', 'desc');
    }

    public function touchActivity()
    {
        $this->last_activity_at = now();
        $this->save();
    }
}
