<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListingAlert extends Model
{
    protected $fillable = ['parent_id', 'lead_id', 'name', 'email', 'filters', 'token', 'sent_ids', 'last_sent_at', 'unsubscribed_at'];

    protected $casts = ['filters' => 'array', 'sent_ids' => 'array', 'last_sent_at' => 'datetime', 'unsubscribed_at' => 'datetime'];
}
