<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssistantMessage extends Model
{
    protected $fillable = ['parent_id', 'tenant_id', 'role', 'content', 'channel', 'staff_id', 'needs_reply'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public static function log(Tenant $tenant, $role, $content, $channel = 'web', array $extra = [])
    {
        return self::create(array_merge([
            'parent_id' => $tenant->parent_id,
            'tenant_id' => $tenant->id,
            'role' => $role,
            'content' => mb_substr((string) $content, 0, 4000),
            'channel' => $channel,
        ], $extra));
    }

    // conversations where the assistant handed over to a person and nobody has answered yet
    public static function waitingCount($parentId)
    {
        return self::where('parent_id', $parentId)->where('needs_reply', 1)->distinct('tenant_id')->count('tenant_id');
    }
}
