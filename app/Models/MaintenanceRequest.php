<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceRequest extends Model
{
    use HasFactory;
    protected $fillable = [
        'property_id',
        'unit_id',
        'issue_type',
        'maintainer_id',
        'status',
        'amount',
        'issue_attachment',
        'invoice',
        'notes',
        'parent_id',
        'request_date',
        'fixed_date',
        'parent_id',
    ];

    public static function slots()
    {
        return [
            'any' => __('Any time'),
            'morning' => __('Morning (8–12)'),
            'afternoon' => __('Afternoon (12–4)'),
            'evening' => __('Evening (4–8)'),
        ];
    }

    public function preferredVisitText()
    {
        // "any time" with no date is not a preference worth showing
        if (!$this->preferred_date && (!$this->preferred_slot || $this->preferred_slot == 'any')) {
            return null;
        }
        return trim(($this->preferred_date ? dateFormat($this->preferred_date) : '') . ' ' . (self::slots()[$this->preferred_slot] ?? ''));
    }

    public static function status()
    {
        return [
            'pending' => __('Pending'),
            'in_progress' => __('In Progress'),
            'completed' => __('Completed'),
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

    public function types()
    {
        return $this->hasOne(Type::class, 'id', 'issue_type');
    }

    public function maintainers()
    {
        return $this->hasOne(User::class, 'id', 'maintainer_id');
    }

    public function tenetData()
    {
        return $this->hasOne(Tenant::class, 'id', 'tenant_id');
    }

    public function vendor()
    {
        return $this->hasOne(Vendor::class, 'id', 'vendor_id');
    }

    public function comments()
    {
        return $this->hasMany('App\Models\Comments', 'maintenance_id', 'id');
    }
}
