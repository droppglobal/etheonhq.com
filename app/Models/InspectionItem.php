<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectionItem extends Model
{
    protected $fillable = ['inspection_id', 'area', 'item', 'condition', 'notes', 'photo', 'sort_order'];
}
