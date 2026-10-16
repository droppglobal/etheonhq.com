<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepositTransaction extends Model
{
    protected $fillable = [
        'tenant_id',
        'property_id',
        'unit_id',
        'type',
        'amount',
        'date',
        'notes',
        'parent_id',
    ];

    public static function types()
    {
        return [
            'received' => __('Deposit Received'),
            'deduction' => __('Deduction'),
            'refund' => __('Refund'),
        ];
    }

    // Amount still held for the tenant: received minus deductions and refunds.
    public static function balance($tenantId)
    {
        $rows = self::where('tenant_id', $tenantId)->get();
        $received = $rows->where('type', 'received')->sum('amount');
        $out = $rows->whereIn('type', ['deduction', 'refund'])->sum('amount');
        return $received - $out;
    }

    public function tenant()
    {
        return $this->hasOne(Tenant::class, 'id', 'tenant_id');
    }
}
