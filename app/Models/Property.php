<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'type',
        'amenities_id',
        'advantage_id',
        'country',
        'state',
        'city',
        'zip_code',
        'listing_type',
        'price',
        'address',
        'parent_id',
        'is_active',
    ];

    public static function types()
    {
        return [
            '' => __('Select Type'),
            'own_property' => __('Own Property'),
            'lease_property' => __('Lease Property'),
        ];
    }

    public function thumbnail()
    {
        return $this->hasOne('App\Models\PropertyImage', 'property_id', 'id')->where('type', 'thumbnail');
    }

    public function propertyImages()
    {
        return $this->hasMany('App\Models\PropertyImage', 'property_id', 'id')->where('type', 'extra');
    }

    public function totalUnit()
    {
        return $this->hasMany('App\Models\PropertyUnit', 'property_id', 'id')->count();
    }
    public function totalUnits()
    {
        return $this->hasMany('App\Models\PropertyUnit', 'property_id', 'id');
    }
    public function totalRoom()
    {
        $units = $this->totalUnits;

        $totalUnit = 0;
        foreach ($units as $unit) {
            $totalUnit += $unit->bedroom;
        }
        return $totalUnit;
    }

    public function vacantUnit()
    {
        return $this->hasMany(PropertyUnit::class)->where('is_occupied', 0)->count();
    }

    public function occupiedUnit()
    {
        return $this->hasMany(PropertyUnit::class)->where('is_occupied', 1)->count();
    }
    public function Amenities()
    {
        return $this->hasMany(Amenity::class)->where('amenities_id', 'id');
    }

    public function Advantanges()
    {
        return $this->hasMany(Advantage::class)->where('type', 'Excluded');
    }
}
