<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteReview extends Model
{
    protected $fillable = ['parent_id', 'user_id', 'name', 'role', 'rating', 'review', 'status'];

    public static function statuses()
    {
        return [
            'pending' => __('Waiting for approval'),
            'approved' => __('Shown on website'),
            'hidden' => __('Hidden'),
        ];
    }
}
