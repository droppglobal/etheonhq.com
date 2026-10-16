<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditNote extends Model
{
    protected $fillable = ['parent_id', 'invoice_id', 'number', 'type', 'amount', 'date', 'method', 'payment_id', 'reason', 'created_by'];

    public static function types()
    {
        return ['credit' => __('Credit note'), 'refund' => __('Refund')];
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function displayNumber()
    {
        return ($this->type == 'refund' ? 'RF-' : 'CN-') . str_pad($this->number, 4, '0', STR_PAD_LEFT);
    }

    public static function nextNumber($parentId)
    {
        return (int) self::where('parent_id', $parentId)->lockForUpdate()->max('number') + 1;
    }
}
