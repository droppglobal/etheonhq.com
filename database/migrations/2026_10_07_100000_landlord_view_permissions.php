<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

// Every owner gets the default Landlord role (like Tenant and Maintainer): landlords can view their own
// properties, units, tenants, invoices, expenses and repairs.
return new class extends Migration
{
    public function up()
    {
        foreach (User::where('type', 'owner')->pluck('id') as $ownerId) {
            defaultLandlordCreate($ownerId);
        }
    }

    public function down()
    {
        $keep = ['manage account settings', 'manage password settings', 'manage 2FA settings'];
        $ids = \Spatie\Permission\Models\Permission::whereIn('name', array_diff(defaultLandlordPermissions(), $keep))->pluck('id');
        foreach (Role::where('name', 'landlord')->get() as $role) {
            $role->permissions()->detach($ids);
        }
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
