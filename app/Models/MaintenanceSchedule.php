<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class MaintenanceSchedule extends Model
{
    protected $fillable = [
        'title', 'property_id', 'unit_id', 'issue_type', 'maintainer_id', 'vendor_id', 'frequency',
        'next_date', 'last_run_date', 'is_active', 'notes', 'parent_id',
    ];

    public static function frequencies()
    {
        return [
            'weekly' => __('Weekly'),
            'monthly' => __('Monthly'),
            'quarterly' => __('Every 3 months'),
            'half_yearly' => __('Every 6 months'),
            'yearly' => __('Yearly'),
        ];
    }

    public static function advance($date, $frequency)
    {
        $date = Carbon::parse($date);
        switch ($frequency) {
            case 'weekly':
                return $date->addWeek();
            case 'quarterly':
                return $date->addMonthsNoOverflow(3);
            case 'half_yearly':
                return $date->addMonthsNoOverflow(6);
            case 'yearly':
                return $date->addYearNoOverflow();
            default:
                return $date->addMonthNoOverflow();
        }
    }

    public function properties()
    {
        return $this->hasOne(Property::class, 'id', 'property_id');
    }

    public function units()
    {
        return $this->hasOne(PropertyUnit::class, 'id', 'unit_id');
    }

    public function maintainer()
    {
        return $this->hasOne(User::class, 'id', 'maintainer_id');
    }

    public function vendor()
    {
        return $this->hasOne(Vendor::class, 'id', 'vendor_id');
    }

    public function types()
    {
        return $this->hasOne(Type::class, 'id', 'issue_type');
    }
}
