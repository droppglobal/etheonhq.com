<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandlordProfile extends Model
{
    protected $fillable = ['user_id', 'management_fee_percent', 'bank_details', 'notes', 'parent_id'];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
