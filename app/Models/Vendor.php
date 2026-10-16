<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $fillable = ['name', 'company', 'email', 'phone', 'service', 'address', 'notes', 'is_active', 'parent_id'];

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'vendor_id', 'id');
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class, 'vendor_id', 'id');
    }
}
