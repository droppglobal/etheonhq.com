<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentIncrease extends Model
{
    protected $fillable = ['parent_id', 'tenant_id', 'unit_id', 'old_rent', 'new_rent', 'effective_date', 'notes', 'notice_sent_at', 'applied_at', 'status', 'created_by'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function unit()
    {
        return $this->belongsTo(PropertyUnit::class, 'unit_id');
    }

    public function changePercent()
    {
        return $this->old_rent > 0 ? round(($this->new_rent - $this->old_rent) / $this->old_rent * 100, 1) : null;
    }

    public static function statuses()
    {
        return ['scheduled' => __('Scheduled'), 'applied' => __('Applied'), 'cancelled' => __('Cancelled')];
    }

    /**
     * Puts the new rent into effect on the unit. Recurring invoices pick it up when they are next copied
     * (see CronjobController::recurringInvoice), so invoices already issued are never changed.
     */
    public function apply()
    {
        if ($this->status != 'scheduled') {
            return false;
        }
        if ($unit = PropertyUnit::find($this->unit_id)) {
            $unit->rent = $this->new_rent;
            $unit->save();
        }
        $this->status = 'applied';
        $this->applied_at = now();
        $this->save();
        return true;
    }
}
