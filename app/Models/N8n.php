<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class N8n extends Model
{
    use HasFactory;

    public static function method()
    {
        return [
            'GET' => __('GET'),
            'POST' => __('POST'),
            'PATCH' => __('PATCH'),
            'PUT' => __('PUT'),
            'HEAD' => __('HEAD')
        ];
    }

    public static $module = [
        'create_user' => 'Create User',
        'create_tenant' => 'Create Tenant',
        'create_maintainer' => 'Create Maintainer',
        'create_maintenance_request' => 'New Maintenance Request',
        'maintenance_request_complete' => 'New Maintenance Complete',
        'create_invoice' => 'Create Invoice',
        'payment_reminder' => 'Payment Reminder',
    ];
}
