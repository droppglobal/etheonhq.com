<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FrontHomePage extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'content_value',
        'enabled',
        'parent_id',
        'sort_order',
        'translations',
    ];
}
