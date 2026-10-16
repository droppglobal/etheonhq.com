<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandlordPayout extends Model
{
    protected $fillable = ['landlord_id', 'amount', 'payout_date', 'method', 'reference', 'notes', 'parent_id'];
}
