<?php

namespace Database\Seeders;

use App\Models\Maintainer;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DefaultDataUsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $currentRouteName = Route::currentRouteName();
        if ($currentRouteName != 'LaravelUpdater::database') {

            // Default All Permission
            $allPermission = [
                [
                    'name' => 'manage user',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'create user',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'edit user',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'delete user',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'show user',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage role',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'create role',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'edit role',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'delete role',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage contact',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'create contact',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'edit contact',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'delete contact',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage note',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'create note',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'edit note',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'delete note',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage logged history',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'delete logged history',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage pricing packages',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'create pricing packages',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'edit pricing packages',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'delete pricing packages',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'buy pricing packages',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage pricing transation',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage coupon',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'create coupon',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'edit coupon',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'delete coupon',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage coupon history',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'delete coupon history',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage account settings',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage password settings',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage general settings',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage company settings',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage email settings',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage payment settings',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage seo settings',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage google recaptcha settings',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage storage settings',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage notification',
                    'gaurd_name' => 'web',
                ],
                [
                    'name' => 'edit notification',
                    'gaurd_name' => 'web',
                ],
                [
                    'name' => 'manage FAQ',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'create FAQ',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'edit FAQ',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'delete FAQ',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage Page',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'create Page',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'edit Page',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'delete Page',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'show Page',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage home page',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'edit home page',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage footer',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'edit footer',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage 2FA settings',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage auth page',
                    'guard_name' => 'web',
                ],


                [
                    'name' => 'manage property',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'create property',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'edit property',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'delete property',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'show property',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage unit',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'create unit',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'edit unit',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'show unit',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'delete unit',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage tenant',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'create tenant',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'edit tenant',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'delete tenant',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'show tenant',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage invoice',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'create invoice',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'edit invoice',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'delete invoice',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'show invoice',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage maintainer',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'create maintainer',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'edit maintainer',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'delete maintainer',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage maintenance request',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'create maintenance request',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'edit maintenance request',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'delete maintenance request',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'show maintenance request',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'delete invoice type',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'create invoice payment',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'delete invoice payment',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage expense',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'create expense',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'edit expense',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'delete expense',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'show expense',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'manage types',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'create types',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'edit types',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'delete types',
                    'guard_name' => 'web',
                ],

            ];
            Permission::insert($allPermission);

            // Default Super Admin Role
            $superAdminRoleData =  [
                'name' => 'super admin',
                'parent_id' => 0,
            ];
            $systemSuperAdminRole = Role::create($superAdminRoleData);
            $systemSuperAdminPermission = [
                ['name' => 'manage user'],
                ['name' => 'create user'],
                ['name' => 'edit user'],
                ['name' => 'delete user'],
                ['name' => 'show user'],
                ['name' => 'manage contact'],
                ['name' => 'create contact'],
                ['name' => 'edit contact'],
                ['name' => 'delete contact'],
                ['name' => 'manage note'],
                ['name' => 'create note'],
                ['name' => 'edit note'],
                ['name' => 'delete note'],
                ['name' => 'manage pricing packages'],
                ['name' => 'create pricing packages'],
                ['name' => 'edit pricing packages'],
                ['name' => 'delete pricing packages'],
                ['name' => 'manage pricing transation'],
                ['name' => 'manage coupon'],
                ['name' => 'create coupon'],
                ['name' => 'edit coupon'],
                ['name' => 'delete coupon'],
                ['name' => 'manage coupon history'],
                ['name' => 'delete coupon history'],
                ['name' => 'manage account settings'],
                ['name' => 'manage password settings'],
                ['name' => 'manage general settings'],
                ['name' => 'manage email settings'],
                ['name' => 'manage payment settings'],
                ['name' => 'manage seo settings'],
                ['name' => 'manage google recaptcha settings'],
                ['name' => 'manage storage settings'],
                ['name' => 'manage FAQ'],
                ['name' => 'create FAQ'],
                ['name' => 'edit FAQ'],
                ['name' => 'delete FAQ'],
                ['name' => 'manage Page'],
                ['name' => 'create Page'],
                ['name' => 'edit Page'],
                ['name' => 'delete Page'],
                ['name' => 'show Page'],
                ['name' => 'manage home page'],
                ['name' => 'edit home page'],
                ['name' => 'manage footer'],
                ['name' => 'edit footer'],
                ['name' => 'manage 2FA settings'],
                ['name' => 'manage auth page'],


            ];
            $systemSuperAdminRole->givePermissionTo($systemSuperAdminPermission);
            // Default Super Admin
            $superAdminData =     [
                'first_name' => 'Super Admin',
                'email' => 'superadmin@gmail.com',
                'password' => Hash::make('123456'),
                'type' => 'super admin',
                'lang' => 'english',
                'email_verified_at' => now(),
                'profile' => 'avatar.png',
            ];
            $systemSuperAdmin = User::create($superAdminData);
            $systemSuperAdmin->assignRole($systemSuperAdminRole);
            HomePageSection();
            CustomPage();
            authPage($systemSuperAdmin->id);
            DefaultBankTransferPayment();

            // Default Owner Role
            $ownerRoleData = [
                'name' => 'owner',
                'parent_id' => $systemSuperAdmin->id,
            ];
            $systemOwnerRole = Role::create($ownerRoleData);

            // Default Owner All Permissions
            $systemOwnerPermission = [
                ['name' => 'manage user'],
                ['name' => 'create user'],
                ['name' => 'edit user'],
                ['name' => 'delete user'],
                ['name' => 'manage role'],
                ['name' => 'create role'],
                ['name' => 'edit role'],
                ['name' => 'delete role'],
                ['name' => 'manage contact'],
                ['name' => 'create contact'],
                ['name' => 'edit contact'],
                ['name' => 'delete contact'],
                ['name' => 'manage note'],
                ['name' => 'create note'],
                ['name' => 'edit note'],
                ['name' => 'delete note'],
                ['name' => 'manage logged history'],
                ['name' => 'delete logged history'],
                ['name' => 'manage pricing packages'],
                ['name' => 'buy pricing packages'],
                ['name' => 'manage pricing transation'],
                ['name' => 'manage account settings'],
                ['name' => 'manage payment settings'],

                ['name' => 'manage password settings'],
                ['name' => 'manage general settings'],
                ['name' => 'manage company settings'],
                ['name' => 'manage email settings'],
                ['name' => 'manage notification'],
                ['name' => 'edit notification'],
                ['name' => 'manage 2FA settings'],


                ['name' => 'manage property'],
                ['name' => 'create property'],
                ['name' => 'edit property'],
                ['name' => 'delete property'],
                ['name' => 'show property'],
                ['name' => 'manage unit'],
                ['name' => 'create unit'],
                ['name' => 'edit unit'],
                ['name' => 'show unit'],
                ['name' => 'delete unit'],
                ['name' => 'manage tenant'],
                ['name' => 'create tenant'],
                ['name' => 'edit tenant'],
                ['name' => 'delete tenant'],
                ['name' => 'show tenant'],
                ['name' => 'manage invoice'],
                ['name' => 'create invoice'],
                ['name' => 'edit invoice'],
                ['name' => 'delete invoice'],
                ['name' => 'show invoice'],
                ['name' => 'manage maintainer'],
                ['name' => 'create maintainer'],
                ['name' => 'edit maintainer'],
                ['name' => 'delete maintainer'],
                ['name' => 'manage maintenance request'],
                ['name' => 'create maintenance request'],
                ['name' => 'edit maintenance request'],
                ['name' => 'delete maintenance request'],
                ['name' => 'show maintenance request'],
                ['name' => 'delete invoice type'],
                ['name' => 'create invoice payment'],
                ['name' => 'delete invoice payment'],
                ['name' => 'manage expense'],
                ['name' => 'create expense'],
                ['name' => 'edit expense'],
                ['name' => 'delete expense'],
                ['name' => 'show expense'],
                ['name' => 'manage types'],
                ['name' => 'create types'],
                ['name' => 'edit types'],
                ['name' => 'delete types'],

            ];
            $systemOwnerRole->givePermissionTo($systemOwnerPermission);

            // Default Owner Create
            $ownerData =    [
                'first_name' => 'Owner',
                'email' => 'owner@gmail.com',
                'password' => Hash::make('123456'),
                'type' => 'owner',
                'lang' => 'english',
                'email_verified_at' => now(),
                'profile' => 'avatar.png',
                'code' => uniqid(),
                'subscription' => 1,
                'parent_id' => $systemSuperAdmin->id,
                'subscription_expire_date' => Carbon::now()->addMonths(1)->isoFormat('YYYY-MM-DD'),
            ];
            $systemOwner = User::create($ownerData);

            // Default Template Assign
            defaultTemplate($systemOwner->id);

            // Default frontend Template
            FrontHomePageSection($systemOwner->id);
            AdditionalPageSection($systemOwner->id);


            // Default Owner Role Assign
            $systemOwner->assignRole($systemOwnerRole);


            // Default Owner Role
            $managerRoleData =  [
                'name' => 'manager',
                'parent_id' => $systemOwner->id,
            ];
            $systemManagerRole = Role::create($managerRoleData);
            // Default Manager All Permissions
            $systemManagerPermission = [
                ['name' => 'manage user'],
                ['name' => 'create user'],
                ['name' => 'edit user'],
                ['name' => 'delete user'],
                ['name' => 'manage contact'],
                ['name' => 'create contact'],
                ['name' => 'edit contact'],
                ['name' => 'delete contact'],
                ['name' => 'manage note'],
                ['name' => 'create note'],
                ['name' => 'edit note'],
                ['name' => 'delete note'],
                ['name' => 'manage 2FA settings'],

                ['name' => 'manage property'],
                ['name' => 'create property'],
                ['name' => 'edit property'],
                ['name' => 'delete property'],
                ['name' => 'show property'],
                ['name' => 'manage unit'],
                ['name' => 'create unit'],
                ['name' => 'edit unit'],
                ['name' => 'show unit'],
                ['name' => 'delete unit'],
                ['name' => 'manage tenant'],
                ['name' => 'create tenant'],
                ['name' => 'edit tenant'],
                ['name' => 'delete tenant'],
                ['name' => 'show tenant'],
                ['name' => 'manage invoice'],
                ['name' => 'create invoice'],
                ['name' => 'edit invoice'],
                ['name' => 'delete invoice'],
                ['name' => 'show invoice'],
                ['name' => 'manage maintainer'],
                ['name' => 'create maintainer'],
                ['name' => 'edit maintainer'],
                ['name' => 'delete maintainer'],
                ['name' => 'manage maintenance request'],
                ['name' => 'create maintenance request'],
                ['name' => 'edit maintenance request'],
                ['name' => 'delete maintenance request'],
                ['name' => 'show maintenance request'],
                ['name' => 'delete invoice type'],
                ['name' => 'create invoice payment'],
                ['name' => 'delete invoice payment'],
                ['name' => 'manage expense'],
                ['name' => 'create expense'],
                ['name' => 'edit expense'],
                ['name' => 'delete expense'],
                ['name' => 'show expense'],
                ['name' => 'manage types'],
                ['name' => 'create types'],
                ['name' => 'edit types'],
                ['name' => 'delete types'],

            ];
            $systemManagerRole->givePermissionTo($systemManagerPermission);

            // Default Manager Create
            $managerData =   [
                'first_name' => 'Manager',
                'email' => 'manager@gmail.com',
                'password' => Hash::make('123456'),
                'type' => 'manager',
                'lang' => 'english',
                'email_verified_at' => now(),
                'profile' => 'avatar.png',
                'subscription' => 0,
                'parent_id' => $systemOwner->id,
            ];
            $systemManager = User::create($managerData);
            // Default Manager Role Assign
            $systemManager->assignRole($systemManagerRole);



            // Default Tenant role
            $systemTenantRole = defaultTenantCreate($systemOwner->id);
            // Default Tenant
            $tenantData =  [
                'first_name' => 'Tenant',
                'email' => 'tenant@gmail.com',
                'password' => Hash::make('123456'),
                'type' => 'tenant',
                'lang' => 'english',
                'email_verified_at' => now(),
                'profile' => 'avatar.png',
                'parent_id' => $systemOwner->id,
            ];
            $systemTenant = User::create($tenantData);
            $systemTenantDetail = new Tenant();
            $systemTenantDetail->user_id = $systemTenant->id;
            $systemTenantDetail->parent_id = $systemOwner->id;
            $systemTenantDetail->save();
            // Default tenant role assign
            $systemTenant->assignRole($systemTenantRole);

            // Default Maintainer role
            $systemTenantRole = defaultMaintainerCreate($systemOwner->id);
            // Default admin
            $maintainerData = [
                'first_name' => 'Maintainer',
                'email' => 'maintainer@gmail.com',
                'password' => Hash::make('123456'),
                'type' => 'maintainer',
                'lang' => 'english',
                'email_verified_at' => now(),
                'profile' => 'avatar.png',
                'parent_id' => $systemOwner->id,
            ];
            $systemMaintainer = User::create($maintainerData);
            $systemMaintainerDetail = new Maintainer();
            $systemMaintainerDetail->user_id = $systemMaintainer->id;
            $systemMaintainerDetail->parent_id = $systemOwner->id;
            $systemMaintainerDetail->save();

            // Default admin role assign
            $systemMaintainer->assignRole($systemTenantRole);

            // Default Landlord role
            $systemLandlordRole = defaultLandlordCreate($systemOwner->id);
            // Default Landlord (view-only access to the properties assigned to them)
            $landlordData = [
                'first_name' => 'Landlord',
                'email' => 'landlord@gmail.com',
                'password' => Hash::make('123456'),
                'type' => 'landlord',
                'lang' => 'english',
                'email_verified_at' => now(),
                'profile' => 'avatar.png',
                'parent_id' => $systemOwner->id,
            ];
            $systemLandlord = User::create($landlordData);
            \App\Models\LandlordProfile::create([
                'user_id' => $systemLandlord->id,
                'management_fee_percent' => 0,
                'parent_id' => $systemOwner->id,
            ]);
            // Default landlord role assign
            $systemLandlord->assignRole($systemLandlordRole);

            // Subscription default data
            $subscriptionData = [
                'title' => 'Basic',
                'package_amount' => 0,
                'interval' => 'Unlimited',
                'user_limit' => 10,
                'property_limit' => 10,
                'tenant_limit' => 10,
                'enabled_logged_history' => 1,
                'enabled_openai' => 1,
                'enabled_n8n' => 1,
            ];
            \App\Models\Subscription::create($subscriptionData);
            NewPermission();
        } else {
            NewPermission();
        }
    }
}
