<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inspection extends Model
{
    protected $fillable = [
        'tenant_id', 'property_id', 'unit_id', 'type', 'inspection_date', 'status', 'notes',
        'tenant_signed_name', 'tenant_signed_at', 'tenant_signed_ip', 'parent_id',
    ];

    public static function types()
    {
        return [
            'move_in' => __('Move-in'),
            'move_out' => __('Move-out'),
            'routine' => __('Routine'),
        ];
    }

    public static function conditions()
    {
        return [
            'good' => __('Good'),
            'fair' => __('Fair'),
            'poor' => __('Poor'),
            'damaged' => __('Damaged'),
            'na' => __('N/A'),
        ];
    }

    public static function conditionColor($condition)
    {
        return ['good' => 'success', 'fair' => 'info', 'poor' => 'warning', 'damaged' => 'danger'][$condition] ?? 'secondary';
    }

    // Default checklist used when a new inspection is created
    public static function defaultChecklist()
    {
        $common = [__('Walls & Ceiling'), __('Floor'), __('Windows'), __('Doors & Locks'), __('Lights & Switches')];
        return [
            __('Living Room') => $common,
            __('Kitchen') => array_merge($common, [__('Sink & Taps'), __('Cabinets'), __('Appliances')]),
            __('Bedroom') => array_merge($common, [__('Wardrobe')]),
            __('Bathroom') => array_merge($common, [__('Toilet'), __('Shower / Bath'), __('Sink & Taps')]),
            __('Exterior') => [__('Entrance'), __('Balcony / Garden'), __('Parking')],
            __('Keys & Meters') => [__('Keys handed over'), __('Electricity meter'), __('Water meter')],
        ];
    }

    public function items()
    {
        return $this->hasMany(InspectionItem::class, 'inspection_id', 'id')->orderBy('sort_order')->orderBy('id');
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

    // The move-in inspection of the same tenancy, used to compare a move-out against
    public function baseline()
    {
        return self::where('parent_id', $this->parent_id)
            ->where('tenant_id', $this->tenant_id)
            ->where('unit_id', $this->unit_id)
            ->where('type', 'move_in')
            ->where('id', '!=', $this->id)
            ->orderBy('id', 'desc')
            ->first();
    }
}
