<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class VisitorPass extends Model
{
    protected $fillable = [
        'tenant_id', 'property_id', 'unit_id', 'visitor_name', 'visitor_phone', 'purpose', 'vehicle_number',
        'visit_date', 'code', 'status', 'checked_in_at', 'checked_out_at', 'checked_in_by', 'parent_id',
    ];

    public static function statuses()
    {
        return ['active' => __('Expected'), 'checked_in' => __('Inside'), 'checked_out' => __('Left'), 'cancelled' => __('Cancelled')];
    }

    public static function statusColor($status)
    {
        return ['active' => 'info', 'checked_in' => 'success', 'checked_out' => 'secondary', 'cancelled' => 'danger'][$status] ?? 'secondary';
    }

    // 6-character code without look-alike characters (0/O, 1/I)
    public static function newCode($parentId)
    {
        do {
            $code = '';
            for ($i = 0; $i < 6; $i++) {
                $code .= 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'[random_int(0, 31)];
            }
        } while (self::where('parent_id', $parentId)->where('code', $code)->exists());
        return $code;
    }

    public function tenants()
    {
        return $this->hasOne(Tenant::class, 'id', 'tenant_id');
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
