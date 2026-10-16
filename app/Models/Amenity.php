<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Amenity extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'description',
        'parent_id',

    ];


    public function properties()
    {
        return $this->belongsToMany(Property::class, 'amenity_property', 'amenity_id', 'property_id');
    }
}
