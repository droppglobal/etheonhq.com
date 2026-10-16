<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileDevice extends Model
{
    protected $fillable = ['user_id', 'parent_id', 'token_hash', 'fcm_token', 'platform', 'app_version', 'device_name', 'last_seen_at'];
}
