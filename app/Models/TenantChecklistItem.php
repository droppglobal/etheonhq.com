<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantChecklistItem extends Model
{
    protected $fillable = ['parent_id', 'tenant_id', 'item', 'done_at', 'done_by', 'note'];

    public function doneBy()
    {
        return $this->belongsTo(User::class, 'done_by');
    }
}
