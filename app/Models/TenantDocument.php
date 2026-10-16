<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantDocument extends Model
{
    protected $fillable=[
        'document',
        'title',
        'document_type',
        'expiry_date',
        'expiry_reminder_sent',
        'property_id',
        'tenant_id',
        'parent_id',
    ];
    use HasFactory;

    public static function documentTypes()
    {
        return [
            'id_proof' => __('ID Proof'),
            'address_proof' => __('Address Proof'),
            'police_verification' => __('Police Verification'),
            'insurance' => __('Insurance'),
            'income_proof' => __('Income Proof'),
            'other' => __('Other'),
        ];
    }

    public function isExpired()
    {
        return !empty($this->expiry_date) && \Carbon\Carbon::parse($this->expiry_date)->lt(\Carbon\Carbon::today());
    }
}
