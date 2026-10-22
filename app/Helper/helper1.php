<?php

use App\Mail\Common;
use App\Mail\EmailVerification;
use App\Mail\TestMail;
use App\Models\Additional;
use App\Models\Agreement;
use App\Models\AuthPage;
use App\Models\Custom;
use App\Models\FAQ;
use App\Models\FrontHomePage;
use App\Models\HomePage;
use App\Models\LoggedHistory;
use App\Models\N8n;
use App\Models\Notification;
use App\Models\Page;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use PragmaRX\Google2FAQRCode\Google2FA;
use Spatie\Permission\Models\Role;

use App\Models\Invoice;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Permission;
use Twilio\Rest\Client;

if (!function_exists('settingsKeys')) {
    function settingsKeys()
    {
        return $settingsKeys = [
            "app_name" => "",
            "theme_mode" => "light",
            "layout_font" => "Roboto",
            "accent_color" => "preset-6",
            "color_type" => "preset",
            "custom_color" => "--primary-rgb: 0,0,0",
            "custom_color_code" => "#000000",
            "sidebar_caption" => "true",
            "theme_layout" => "ltr",
            "layout_width" => "false",
            "owner_email_verification" => "off",
            "landing_page" => "on",
            "register_page" => "on",
            "company_logo" => "logo.png",
            "logo" => "logo.png",
            "company_favicon" => "favicon.png",
            "landing_logo" => "landing_logo.png",
            "light_logo" => "light_logo.png",
            "meta_seo_title" => "",
            "meta_seo_keyword" => "",
            "meta_seo_description" => "",
            "meta_seo_image" => "",
            "company_date_format" => "M j, Y",
            "company_time_format" => "g:i A",
            "company_name" => "",
            "company_phone" => "",
            "company_address" => "",
            "company_email" => "",
            "company_email_from_name" => "",
            "google_recaptcha" => "off",
            "recaptcha_key" => "",
            "recaptcha_secret" => "",
            'SERVER_DRIVER' => "",
            'SERVER_HOST' => "",
            'SERVER_PORT' => "",
            'SERVER_USERNAME' => "",
            'SERVER_PASSWORD' => "",
            'SERVER_ENCRYPTION' => "",
            'FROM_EMAIL' => "",
            'FROM_NAME' => "",
            "invoice_number_prefix" => "#INV-000",
            "expense_number_prefix" => "#EXP-000",
            "agreement_number_prefix" => "#AGR-000",
            'CURRENCY' => "USD",
            'CURRENCY_SYMBOL' => "$",
            'STRIPE_PAYMENT' => "off",
            'STRIPE_KEY' => "",
            'STRIPE_SECRET' => "",
            "paypal_payment" => "off",
            "paypal_mode" => "",
            "paypal_client_id" => "",
            "paypal_secret_key" => "",
            "bank_transfer_payment" => "off",
            "bank_name" => "",
            "bank_holder_name" => "",
            "bank_account_number" => "",
            "bank_ifsc_code" => "",
            "bank_other_details" => "",
            "flutterwave_payment" => "off",
            "flutterwave_public_key" => "",
            "flutterwave_secret_key" => "",
            "timezone" => "",
            "footer_column_1" => "Quick Links",
            "footer_column_1_enabled" => "active",
            "footer_column_2" => "Help",
            "footer_column_2_enabled" => "active",
            "footer_column_3" => "OverView",
            "footer_column_3_enabled" => "active",
            "footer_column_4" => "Core System",
            "footer_column_4_enabled" => "active",
            "pricing_feature" => "on",
            "copyright" => "",

            // New Setting
            "aws_s3_key" => "",
            "aws_s3_secret" => "",
            "aws_s3_region" => "",
            "aws_s3_bucket" => "",
            "aws_s3_url" => "",
            "aws_s3_endpoint" => "",
            "aws_s3_file_type" => "jpg,jpeg,png,xlsx,xls,csv,pdf",
            "local_file_type" => "jpg,jpeg,png,xlsx,xls,csv,pdf",
            "wasabi_key" => "",
            "wasabi_secret" => "",
            "wasabi_region" => "",
            "wasabi_bucket" => "",
            "wasabi_url" => "",

            // "wasabi_root" => "",
            "wasabi_file_type" => "jpg,jpeg,png,xlsx,xls,csv,pdf",
            "storage_type" => "local",
            "paystack_payment" => "off",
            "paystack_public_key" => "",
            "paystack_secret_key" => "",
            'twilio_sid' => '',
            'twilio_token' => '',

            'razorpay_api_key' => '',
            'razorpay_secret_key' => '',
            'razorpay_payment' => '',

            'twilio_from_number' => '',
            'twilio_whatsapp_from_number' => '',
            'terms_condition' => 'The tenant agrees to maintain the rented property in good condition and promptly report any maintenance issues to the landlord or property manager. Rent must be paid in full on or before the agreed due date each month. The landlord is responsible for providing timely maintenance and repairs for any structural or essential service issues. Both parties agree that this agreement is subject to and governed by all applicable local tenancy laws and regulations.',
            'agreement_description' => 'This default agreement outlines the essential terms and responsibilities for property rental within the Property Management System. It establishes clear expectations regarding rent payments, property maintenance, permitted modifications, and legal compliance to protect the interests of both the landlord and the tenant. This template serves as the baseline contract for all rental arrangements managed through the system.',
            'openai_secret_key' => '',

            'landlord_signature' => '',
            'landlord_signature_date' => '',
            'cronjob' => '',
            'cronjob_last_execution' => '',
            'tax_number' => '',
            'late_fee_enabled' => 'off',
            'late_fee_grace_days' => '0',
            'document_expiry_reminder_days' => '30',
            'lead_expiry_days' => '30',
        ];
    }
}

if (!function_exists('settings')) {
    function settings($userId = 0)
    {
        $settingData = DB::table('settings');
        if (empty($userId)) {
            if (\Auth::check()) {
                $userId = parentId();
                $settingData = $settingData->where('parent_id', $userId);
            } else {
                // public pages and cron jobs act for one owner account (see useAccountSettings)
                $settingData = $settingData->where('parent_id', app()->bound('settings.account') ? app('settings.account') : 1);
            }
        } else {
            $settingData = $settingData->where('parent_id', $userId);
        }
        $settingData = $settingData->get();
        $details = settingsKeys();

        foreach ($settingData as $row) {
            $details[$row->name] = $row->value;
        }

        config(
            [
                'captcha.secret' => $details['recaptcha_secret'],
                'captcha.sitekey' => $details['recaptcha_key'],
                'options' => [
                    'timeout' => 30,
                ]
            ]
        );

        return $details;
    }
}
if (!function_exists('openAiSettings')) {
    function openAiSettings()
    {
        $settingData = DB::table('settings')->where('name', 'openai_secret_key')->where('parent_id', '=', 1)->get();
        foreach ($settingData as $setting) {
            $result[$setting->name] = $setting->value;
        }
        return $result;
    }
}
if (!function_exists('subscriptionPaymentSettings')) {
    function subscriptionPaymentSettings()
    {
        $settingData = DB::table('settings')->where('type', 'payment')->where('parent_id', '=', 1)->get();
        $result = [
            'CURRENCY' => "USD",
            'CURRENCY_SYMBOL' => "$",
            'STRIPE_PAYMENT' => "off",
            'STRIPE_KEY' => "",
            'STRIPE_SECRET' => "",
            "paypal_payment" => "off",
            "paypal_mode" => "",
            "paypal_client_id" => "",
            "paypal_secret_key" => "",
            "bank_transfer_payment" => "off",
            "bank_name" => "",
            "bank_holder_name" => "",
            "bank_account_number" => "",
            "bank_ifsc_code" => "",
            "bank_other_details" => "",
            "flutterwave_payment" => "off",
            "flutterwave_public_key" => "",
            "flutterwave_secret_key" => "",
            "paystack_payment" => "off",
            "paystack_public_key" => "",
            "paystack_secret_key" => "",
            'razorpay_api_key' => '',
            'razorpay_secret_key' => '',
            'razorpay_payment' => '',
        ];

        foreach ($settingData as $setting) {
            $result[$setting->name] = $setting->value;
        }

        return $result;
    }
}
if (!function_exists('invoicePaymentSettings')) {
    function invoicePaymentSettings($id)
    {
        $settingData = DB::table('settings')->where('type', 'payment')->where('parent_id', $id)->get();
        $result = [
            'CURRENCY' => "USD",
            'CURRENCY_SYMBOL' => "$",
            'STRIPE_PAYMENT' => "off",
            'STRIPE_KEY' => "",
            'STRIPE_SECRET' => "",
            "paypal_payment" => "off",
            "paypal_mode" => "",
            "paypal_client_id" => "",
            "paypal_secret_key" => "",
            "bank_transfer_payment" => "off",
            "bank_name" => "",
            "bank_holder_name" => "",
            "bank_account_number" => "",
            "bank_ifsc_code" => "",
            "bank_other_details" => "",
            "flutterwave_payment" => "off",
            "flutterwave_public_key" => "",
            "flutterwave_secret_key" => "",
            "paystack_payment" => "off",
            "paystack_public_key" => "",
            "paystack_secret_key" => "",
            'razorpay_api_key' => '',
            'razorpay_secret_key' => '',
            'razorpay_payment' => '',
            'tax_title' => '',
            'tax_number' => ''
        ];

        foreach ($settingData as $row) {
            $result[$row->name] = $row->value;
        }
        return $result;
    }
}

if (!function_exists('getSettingsValByName')) {
    function getSettingsValByName($key)
    {
        $setting = settings();
        if (!isset($setting[$key]) || empty($setting[$key])) {
            $setting[$key] = '';
        }

        return $setting[$key];
    }
}

if (!function_exists('getSettingsValByIdName')) {
    function getSettingsValByIdName($id, $key)
    {
        $setting = settingsById($id);
        if (!isset($setting[$key]) || empty($setting[$key])) {
            $setting[$key] = '';
        }

        return $setting[$key];
    }
}

if (!function_exists('settingDateFormat')) {
    function settingDateFormat($settings, $date)
    {
        return date($settings['company_date_format'], strtotime($date));
    }
}
if (!function_exists('settingPriceFormat')) {
    function settingPriceFormat($settings, $price)
    {
        return $settings['CURRENCY_SYMBOL'] . $price;
    }
}
if (!function_exists('settingTimeFormat')) {
    function settingTimeFormat($settings, $time)
    {
        return date($settings['company_time_format'], strtotime($time));
    }
}
if (!function_exists('dateFormat')) {
    function dateFormat($date)
    {
        $settings = settings();

        return date($settings['company_date_format'], strtotime($date));
    }
}
if (!function_exists('timeFormat')) {
    function timeFormat($time)
    {
        $settings = settings();

        return date($settings['company_time_format'], strtotime($time));
    }
}
if (!function_exists('priceFormat')) {
    function priceFormat($price)
    {
        $settings = settings();

        return $settings['CURRENCY_SYMBOL'] . $price;
    }
}
if (!function_exists('priceFormatFor')) {
    // priceFormat() for a given owner's settings (no logged-in user, e.g. webhooks and cron)
    function priceFormatFor($price, $settings)
    {
        return ($settings['CURRENCY_SYMBOL'] ?? '') . (floor($price) == $price ? (int) $price : number_format((float) $price, 2, '.', ''));
    }
}
if (!function_exists('useAccountSettings')) {
    // When nobody is logged in, makes settings(), priceFormat(), dateFormat(), invoicePrefix()... use this owner's settings
    function useAccountSettings($parentId)
    {
        app()->instance('settings.account', (int) $parentId);
    }
}
if (!function_exists('parentId')) {
    function parentId()
    {
        if (\Auth::user()->type == 'owner' || \Auth::user()->type == 'super admin') {
            return \Auth::user()->id;
        } else {
            return \Auth::user()->parent_id;
        }
    }
}
if (!function_exists('landlordPropertyIds')) {
    // A landlord sees only the properties assigned to them: their ids, or null for every other user (no extra limit)
    function landlordPropertyIds()
    {
        $user = \Auth::user();
        if (!$user || $user->type != 'landlord') {
            return null;
        }
        return \App\Models\Property::where('parent_id', $user->parent_id)->where('landlord_id', $user->id)->pluck('id')->all();
    }
}
if (!function_exists('scopeLandlord')) {
    // Limits a query to the logged-in landlord's properties ($column holds the property id); unchanged for other users
    function scopeLandlord($query, $column = 'property_id')
    {
        $ids = landlordPropertyIds();
        return $ids === null ? $query : $query->whereIn($column, $ids ?: [0]);
    }
}
if (!function_exists('assignSubscription')) {
    function assignSubscription($id)
    {
        $subscription = Subscription::find($id);

        if ($subscription) {
            \Auth::user()->subscription = $subscription->id;
            if ($subscription->interval == 'Monthly') {
                \Auth::user()->subscription_expire_date = Carbon::now()->addMonths(1)->isoFormat('YYYY-MM-DD');
            } elseif ($subscription->interval == 'Quarterly') {
                \Auth::user()->subscription_expire_date = Carbon::now()->addMonths(3)->isoFormat('YYYY-MM-DD');
            } elseif ($subscription->interval == 'Yearly') {
                \Auth::user()->subscription_expire_date = Carbon::now()->addYears(1)->isoFormat('YYYY-MM-DD');
            } else {
                \Auth::user()->subscription_expire_date = Carbon::now()->addMonths(1)->isoFormat('YYYY-MM-DD');
            }
            \Auth::user()->save();

            SetLimit($subscription, auth()->user()->id);

            return [
                'is_success' => true,
            ];
        } else {
            return [
                'is_success' => false,
                'error' => 'Subscription is deleted.',
            ];
        }
    }
}
if (!function_exists('assignManuallySubscription')) {
    function assignManuallySubscription($id, $userId)
    {
        $owner = User::find($userId);
        $subscription = Subscription::find($id);
        if ($subscription) {
            $owner->subscription = $subscription->id;
            if ($subscription->interval == 'Monthly') {
                $owner->subscription_expire_date = Carbon::now()->addMonths(1)->isoFormat('YYYY-MM-DD');
            } elseif ($subscription->interval == 'Quarterly') {
                $owner->subscription_expire_date = Carbon::now()->addMonths(3)->isoFormat('YYYY-MM-DD');
            } elseif ($subscription->interval == 'Yearly') {
                $owner->subscription_expire_date = Carbon::now()->addYears(1)->isoFormat('YYYY-MM-DD');
            } else {
                $owner->subscription_expire_date = Carbon::now()->addMonths(1)->isoFormat('YYYY-MM-DD');
            }
            $owner->save();

            SetLimit($subscription, $userId);

            return [
                'is_success' => true,
            ];
        } else {
            return [
                'is_success' => false,
                'error' => 'Subscription is deleted.',
            ];
        }
    }
}

if (!function_exists('SetLimit')) {
    function SetLimit($subscription, $id)
    {
        // set user_limit
        $user_limit = $subscription->user_limit;
        $users = User::where('parent_id', '=', $id)->whereNotIn('type', ['super admin', 'owner', 'tenant', 'maintainer'])->update(['is_active' => 0]);
        $users = User::where('parent_id', '=', $id)->whereNotIn('type', ['super admin', 'owner', 'tenant', 'maintainer'])->take($user_limit)->get()->each(function ($user) {
            $user->update(['is_active' => 1]);
        });

        // set tenant
        $tenant_limit = $subscription->tenant_limit;
        $tenant = User::where('parent_id', '=', $id)->whereIn('type', ['tenant'])->update(['is_active' => 0]);
        $tenant = User::where('parent_id', '=', $id)->whereIn('type', ['tenant'])->take($tenant_limit)->get()->each(function ($tenant) {
            $tenant->update(['is_active' => 1]);
        });

        // set Property
        $property_limit = $subscription->property_limit;
        $property = Property::where('parent_id', '=', $id)->update(['is_active' => 0]);
        $property = Property::where('parent_id', '=', $id)->take($property_limit)->get()->each(function ($property) {
            $property->update(['is_active' => 1]);
        });
    }
}

if (!function_exists('smtpDetail')) {
    function smtpDetail($id)
    {
        $settings = emailSettings($id);

        $smtpDetail = config(
            [
                'mail.mailers.smtp.transport' => $settings['SERVER_DRIVER'],
                'mail.mailers.smtp.host' => $settings['SERVER_HOST'],
                'mail.mailers.smtp.port' => $settings['SERVER_PORT'],
                'mail.mailers.smtp.encryption' => $settings['SERVER_ENCRYPTION'],
                'mail.mailers.smtp.username' => $settings['SERVER_USERNAME'],
                'mail.mailers.smtp.password' => $settings['SERVER_PASSWORD'],
                'mail.from.address' => $settings['FROM_EMAIL'],
                'mail.from.name' => $settings['FROM_NAME'],
            ]
        );

        return $smtpDetail;
    }
}

if (!function_exists('invoicePrefix')) {
    function invoicePrefix()
    {
        $settings = settings();
        return $settings["invoice_number_prefix"];
    }
}
if (!function_exists('expensePrefix')) {
    function expensePrefix()
    {
        $settings = settings();
        return $settings["expense_number_prefix"];
    }
}
if (!function_exists('agreementPrefix')) {
    function agreementPrefix()
    {
        $settings = settings();
        return $settings["agreement_number_prefix"];
    }
}
if (!function_exists('timeCalculation')) {
    function timeCalculation($startDate, $startTime, $endDate, $endTime)
    {
        $startdate = $startDate . ' ' . $startTime;
        $enddate = $endDate . ' ' . $endTime;

        $startDateTime = new DateTime($startdate);
        $endDateTime = new DateTime($enddate);

        $interval = $startDateTime->diff($endDateTime);
        $totalHours = $interval->h + $interval->i / 60;

        return number_format($totalHours, 2);
    }
}

if (!function_exists('setup')) {
    function setup()
    {
        $setupPath = storage_path() . "/installed";
        return $setupPath;
    }
}

if (!function_exists('userLoggedHistory')) {
    function userLoggedHistory()
    {
        $serverip = $_SERVER['REMOTE_ADDR'];
        $data = @unserialize(file_get_contents('http://ip-api.com/php/' . $serverip));
        if (isset($data['status']) && $data['status'] == 'success') {
            $browser = new \WhichBrowser\Parser($_SERVER['HTTP_USER_AGENT']);
            if ($browser->device->type == 'bot') {
                return redirect()->intended(RouteServiceProvider::HOME);
            }
            $referrerData = isset($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER']) : null;
            $data['browser'] = $browser->browser->name ?? null;
            $data['os'] = $browser->os->name ?? null;
            $data['language'] = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? mb_substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : null;
            $data['device'] = User::getDevice($_SERVER['HTTP_USER_AGENT']);
            $data['referrer_host'] = !empty($referrerData['host']);
            $data['referrer_path'] = !empty($referrerData['path']);
            $result = json_encode($data);
            $details = new LoggedHistory();
            $details->type = Auth::user()->type;
            $details->user_id = Auth::user()->id;
            $details->date = date('Y-m-d H:i:s');
            $details->Details = $result;
            $details->ip = $serverip;
            $details->parent_id = parentId();
            $details->save();
        }
    }
}
if (!function_exists('settingsById')) {

    function settingsById($userId)
    {
        $data = DB::table('settings');
        $data = $data->where('parent_id', $userId);
        $data = $data->get();
        $settings = settingsKeys();

        foreach ($data as $row) {
            $settings[$row->name] = $row->value;
        }

        config(
            [
                'captcha.secret' => $settings['recaptcha_key'],
                'captcha.sitekey' => $settings['recaptcha_secret'],
                'options' => [
                    'timeout' => 30,
                ],
            ]
        );

        return $settings;
    }
}

if (!function_exists('ensurePermissions')) {
    // Creates the permissions that do not exist yet (a fresh install creates the newer ones only later, in NewPermission())
    function ensurePermissions(array $names)
    {
        $missing = array_diff($names, Permission::whereIn('name', $names)->where('guard_name', 'web')->pluck('name')->all());
        foreach ($missing as $name) {
            Permission::create(['name' => $name, 'guard_name' => 'web']);
        }
        if ($missing) {
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }
}

if (!function_exists('defaultTenantCreate')) {
    function defaultTenantCreate($id)
    {
        // Default Tenant role
        $tenantRoleData = [
            'name' => 'tenant',
            'parent_id' => $id,
        ];
        $systemTenantRole = Role::create($tenantRoleData);
        // Default Tenant permissions
        $systemTenantPermissions = [
            ['name' => 'manage invoice'],
            ['name' => 'show invoice'],
            ['name' => 'manage contact'],
            ['name' => 'create contact'],
            ['name' => 'edit contact'],
            ['name' => 'delete contact'],
            ['name' => 'manage note'],
            ['name' => 'create invoice payment'],
            ['name' => 'manage maintenance request'],
            ['name' => 'create maintenance request'],
            ['name' => 'edit maintenance request'],
            ['name' => 'delete maintenance request'],
            ['name' => 'show maintenance request'],
            ['name' => 'manage account settings'],
            ['name' => 'manage password settings'],
            ['name' => 'manage 2FA settings'],
            ['name' => 'manage agreement'],
            ['name' => 'show agreement'],
            ['name' => 'manage inspection'],
            ['name' => 'book facility'],
            ['name' => 'create visitor pass'],
            ['name' => 'vote poll'],

        ];
        ensurePermissions(array_column($systemTenantPermissions, 'name'));
        $systemTenantRole->givePermissionTo($systemTenantPermissions);
        return $systemTenantRole;
    }
}

if (!function_exists('defaultStaffRolesCreate')) {
    /**
     * Ready-made staff roles for an owner account. Only missing roles are created: a role the owner
     * already has (and may have changed) is never touched.
     */
    function defaultStaffRolesCreate($ownerId)
    {
        $common = ['manage account settings', 'manage password settings', 'manage 2FA settings', 'manage contact', 'create contact', 'edit contact', 'manage note',
            'manage tenant', 'show tenant', 'manage property', 'show property', 'manage unit', 'show unit'];
        $roles = [
            // money: invoices, payments, expenses, deposits and the financial reports
            'accountant' => array_merge($common, ['manage invoice', 'create invoice', 'edit invoice', 'show invoice', 'create invoice payment', 'delete invoice payment', 'delete invoice type',
                'manage expense', 'create expense', 'edit expense', 'delete expense', 'show expense', 'manage deposit', 'create deposit', 'manage vendor', 'manage meter reading', 'create meter reading',
                'manage landlord', 'manage income report', 'manage expense report', 'manage profile and loss report', 'manage profit loss report', 'manage ledger report', 'manage tax report',
                'manage arrears report', 'manage rent roll report', 'manage payout report']),
            // reception: visitors, parking, bookings, repairs intake, enquiries and tenant chats
            'front desk' => array_merge($common, ['create note', 'manage maintenance request', 'create maintenance request', 'show maintenance request', 'manage visitor', 'manage parking',
                'manage facility', 'manage lead', 'create lead', 'edit lead', 'manage rental application', 'manage calendar', 'manage inspection', 'create inspection', 'manage assistant chats']),
        ];
        foreach ($roles as $name => $permissions) {
            if (Role::where('parent_id', $ownerId)->where('name', $name)->exists()) {
                continue;
            }
            $role = Role::create(['name' => $name, 'parent_id' => $ownerId]);
            $role->givePermissionTo(Permission::whereIn('name', array_unique($permissions))->where('guard_name', 'web')->get()->unique('name'));
        }
    }
}

if (!function_exists('defaultLandlordPermissions')) {
    // Landlords only look: their own properties, units, tenants, invoices, expenses and repairs
    // (every list and page is limited to their properties with scopeLandlord()).
    function defaultLandlordPermissions()
    {
        return [
            'manage account settings', 'manage password settings', 'manage 2FA settings',
            'manage property', 'show property', 'manage unit', 'show unit', 'manage tenant', 'show tenant',
            'manage invoice', 'show invoice', 'manage expense', 'show expense',
            'manage maintenance request', 'show maintenance request',
        ];
    }
}

if (!function_exists('defaultLandlordCreate')) {
    // Default Landlord role of an owner account (created when missing; its permissions are topped up)
    function defaultLandlordCreate($id)
    {
        ensurePermissions(defaultLandlordPermissions());
        $role = Role::firstOrCreate(['name' => 'landlord', 'parent_id' => $id, 'guard_name' => 'web']);
        // some installs hold the same permission name twice: both rows are given
        $role->permissions()->syncWithoutDetaching(Permission::whereIn('name', defaultLandlordPermissions())->where('guard_name', 'web')->pluck('id'));
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        return $role;
    }
}

if (!function_exists('defaultMaintainerCreate')) {
    function defaultMaintainerCreate($id)
    {
        $maintainerRoleData = [
            'name' => 'maintainer',
            'parent_id' => $id,
        ];
        $systemMaintainerRole = Role::create($maintainerRoleData);
        // Default admin permissions
        $systemMaintainerPermissions = [
            ['name' => 'manage maintenance request'],
            ['name' => 'show maintenance request'],
            ['name' => 'manage contact'],
            ['name' => 'create contact'],
            ['name' => 'edit contact'],
            ['name' => 'delete contact'],
            ['name' => 'manage note'],
            ['name' => 'manage account settings'],
            ['name' => 'manage password settings'],
            ['name' => 'manage 2FA settings'],
        ];
        $systemMaintainerRole->givePermissionTo($systemMaintainerPermissions);
        return $systemMaintainerRole;
    }
}

if (!function_exists('defaultTemplateList')) {
    function defaultTemplateList()
    {

        return [
            'user_create' =>
                [
                    'module' => 'user_create',
                    'name' => 'New User',
                    'short_code' => ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{new_user_name}', '{username}', '{password}', '{app_link}'],
                    'subject' => 'Welcome to {company_name}!',
                    'templete' => '<p><strong>Dear {new_user_name}</strong>,</p><p>&nbsp;</p><blockquote><p>Welcome We are excited to have you on board and look forward to providing you with an exceptional experience.</p><p>We hope you enjoy your experience with us. If you have any feedback, feel free to share it with us.</p><p>&nbsp;</p><p>Your account details are as follows:</p><p><strong>App Link:</strong> <a href="{app_link}">{app_link}</a></p><p><strong>Username:</strong> {username}</p><p><strong>Password:</strong> {password}</p><p>&nbsp;</p><p>Thank you for choosing {company_name}!</p></blockquote>',
                    'sms_message' => 'Hi {new_user_name}, welcome to {company_name}! App: {app_link} Username: {username} Password: {password}',
                ],
            'tenant_create' =>
                [
                    'module' => 'tenant_create',
                    'name' => 'New Tenant',
                    'short_code' => ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{user_name}', '{username}', '{password}', '{app_link}'],
                    'subject' => 'Welcome to {company_name}!',
                    'templete' => '<p><strong>Dear {user_name}</strong>,</p><p>&nbsp;</p><blockquote><p>Welcome We are excited to have you on board and look forward to providing you with an exceptional experience.</p><p>We hope you enjoy your experience with us. If you have any feedback, feel free to share it with us.</p><p>&nbsp;</p><p>Your account details are as follows:</p><p><strong>App Link:</strong> <a href="{app_link}">{app_link}</a></p><p><strong>Username:</strong> {username}</p><p><strong>Password:</strong> {password}</p><p>&nbsp;</p><p>Thank you for choosing {company_name}!</p></blockquote>',
                    'sms_message' => 'Hi {username},welcome to {company_name}!App: {app_link}Username: {username}Password: {password}',
                ],
            'maintainer_create' =>
                [
                    'module' => 'maintainer_create',
                    'name' => 'New Maintainer',
                    'short_code' => ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{user_name}', '{username}', '{password}', '{app_link}'],
                    'subject' => 'Welcome to {company_name}!',
                    'templete' => '<p><strong>Dear {user_name}</strong>,</p><p>&nbsp;</p><blockquote><p>Welcome We are excited to have you on board and look forward to providing you with an exceptional experience.</p><p>We hope you enjoy your experience with us. If you have any feedback, feel free to share it with us.</p><p>&nbsp;</p><p>Your account details are as follows:</p><p><strong>App Link:</strong> <a href="{app_link}">{app_link}</a></p><p><strong>Username:</strong> {username}</p><p><strong>Password:</strong> {password}</p><p>&nbsp;</p><p>Thank you for choosing {company_name}!</p></blockquote>',
                    'sms_message' => 'Hi {username},welcome to {company_name}!App: {app_link}Username: {username}Password: {password}',
                ],
            'maintenance_request_create' =>
                [
                    'module' => 'maintenance_request_create',
                    'name' => 'New Maintenance Request',
                    'short_code' => ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{tenant_name}', '{created_at}', '{issue_type}', '{issue_description}', '{tenant_number}', '{tenant_mail}'],
                    'subject' => 'New Maintenance Request Created',
                    'templete' => '<p><strong class="ql-size-huge">Dear Owner,</strong></p><p>We would like to inform you that a new maintenance request has been created for your property. Below are the details of the request:</p><p><strong>Request Details:</strong></p><ul><li>	Submitted By: {tenant_name}</li><li>	Submitted On: {created_at}</li><li>	Category: {issue_type}</li><li>	Description: {issue_description}</li></ul><p><br></p><p><strong>Tenant Contact Information:</strong></p><ul><li>	Name: {tenant_name}</li><li>	Phone: {tenant_number}</li><li>	Email: {tenant_mail}</li></ul><p>Thank you for your attention to this matter.</p><p><strong>Best regards,</strong></p><p><strong>{tenant_name}</strong></p><p><strong>{tenant_mail}</strong></p>',
                    'sms_message' => 'New maintenance request from {tenant_name} for your property. Category: {issue_type}. Submitted on {created_at}.',
                ],
            'maintenance_request_complete' =>
                [
                    'module' => 'maintenance_request_complete',
                    'name' => 'Maintenance Request Complete',
                    'short_code' => ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{tenant_name}', '{submitted_at}', '{issue_type}', '{issue_description}', '{completed_at}', '{maintainer_email}', '{maintainer_number}', '{maintainer_name}'],
                    'subject' => 'Maintenance Request Completed!',
                    'templete' => '<p><strong>Dear {tenant_name},</strong></p><p><br></p><p>We are pleased to inform you that your maintenance request has been successfully completed.</p><p><br></p><p> <strong>Request Details:</strong></p><ul><li>Submitted By: {tenant_name}</li><li>Submitted On: {submitted_at}</li><li>Category: {issue_type}</li><li>Description: {issue_description}</li></ul><p><br></p><p><strong>Completion Details:</strong></p><ul><li> Completed On: {completed_at}</li> </ul><p><br></p><p><strong>Feedback:</strong></p><p>We value your feedback to improve our services. Please let us know if you are satisfied with the maintenance performed or if there are any further issues that need attention.</p><p><br></p><p><strong>Contact Information:</strong></p><p> If you have any questions or need further assistance, please contact us at {maintainer_email} or {maintainer_number}.</p><p>Thank you for your cooperation and patience.</p><p><br></p><p><strong>Best regards,</strong></p><p>{maintainer_name}</p><p>{maintainer_email}</p>',
                    'sms_message' => 'Dear {tenant_name}, your maintenance request (Category: {issue_type}) submitted on {submitted_at} has been completed on {completed_at}.',
                ],
            'invoice_create' =>
                [
                    'module' => 'invoice_create',
                    'name' => 'New Invoice',
                    'short_code' => ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{user_name}', '{invoice_number}', '{invoice_date}', '{invoice_due_date}', '{invoice_description}', '{amount}', '{payment_link}'],
                    'subject' => 'Invoice Created',
                    'templete' => '<p><strong>Dear {user_name},</strong></p><p><br></p><p> We are pleased to inform you that an invoice has been created  with {company_name}.</p><p><br></p><p><strong> Invoice Details:</strong></p><ul><li>Invoice Number: {invoice_number}</li><li>Date Issued: {invoice_date}</li><li>Due Date: {invoice_due_date}</li><li>Amount Due: {amount}</li><li>Description: {invoice_description}</li></ul><p><br></p><p><strong>Contact Information:</strong></p><p>If you have any questions or need further assistance, please contact our billing department at {company_email} or {company_number}.</p><p><br></p><p>Thank you for your prompt attention to this matter.</p><p><br></p><p><strong>Best regards,</strong></p><p><strong>{company_name}</strong></p><p><strong>{company_email}</strong></p>',
                    'sms_message' => 'Dear {user_name}, your invoice #{invoice_number} from {company_name} for {amount} has been issued on {invoice_date}, due by {invoice_due_date}.',
                ],
            'payment_reminder' =>
                [
                    'module' => 'payment_reminder',
                    'name' => 'Payment Reminder',
                    'short_code' => ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{user_name}', '{invoice_number}', '{invoice_date}', '{invoice_due_date}', '{amount}', '{invoice_description}', '{payment_link}'],
                    'subject' => 'Friendly Reminder: Payment Due Soon',
                    'templete' => '<p><strong>Dear {user_name},</strong></p><p><br></p><p> This is a friendly reminder that your payment for the following invoice is due soon:</p><p><br></p><p><strong>Invoice Details:</strong></p><ul><li>Invoice Number: {invoice_number}</li><li>Date Issued: {invoice_date}</li><li>Due Date: {invoice_due_date}</li><li>Amount Due: {amount}</li><li>Description: {invoice_description}</li></ul><p><br></p><p><br></p><p>If you have already made your payment, please disregard this notice. Otherwise, we appreciate your prompt attention to this matter.</p><p><br></p><p><strong>Contact Information:</strong></p><p>If you have any questions or need assistance, feel free to contact our billing department at {company_email} or {company_number}.</p><p><br></p><p>Thank you for your cooperation!</p><p><br></p><p><strong>Best regards,</strong></p><p><strong>{company_name}</strong></p><p><strong>{company_email}</strong></p>',
                    'sms_message' => 'Dear {user_name}, your payment for invoice #{invoice_number} ({amount}) is due on {invoice_due_date}.',
                ],
            'lease_expiry_reminder' => [
                'module' => 'lease_expiry_reminder',
                'name' => 'Lease Expiry Reminder',
                'short_code' => ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{user_name}', '{property_name}', '{unit_name}', '{lease_start_date}', '{lease_end_date}', '{days_left}'],
                'subject' => 'Lease Expiry Reminder',
                'templete' => '<p><strong>Dear {user_name},</strong></p><p><br></p><p>This is a friendly reminder that your lease agreement with <strong>{company_name}</strong> is approaching its expiry date.</p><p><br></p><p><strong>Lease Details:</strong></p><ul><li>Property: {property_name}</li><li>Unit: {unit_name}</li><li>Lease Start Date: {lease_start_date}</li><li>Lease End Date: {lease_end_date}</li><li>Days Remaining: {days_left}</li></ul><p><br></p><p>Please make the necessary arrangements regarding renewal or vacating the premises before the expiry date.</p><p><br></p><p><strong>Contact Information:</strong></p><p>If you have any questions or wish to renew your lease, please contact us at {company_email} or {company_phone_number}.</p><p><br></p><p>We appreciate your continued association with us.</p><p><br></p><p><strong>Best regards,</strong></p><p><strong>{company_name}</strong></p><p><strong>{company_email}</strong></p>',
                'sms_message' => 'Dear {user_name}, lease for {property_name} ({unit_name}) ends on {lease_end_date}. {days_left} days left. Contact {company_name}.',
            ],
        ];
    }
}

if (!function_exists('defaultTemplate')) {
    function defaultTemplate($id)
    {
        $templateData = defaultTemplateList();
        $createdTemplates = [];

        foreach ($templateData as $key => $value) {
            $existing = Notification::where('module', $value['module'])
                ->where('parent_id', $id)
                ->first();

            if ($existing) {
                continue;
            }

            $template = new Notification();
            $template->module = $value['module'];
            $template->name = $value['name'];
            $template->subject = $value['subject'];
            $template->message = $value['templete'];
            $template->short_code = json_encode($value['short_code']);
            $template->enabled_email = 0;
            $template->parent_id = $id;

            if (!empty($value['sms_message'])) {
                $template->sms_message = $value['sms_message'];
            }

            $template->enabled_sms = 0;
            $template->enabled_whatsapp = 0;
            $template->save();

            $createdTemplates[] = $template;
        }

        return $createdTemplates;
    }
}

if (!function_exists('defaultSMSTemplate')) {
    function defaultSMSTemplate()
    {
        $templateData = defaultTemplateList();
        $createdTemplates = [];

        foreach ($templateData as $key => $value) {
            $Users = User::where('type', 'owner')->get();
            foreach ($Users as $User) {
                $template = Notification::where('module', $value['module'])->where('parent_id', $User->id)->first();
                if (empty($template)) {
                    $template = new Notification();
                    $template->module = $value['module'];
                    $template->name = $value['name'];
                    $template->subject = $value['subject'];
                    $template->message = $value['templete'];
                    $template->short_code = json_encode($value['short_code']);
                    $template->enabled_email = 0;
                    $template->enabled_sms = 0;
                    $template->enabled_whatsapp = 0;
                    $template->parent_id = $User->id;
                    $template->save();
                }
            }
            Notification::where('module', $value['module'])->whereNull('sms_message')->update(['sms_message' => $value['sms_message'], 'enabled_sms' => 0]);
            $createdTemplates[] = $value;
        }

        return $createdTemplates;
    }
}

if (!function_exists('MessageReplace')) {
    function MessageReplace($notification, $id = 0)
    {
        $return['subject'] = $notification->subject;
        $return['message'] = $notification->message;
        $return['sms_message'] = $notification->sms_message;
        if (!empty($notification->password)) {
            $notification['password'] = $notification->password;
        }
        $settings = settings();
        if (!empty($notification)) {
            $search = [];
            $replace = [];

            if ($notification->module == 'user_create') {
                $user = User::find($id);
                $search = ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{new_user_name}', '{username}', '{password}', '{app_link}'];
                $replace = [$settings['company_name'], $settings['company_email'], $settings['company_phone'], $settings['company_address'], $settings['CURRENCY_SYMBOL'], $user->name, $user->email, $notification['password'], env('APP_URL')];
            }
            if ($notification->module == 'tenant_create') {
                $user = User::find($id);
                $search = ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{user_name}', '{username}', '{password}', '{app_link}'];
                $replace = [$settings['company_name'], $settings['company_email'], $settings['company_phone'], $settings['company_address'], $settings['CURRENCY_SYMBOL'], $user->name, $user->email, $notification['password'], env('APP_URL')];
            }
            if ($notification->module == 'maintainer_create') {
                $user = User::find($id);
                $search = ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{user_name}', '{username}', '{password}', '{app_link}'];
                $replace = [$settings['company_name'], $settings['company_email'], $settings['company_phone'], $settings['company_address'], $settings['CURRENCY_SYMBOL'], $user->name, $user->email, $notification['password'], env('APP_URL')];
            }
            if ($notification->module == 'maintenance_request_create') {
                $user_name = User::where('id', $notification->user_id)->first();
                $MaintenanceRequest = MaintenanceRequest::find($id);
                $issue_type = $MaintenanceRequest->types->title;
                $tenamt_name = !empty($MaintenanceRequest->tenetData->user->name) ? $MaintenanceRequest->tenetData->user->name : '';
                $tenamt_number = !empty($MaintenanceRequest->tenetData->user->phone_number) ? $MaintenanceRequest->tenetData->user->phone_number : '';
                $tenamt_mail = !empty($MaintenanceRequest->tenetData->user->email) ? $MaintenanceRequest->tenetData->user->email : '';
                $search = ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{tenant_name}', '{created_at}', '{issue_type}', '{issue_description}', '{tenant_number}', '{tenant_mail}'];
                $replace = [$settings['company_name'], $settings['company_email'], $settings['company_phone'], $settings['company_address'], $settings['CURRENCY_SYMBOL'], $user_name->first_name, $MaintenanceRequest->created_at, $issue_type, $MaintenanceRequest->notes, $tenamt_number, $user_name->email];
            }
            if ($notification->module == 'maintenance_request_complete') {
                $MaintenanceRequest = MaintenanceRequest::find($id);
                $issue_type = $MaintenanceRequest->types->title;
                $tenamt_name = !empty($MaintenanceRequest->tenetData->user->name) ? $MaintenanceRequest->tenetData->user->name : '';
                $tenamt_number = !empty($MaintenanceRequest->tenetData->user->phone_number) ? $MaintenanceRequest->tenetData->user->phone_number : '';
                $tenamt_mail = !empty($MaintenanceRequest->tenetData->user->email) ? $MaintenanceRequest->tenetData->user->email : '';
                $maintainer_email = !empty($MaintenanceRequest->maintainers->email) ? $MaintenanceRequest->maintainers->email : '';
                $maintainer_number = !empty($MaintenanceRequest->maintainers->phone_number) ? $MaintenanceRequest->maintainers->phone_number : '';
                $maintainers_name = !empty($MaintenanceRequest->maintainers->name) ? $MaintenanceRequest->maintainers->name : '';
                $search = ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{tenant_name}', '{submitted_at}', '{issue_type}', '{issue_description}', '{completed_at}', '{maintainer_email}', '{maintainer_number}', '{maintainer_name}'];
                $replace = [$settings['company_name'], $settings['company_email'], $settings['company_phone'], $settings['company_address'], $settings['CURRENCY_SYMBOL'], $tenamt_name, $MaintenanceRequest->created_at, $issue_type, $MaintenanceRequest->notes, $MaintenanceRequest->updated_at, $maintainer_email, $maintainer_number, $maintainers_name];
            }
            if ($notification->module == 'invoice_create') {
                $invoice = Invoice::find($id);
                $user_name = $invoice->tenants->user->name;
                $invoice_number = invoicePrefix() . $invoice->invoice_id;
                $search = ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{user_name}', '{invoice_number}', '{invoice_date}', '{invoice_due_date}', '{invoice_description}', '{amount}'];
                $replace = [$settings['company_name'], $settings['company_email'], $settings['company_phone'], $settings['company_address'], $settings['CURRENCY_SYMBOL'], $user_name, $invoice_number, $invoice->created_at, $invoice->end_date, $invoice->notes, priceFormat($invoice->getInvoiceDueAmount())];
            }
            if ($notification->module == 'invoice_create') {
                $invoice = Invoice::find($id);
                $user_name = $invoice->tenants->user->name;
                $invoice_number = invoicePrefix() . $invoice->invoice_id;
                $search = ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{user_name}', '{invoice_number}', '{invoice_date}', '{invoice_due_date}', '{invoice_description}', '{amount}'];
                $replace = [$settings['company_name'], $settings['company_email'], $settings['company_phone'], $settings['company_address'], $settings['CURRENCY_SYMBOL'], $user_name, $invoice_number, $invoice->created_at, $invoice->end_date, $invoice->notes, priceFormat($invoice->getInvoiceDueAmount())];
            }
            if ($notification->module == 'payment_reminder') {
                $invoice = Invoice::find($id);
                $user_name = $invoice->tenants->user->name;
                $invoice_number = invoicePrefix() . $invoice->invoice_id;
                $search = ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{user_name}', '{invoice_number}', '{invoice_date}', '{invoice_due_date}', '{amount}', '{invoice_description}'];
                $replace = [$settings['company_name'], $settings['company_email'], $settings['company_phone'], $settings['company_address'], $settings['CURRENCY_SYMBOL'], $user_name, $invoice_number, $invoice->created_at, $invoice->end_date, priceFormat($invoice->getInvoiceDueAmount()), $invoice->notes];
            }

            if ($notification->module == 'lease_expiry_reminder') {
                $tenant = Tenant::find($id);
                $user_name = $tenant->user->full_name;
                $endDate = Carbon::parse($tenant->lease_end_date);
                $today = Carbon::today();
                $days_left = $today->diffInDays($endDate, false);
                $search = ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{currency_symbol}', '{user_name}', '{property_name}', '{unit_name}', '{lease_start_date}', '{lease_end_date}', '{days_left}'];
                $replace = [$settings['company_name'], $settings['company_email'], $settings['company_phone'], $settings['company_address'], $settings['CURRENCY_SYMBOL'], $user_name, $tenant->properties->name, $tenant->units->name, dateFormat($tenant->lease_start_date), dateFormat($tenant->lease_end_date), $days_left];
            }

            $return['subject'] = str_replace($search, $replace, $notification->subject);
            $return['message'] = str_replace($search, $replace, $notification->message);
            $return['sms_message'] = str_replace($search, $replace, $notification->sms_message);
        }

        return $return;
    }
}

if (!function_exists('send_twilio_msg')) {
    function send_twilio_msg($to, $msg, $parentId = null)
    {
        if (!empty($msg) && !empty($to)) {
            $settings = $parentId ? settingsById($parentId) : settings();
            if (empty($settings['twilio_sid']) || empty($settings['twilio_token'])) {
                return;
            }

            $sid = $settings['twilio_sid'];
            $token = $settings['twilio_token'];
            $from_number = $settings['twilio_from_number'];

            try {
                $client = new Client($sid, $token);
                $client->messages->create($to, [
                    'from' => $from_number,
                    'body' => $msg,
                ]);
            } catch (\Exception $e) {
                \Log::error('Twilio SMS send failed: ' . $e->getMessage());
            }
        }
    }
}

if (!function_exists('sendTwilioWhatsappMsg')) {
    function sendTwilioWhatsappMsg($params)
    {
        try {
            // cron jobs have no logged-in user, so they pass the account explicitly
            $settings = !empty($params['parent_id']) ? settingsById($params['parent_id']) : settings();
            $sid = $settings['twilio_sid'] ?? '';
            $token = $settings['twilio_token'] ?? '';
            $from = $settings['twilio_whatsapp_from_number'] ?? '';
            if (empty($sid) || empty($token) || empty($from) || empty($params['to'])) {
                return false;
            }

            $client = new Client($sid, $token);

            $to = $params['to'] ?? null;
            $body = $params['body'] ?? '';

            $to = str_starts_with($to, 'whatsapp:') ? $to : 'whatsapp:' . $to;
            $from = str_starts_with($from, 'whatsapp:') ? $from : 'whatsapp:' . $from;

            $client->messages->create($to, [
                'from' => $from,
                'body' => $body,
            ]);

            return true;

        } catch (\Exception $e) {
            \Log::error('Twilio WhatsApp send failed: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('sendEmail')) {
    function sendEmail($to, $datas)
    {
        $datas['settings'] = settings();
        try {
            emailSettings(parentId());
            Mail::to($to)->send(new TestMail($datas));
            return [
                'status' => 'success',
                'message' => __('Email successfully sent'),
            ];
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            return [
                'status' => 'error',
                'message' => __('We noticed that the email settings have not been configured for this system. As a result, email-related functionalities may not work as expected. please add valide email smtp details first.')
            ];
        }
    }
}


if (!function_exists('commonEmailSend')) {
    function commonEmailSend($to, $datas)
    {
        $datas['settings'] = settings();
        try {
            if (Auth::check()) {
                if ($datas['module'] == 'owner_create') {
                    emailSettings(1);
                } else {
                    emailSettings(parentId());
                }
            } else {
                emailSettings($datas['parent_id']);
            }
            Mail::to($to)->send(new Common($datas));
            return [
                'status' => 'success',
                'message' => __('Email successfully sent'),
            ];
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            return [
                'status' => 'error',
                'message' => __('We noticed that the email settings have not been configured for this system. As a result, email-related functionalities may not work as expected. please add valide email smtp details first.')
            ];
        }
    }
}


if (!function_exists('emailSettings')) {
    function emailSettings($id)
    {
        $settingData = DB::table('settings')
            ->where('type', 'smtp')
            ->where('parent_id', $id)
            ->get();

        $result = [
            'FROM_EMAIL' => "",
            'FROM_NAME' => "",
            'SERVER_DRIVER' => "",
            'SERVER_HOST' => "",
            'SERVER_PORT' => "",
            'SERVER_USERNAME' => "",
            'SERVER_PASSWORD' => "",
            'SERVER_ENCRYPTION' => "",
        ];

        foreach ($settingData as $setting) {
            $result[$setting->name] = $setting->value;
        }

        // Apply settings dynamically
        config([
            'mail.default' => $result['SERVER_DRIVER'] ?? '',
            'mail.mailers.smtp.host' => $result['SERVER_HOST'] ?? '',
            'mail.mailers.smtp.port' => $result['SERVER_PORT'] ?? '',
            'mail.mailers.smtp.encryption' => $result['SERVER_ENCRYPTION'] ?? '',
            'mail.mailers.smtp.username' => $result['SERVER_USERNAME'] ?? '',
            'mail.mailers.smtp.password' => $result['SERVER_PASSWORD'] ?? '',
            'mail.from.name' => $result['FROM_NAME'] ?? '',
            'mail.from.address' => $result['FROM_EMAIL'] ?? '',
        ]);
        return $result;
    }
}


if (!function_exists('sendEmailVerification')) {
    function sendEmailVerification($to, $data)
    {
        $data['settings'] = emailSettings(1);
        try {
            Mail::to($to)->send(new EmailVerification($data));

            return [
                'status' => 'success',
                'message' => __('Email successfully sent'),
            ];
        } catch (\Exception $e) {
            Log::error('Email Sending Failed: ' . $e->getMessage());

            return [
                'status' => 'error',
                'message' => __('We noticed that the email settings have not been configured for this system. As a result, email-related functionalities may not work as expected. please contact the administrator to resolve this issue.')
            ];
            return redirect()->back()->with('error', __(''));
        }
    }
}


if (!function_exists('RoleName')) {
    function RoleName($permission_id = '0')
    {
        $retuen = '';
        $role_id_array = DB::table('role_has_permissions')->where('permission_id', $permission_id)->pluck('role_id');
        if (!empty($role_id_array)) {
            $role_id_array = DB::table('roles')->whereIn('id', $role_id_array)->pluck('name')->toArray();
            $retuen = implode(', ', $role_id_array);
        }

        return $retuen;
    }
}


if (!function_exists('HomePageSection')) {
    function HomePageSection()
    {
        $retuen = [
            [
                'title' => 'Header Menu',
                'section' => 'Section 0',
                'content' => '',
                'content_value' => '{"name":"Header Menu","menu_pages":["1","2"]}'
            ],
            [
                'title' => 'Banner',
                'section' => 'Section 1',
                'content_value' => '{"name":"Banner","section_enabled":"active","title":"Smart Tenant - Property Management System","sub_title":"Property management refers to the administration, operation, and oversight of real estate properties on behalf of property owners. It involves various tasks such as marketing rental properties, finding tenants, collecting rent and ensuring legal compliance.","btn_name":"Get Started","btn_link":"#","section_footer_text":"Manage your business efficiently with our all-in-one solution designed for performance, security, and scalability.","section_footer_image":{},"section_main_image":{},"section_footer_image_path":"upload\/homepage\/banner_2.png","section_main_image_path":"upload\/homepage\/banner_1.png","box_image_1_path":"","box_image_2_path":"","box_image_3_path":"","Box1_image_path":"","Box2_image_path":"","Sec4_box1_image_path":"","Sec4_box2_image_path":"","Sec4_box3_image_path":"","Sec4_box4_image_path":"","Sec4_box5_image_path":"","Sec4_box6_image_path":"","Sec7_box1_image_path":"","Sec7_box2_image_path":"","Sec7_box3_image_path":"","Sec7_box4_image_path":"","Sec7_box5_image_path":"","Sec7_box6_image_path":"","Sec7_box7_image_path":"","Sec7_box8_image_path":""}'
            ],
            [
                'title' => 'OverView',
                'section' => 'Section 2',
                'content_value' => '{"name":"OverView","section_enabled":"active","Box1_title":"Customers","Box1_number":"500+","Box2_title":"Subscription Plan","Box2_number":"4+","Box3_title":"Language","Box3_number":"11+","box1_number_image":{},"box2_number_image":{},"box3_number_image":{},"section_footer_image_path":"","section_main_image_path":"","box_image_1_path":"upload\/homepage\/OverView_1.svg","box_image_2_path":"upload\/homepage\/OverView_2.svg","box_image_3_path":"upload\/homepage\/OverView_3.svg","Box1_image_path":"","Box2_image_path":"","Sec4_box1_image_path":"","Sec4_box2_image_path":"","Sec4_box3_image_path":"","Sec4_box4_image_path":"","Sec4_box5_image_path":"","Sec4_box6_image_path":"","Sec7_box1_image_path":"","Sec7_box2_image_path":"","Sec7_box3_image_path":"","Sec7_box4_image_path":"","Sec7_box5_image_path":"","Sec7_box6_image_path":"","Sec7_box7_image_path":"","Sec7_box8_image_path":""}'

            ],
            [
                'title' => 'AboutUs',
                'section' => 'Section 3',
                'content_value' => '{"name":"AboutUs","section_enabled":"active","Box1_title":"Empower Your Business to Thrive with Us","Box1_info":"Unlock growth, streamline operations, and achieve success with our innovative solutions.","Box1_list":["Simplify and automate your business processes for maximum efficiency.","Receive tailored strategies to meet business needs and unlock potential.","Grow confidently with flexible solutions that adapt to your business needs.","Make smarter decisions with real-time analytics and performance tracking.","Rely on 24\/7 expert assistance to keep your business running smoothly."],"Box2_title":"Eliminate Paperwork, Elevate Productivity","Box2_info":"Simplify your operations with seamless digital solutions and focus on what truly matters.","Box2_list":["Replace manual paperwork with automated workflows.","Secure cloud storage lets you manage documents on the go.","Streamlined processes save time and reduce errors.","Keep your information safe with encrypted storage.","Reduce printing, storage, and administrative expenses.","Go green by minimizing paper use and waste."],"section_footer_image_path":"","section_main_image_path":"","box_image_1_path":"","box_image_2_path":"","box_image_3_path":"","Box1_image_path":"upload\/homepage\/img-customize-1.svg","Box2_image_path":"upload\/homepage\/img-customize-2.svg","Sec4_box1_image_path":"","Sec4_box2_image_path":"","Sec4_box3_image_path":"","Sec4_box4_image_path":"","Sec4_box5_image_path":"","Sec4_box6_image_path":"","Sec7_box1_image_path":"","Sec7_box2_image_path":"","Sec7_box3_image_path":"","Sec7_box4_image_path":"","Sec7_box5_image_path":"","Sec7_box6_image_path":"","Sec7_box7_image_path":"","Sec7_box8_image_path":""}'
            ],
            [
                'title' => 'Offer',
                'section' => 'Section 4',
                'content_value' => '{"name":"Offer","section_enabled":"active","Sec4_title":"What Our Software Offers","Sec4_info":"Our software provides powerful, scalable solutions designed to streamline your business operations.","Sec4_box1_title":"User-Friendly Interface","Sec4_box1_enabled":"active","Sec4_box1_info":"Simplify operations with an intuitive and easy-to-use platform.","Sec4_box2_title":"End-to-End Automation","Sec4_box2_enabled":"active","Sec4_box2_info":"Automate repetitive tasks to save time and increase efficiency.","Sec4_box3_title":"Customizable Solutions","Sec4_box3_enabled":"active","Sec4_box3_info":"Tailor features to fit your unique business needs and workflows.","Sec4_box4_title":"Scalable Features","Sec4_box4_enabled":"active","Sec4_box4_info":"Grow your business with flexible solutions that scale with you.","Sec4_box5_title":"Enhanced Security","Sec4_box5_enabled":"active","Sec4_box5_info":"Protect your data with advanced encryption and security protocols.","Sec4_box6_title":"Real-Time Analytics","Sec4_box6_enabled":"active","Sec4_box6_info":"Gain actionable insights with live data tracking and reporting.","Sec4_box1_image":{},"Sec4_box2_image":{},"Sec4_box3_image":{},"Sec4_box4_image":{},"Sec4_box5_image":{},"Sec4_box6_image":{},"section_footer_image_path":"","section_main_image_path":"","box_image_1_path":"","box_image_2_path":"","box_image_3_path":"","Box1_image_path":"","Box2_image_path":"","Sec4_box1_image_path":"upload\/homepage\/offers_1.svg","Sec4_box2_image_path":"upload\/homepage\/offers_2.svg","Sec4_box3_image_path":"upload\/homepage\/offers_3.svg","Sec4_box4_image_path":"upload\/homepage\/offers_4.svg","Sec4_box5_image_path":"upload\/homepage\/offers_5.svg","Sec4_box6_image_path":"upload\/homepage\/offers_6.svg","Sec7_box1_image_path":"","Sec7_box2_image_path":"","Sec7_box3_image_path":"","Sec7_box4_image_path":"","Sec7_box5_image_path":"","Sec7_box6_image_path":"","Sec7_box7_image_path":"","Sec7_box8_image_path":""}'

            ],
            [
                'title' => 'Pricing',
                'section' => 'Section 5',
                'content_value' => '{"name":"Pricing","section_enabled":"active","Sec5_title":"Flexible Pricing","Sec5_info":"Get started for free, upgrade later in our application.","section_footer_image_path":"","section_main_image_path":"","box_image_1_path":"","box_image_2_path":"","box_image_3_path":"","Box1_image_path":"","Box2_image_path":"","Sec4_box1_image_path":"","Sec4_box2_image_path":"","Sec4_box3_image_path":"","Sec4_box4_image_path":"","Sec4_box5_image_path":"","Sec4_box6_image_path":"","Sec7_box1_image_path":"","Sec7_box2_image_path":"","Sec7_box3_image_path":"","Sec7_box4_image_path":"","Sec7_box5_image_path":"","Sec7_box6_image_path":"","Sec7_box7_image_path":"","Sec7_box8_image_path":""}'
            ],
            [
                'title' => 'Core Features',
                'section' => 'Section 6',
                'content_value' => '{"name":"Core Features","section_enabled":"active","Sec6_title":"Core Features","Sec6_info":"Core Modules For Your Business","Sec6_Box_title":["Dashboard","Property","Tenant","Invoice detail","Expenses","Uer Roles & Permissions"],"Sec6_Box_subtitle":["Property management refers to the administration, operation, and oversight of real estate properties on behalf of property owners.","Property management refers to the administration, operation, and oversight of real estate properties on behalf of property owners.","Property management refers to the administration, operation, and oversight of real estate properties on behalf of property owners.","Property management refers to the administration, operation, and oversight of real estate properties on behalf of property owners.","Property management refers to the administration, operation, and oversight of real estate properties on behalf of property owners.","Property management refers to the administration, operation, and oversight of real estate properties on behalf of property owners."],"Sec6_box_image":[{},{},{},{},{},{}],"section_footer_image_path":"","section_main_image_path":"","box_image_1_path":"","box_image_2_path":"","box_image_3_path":"","Box1_image_path":"","Box2_image_path":"","Sec4_box1_image_path":"","Sec4_box2_image_path":"","Sec4_box3_image_path":"","Sec4_box4_image_path":"","Sec4_box5_image_path":"","Sec4_box6_image_path":"","Sec6_box0_image_path":"upload\/homepage\/1.png","Sec6_box1_image_path":"upload\/homepage\/2.png","Sec6_box2_image_path":"upload\/homepage\/3.png","Sec6_box3_image_path":"upload\/homepage\/4.png","Sec6_box4_image_path":"upload\/homepage\/5.png","Sec6_box5_image_path":"upload\/homepage\/6.png","Sec6_box6_image_path":"","Sec7_box1_image_path":"","Sec7_box2_image_path":"","Sec7_box3_image_path":"","Sec7_box4_image_path":"","Sec7_box5_image_path":"","Sec7_box6_image_path":"","Sec7_box7_image_path":"","Sec7_box8_image_path":""}',
            ],
            [
                'title' => 'Testimonials',
                'section' => 'Section 7',
                'content_value' => '{"name":"Testimonials","section_enabled":"active","Sec7_title":"What Our Customers Say About Us","Sec7_info":"We\u2019re proud of the impact our software has had on businesses just like yours. Hear directly from our customers about how our solutions have made a difference in their day-to-day operations","Sec7_box1_name":"Lenore Becker","Sec7_box1_tag":null,"Sec7_box1_Enabled":"active","Sec7_box1_review":"Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Vestibulum rutrum, mi nec elementum vehicula, eros quam gravida nisl, id fringilla neque ante vel mi. Quisque ut nisi. Nulla porta dolor. Aenean tellus metus, bibendum sed, posuere ac, mattis non, nunc.","Sec7_box2_name":"Damian Morales","Sec7_box2_tag":"New","Sec7_box2_Enabled":"active","Sec7_box2_review":"Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Vestibulum rutrum.","Sec7_box3_name":"Oleg Lucas","Sec7_box3_tag":null,"Sec7_box3_Enabled":"active","Sec7_box3_review":"Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Vestibulum rutrum, mi nec elementum vehicula, eros quam gravida nisl, id fringilla neque ante vel mi. Quisque ut nisi. Nulla porta dolor. Aenean tellus metus, bibendum sed, posuere ac, mattis non, nunc.","Sec7_box4_name":"Jerome Mccoy","Sec7_box4_tag":null,"Sec7_box4_Enabled":"active","Sec7_box4_review":"Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Vestibulum rutrum, mi nec elementum vehicula, eros quam gravida nisl, id fringilla neque ante vel mi. Quisque ut nisi. Nulla porta dolor. Aenean tellus metus, bibendum sed, posuere ac, mattis non, nunc.","Sec7_box5_name":"Rafael Carver","Sec7_box5_tag":null,"Sec7_box5_Enabled":"active","Sec7_box5_review":"Aenean leo ligula, porttitor eu, consequat vitae, eleifend.","Sec7_box6_name":"Edan Rodriguez","Sec7_box6_tag":null,"Sec7_box6_Enabled":"active","Sec7_box6_review":"Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Vestibulum rutrum, mi nec elementum vehicula, eros quam gravida nisl, id fringilla neque ante vel mi. Quisque ut nisi. Nulla porta dolor. Aenean tellus metus, bibendum sed, posuere ac, mattis non, nunc.","Sec7_box7_name":"Kalia Middleton","Sec7_box7_tag":null,"Sec7_box7_Enabled":"active","Sec7_box7_review":"Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Vestibulum rutrum, mi nec elementum.","Sec7_box8_name":"Zenaida Chandler","Sec7_box8_tag":null,"Sec7_box8_Enabled":"active","Sec7_box8_review":"Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Vestibulum rutrum, mi nec elementum vehicula, eros quam gravida nisl, id fringilla neque ante vel mi. Quisque ut nisi. Nulla porta dolor. Aenean tellus metus, bibendum sed, posuere ac, mattis non, nunc.","Sec7_box1_image":{},"Sec7_box2_image":{},"Sec7_box3_image":{},"Sec7_box4_image":{},"Sec7_box5_image":{},"Sec7_box6_image":{},"Sec7_box7_image":{},"Sec7_box8_image":{},"section_footer_image_path":"","section_main_image_path":"","box_image_1_path":"","box_image_2_path":"","box_image_3_path":"","Box1_image_path":"","Box2_image_path":"","Sec4_box1_image_path":"","Sec4_box2_image_path":"","Sec4_box3_image_path":"","Sec4_box4_image_path":"","Sec4_box5_image_path":"","Sec4_box6_image_path":"","Sec7_box1_image_path":"upload\/homepage\/review_1.png","Sec7_box2_image_path":"upload\/homepage\/review_2.png","Sec7_box3_image_path":"upload\/homepage\/review_3.png","Sec7_box4_image_path":"upload\/homepage\/review_4.png","Sec7_box5_image_path":"upload\/homepage\/review_5.png","Sec7_box6_image_path":"upload\/homepage\/review_6.png","Sec7_box7_image_path":"upload\/homepage\/review_7.png","Sec7_box8_image_path":"upload\/homepage\/review_8.png"}'

            ],
            [
                'title' => 'Choose US',
                'section' => 'Section 8',
                'content_value' => '{"name":"Choose US","section_enabled":"active","Sec8_title":"Reason to Choose US","Sec8_box1_info":"Proven Expertise","Sec8_box2_info":"Customizable Solutions","Sec8_box3_info":"Seamless Integration","Sec8_box4_info":"Exceptional Support","Sec8_box5_info":"Scalable and Future-Proof","Sec8_box6_info":"Security You Can Trust","Sec8_box7_info":"User-Friendly Interface","Sec8_box8_info":"Innovation at Its Core","section_footer_image_path":"","section_main_image_path":"","box_image_1_path":"","box_image_2_path":"","box_image_3_path":"","Box1_image_path":"","Box2_image_path":"","Sec4_box1_image_path":"","Sec4_box2_image_path":"","Sec4_box3_image_path":"","Sec4_box4_image_path":"","Sec4_box5_image_path":"","Sec4_box6_image_path":"","Sec7_box1_image_path":"","Sec7_box2_image_path":"","Sec7_box3_image_path":"","Sec7_box4_image_path":"","Sec7_box5_image_path":"","Sec7_box6_image_path":"","Sec7_box7_image_path":"","Sec7_box8_image_path":""}'

            ],
            [
                'title' => 'FAQ',
                'section' => 'Section 9',
                'content_value' => '{"name":"FAQ","section_enabled":"active","Sec9_title":"Frequently Asked Questions (FAQ)","Sec9_info":"Please refer the Frequently ask question for your quick help","section_footer_image_path":"","section_main_image_path":"","box_image_1_path":"","box_image_2_path":"","box_image_3_path":"","Box1_image_path":"","Box2_image_path":"","Sec4_box1_image_path":"","Sec4_box2_image_path":"","Sec4_box3_image_path":"","Sec4_box4_image_path":"","Sec4_box5_image_path":"","Sec4_box6_image_path":"","Sec7_box1_image_path":"","Sec7_box2_image_path":"","Sec7_box3_image_path":"","Sec7_box4_image_path":"","Sec7_box5_image_path":"","Sec7_box6_image_path":"","Sec7_box7_image_path":"","Sec7_box8_image_path":""}'

            ],
            [
                'title' => 'AboutUS - Footer',
                'section' => 'Section 10',
                'content_value' => '{"name":"AboutUS - Footer","section_enabled":"active","Sec10_title":"About Smart Tenant SaaS",
                "Sec10_info":"Property management refers to the administration, operation, and oversight of real estate properties on behalf of
                property owners. It involves various tasks such as marketing rental properties, finding tenants, collecting rent
                and ensuring legal compliance.","section_footer_image_path":"","section_main_image_path":"","box_image_1_path":"","box_image_2_path":"","box_image_3_path":"","Box1_image_path":"","Box2_image_path":"","Sec4_box1_image_path":"","Sec4_box2_image_path":"","Sec4_box3_image_path":"","Sec4_box4_image_path":"","Sec4_box5_image_path":"","Sec4_box6_image_path":"","Sec7_box1_image_path":"","Sec7_box2_image_path":"","Sec7_box3_image_path":"","Sec7_box4_image_path":"","Sec7_box5_image_path":"","Sec7_box6_image_path":"","Sec7_box7_image_path":"","Sec7_box8_image_path":""}'

            ],
        ];

        foreach ($retuen as $key => $value) {
            $HomePage = new HomePage();
            $HomePage->title = $value['title'];
            $HomePage->section = $value['section'];
            if (!empty($value['content_value'])) {
                $HomePage->content_value = $value['content_value'];
            }
            $HomePage->enabled = 1;
            $HomePage->parent_id = 1;
            $HomePage->save();
        }
        return '';
    }
}



if (!function_exists('CustomPage')) {
    function CustomPage()
    {
        $retuen = [
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy_policy',
                'content' => "<h3><strong>1. Information We Collect</strong></h3><p>We may collect the following types of information from you:</p><h4><strong>a. Personal Information</strong></h4><ul><li>Name, email address, phone number, and other contact details.</li><li>Payment information (if applicable).</li></ul><h4><strong>b. Non-Personal Information</strong></h4><ul><li>Browser type, operating system, and device information.</li><li>Usage data, including pages visited, time spent, and other analytical data.</li></ul><h4><strong>c. Information You Provide</strong></h4><ul><li>Information you voluntarily provide when contacting us, signing up, or completing forms.</li></ul><h4><strong>d. Cookies and Tracking Technologies</strong></h4><ul><li>We use cookies, web beacons, and other tracking tools to enhance your experience and analyze usage patterns.</li></ul><h3><strong>2. How We Use Your Information</strong></h3><p>We use the information collected for the following purposes:</p><ul><li>To provide, maintain, and improve our Services.</li><li>To process transactions and send you confirmations.</li><li>To communicate with you, including responding to inquiries or providing updates.</li><li>To personalize your experience and deliver tailored content.</li><li>To comply with legal obligations and protect against fraud or misuse.</li></ul><h3><strong>3. How We Share Your Information</strong></h3><p>We do not sell your personal information. However, we may share your information with:</p><ul><li><strong>Service Providers:</strong> Third-party vendors who assist in providing our Services.</li><li><strong>Legal Authorities:</strong> When required to comply with legal obligations or protect our rights.</li><li><strong>Business Transfers:</strong> In the event of a merger, acquisition, or sale of assets, your information may be transferred.</li></ul><h3><strong>4. Data Security</strong></h3><p>We implement appropriate technical and organizational measures to protect your data against unauthorized access, disclosure, alteration, or destruction. However, no method of transmission or storage is 100% secure, and we cannot guarantee absolute security.</p><h3><strong>5. Your Rights</strong></h3><p>You have the right to:</p><ul><li>Access, correct, or delete your personal data.</li><li>Opt-out of certain data processing activities, including marketing communications.</li><li>Withdraw consent where processing is based on consent.</li></ul><p>To exercise your rights, please contact us at [contact email].</p><h3><strong>6. Third-Party Links</strong></h3><p>Our Services may contain links to third-party websites. We are not responsible for the privacy practices or content of these websites. Please review their privacy policies before engaging with them.</p><h3><strong>7. Children's Privacy</strong></h3><p>Our Services are not intended for children under the age of [13/16], and we do not knowingly collect personal information from them. If we become aware that a child has provided us with personal data, we will take steps to delete it.</p><h3><strong>8. Changes to This Privacy Policy</strong></h3><p>We may update this Privacy Policy from time to time. Any changes will be posted on this page with a revised 'Last Updated' date. Your continued use of the Services after such changes constitutes your acceptance of the new terms.</p><h3>&nbsp;</h3>"
            ],
            [
                'title' => 'Terms & Conditions',
                'slug' => 'terms_conditions',
                'content' => "<h3><strong>1. Acceptance of Terms</strong></h3><p>By using our Services, you confirm that you are at least [18 years old or the legal age in your jurisdiction] and capable of entering into a binding agreement. If you are using our Services on behalf of an organization, you represent that you have the authority to bind that organization to these Terms.</p><h3><strong>2. Use of Services</strong></h3><p>You agree to use our Services only for lawful purposes and in accordance with these Terms. You must not:</p><ul><li>Violate any applicable laws or regulations.</li><li>Use our Services in a manner that could harm, disable, overburden, or impair them.</li><li>Attempt to gain unauthorized access to our systems or networks.</li><li>Transmit any harmful code, viruses, or malicious software.</li></ul><h3><strong>3. User Accounts</strong></h3><p>If you create an account with us, you are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account. You agree to notify us immediately of any unauthorized use of your account or breach of security.</p><h3><strong>4. Intellectual Property</strong></h3><p>All content, trademarks, logos, and intellectual property associated with our Services are owned by [Your Company Name] or our licensors. You are granted a limited, non-exclusive, non-transferable license to access and use the Services for personal or authorized business purposes. Any unauthorized use, reproduction, or distribution is prohibited.</p><h3><strong>5. Payment and Billing</strong> (if applicable)</h3><p>If our Services involve payments:</p><ul><li>All fees are due at the time of purchase unless otherwise agreed.</li><li>We reserve the right to change pricing or introduce new fees with prior notice.</li><li>Refunds, if applicable, will be handled according to our [Refund Policy].</li></ul><h3><strong>6. Termination of Services</strong></h3><p>We reserve the right to suspend or terminate your access to our Services at our discretion, without prior notice, if:</p><ul><li>You breach these Terms.</li><li>We are required to do so by law.</li><li>Our Services are discontinued or altered.</li></ul><h3><strong>7. Limitation of Liability</strong></h3><p>To the fullest extent permitted by law:</p><ul><li>[Your Company Name] and its affiliates shall not be liable for any direct, indirect, incidental, or consequential damages resulting from your use of our Services.</li><li>Our liability is limited to the amount you paid, if any, for accessing our Services.</li></ul><h3><strong>8. Indemnification</strong></h3><p>You agree to indemnify and hold [Your Company Name], its affiliates, employees, and partners harmless from any claims, liabilities, damages, losses, or expenses arising from your use of the Services or violation of these Terms.</p><h3><strong>9. Modifications to Terms</strong></h3><p>We may update these Terms from time to time. Any changes will be effective immediately upon posting, and your continued use of the Services constitutes your acceptance of the revised Terms.</p>"
            ],
        ];
        foreach ($retuen as $key => $value) {
            $Page = new Page();
            $Page->title = $value['title'];
            $Page->slug = $value['slug'];
            $Page->content = $value['content'];
            $Page->enabled = 1;
            $Page->parent_id = 1;
            $Page->save();
        }


        $FAQ_retuen = [
            [
                'question' => 'What features does your software offer?',
                'description' => 'Our software provides a range of features including automation tools, real-time analytics, cloud-based access, secure data storage, seamless integrations, and customizable solutions tailored to your business needs.',
            ],
            [
                'question' => 'Is your software easy to use?',
                'description' => 'Yes! Our platform is designed to be user-friendly and intuitive, so your team can get started quickly without a steep learning curve.',
            ],
            [
                'question' => 'Can I integrate your software with my existing systems?',
                'description' => 'Absolutely! Our software is built to easily integrate with your current tools and systems, making the transition seamless and efficient.',
            ],
            [
                'question' => 'Is customer support available?',
                'description' => 'Yes! We offer 24/7 customer support. Our dedicated team is ready to assist you with any questions or issues you may have.',
            ],
            [
                'question' => 'Is my data secure with your software?',
                'description' => 'Yes. We use advanced encryption and data protection protocols to ensure your data is secure and private at all times.',
            ],
            [
                'question' => 'Can I customize the software to fit my business needs?',
                'description' => 'Yes! Our software is highly customizable to adapt to your unique workflows and requirements.',
            ],
            [
                'question' => 'What types of businesses can benefit from your software?',
                'description' => 'Our solutions are suitable for a wide range of industries, including retail, healthcare, finance, marketing, and more. We tailor our offerings to meet the specific needs of each business.',
            ],

            [
                'question' => 'Is there a free trial available?',
                'description' => 'Yes! We offer a free trial so you can explore the features and capabilities of our software before committing.',
            ],

            [
                'question' => 'Do I need technical expertise to use the software?',
                'description' => 'Not at all. Our software is designed for users of all skill levels. Plus, our support team is available to guide you through any setup or usage questions.',
            ],

            [
                'question' => 'How often is the software updated?',
                'description' => 'We regularly release updates to improve features, security, and overall performance, ensuring that you always have access to the latest technology.',
            ],
        ];
        foreach ($FAQ_retuen as $key => $FAQ_value) {
            $FAQs = new FAQ();
            $FAQs->question = $FAQ_value['question'];
            $FAQs->description = $FAQ_value['description'];
            $FAQs->enabled = 1;
            $FAQs->parent_id = 1;
            $FAQs->save();
        }
        return '';
    }
}
if (!function_exists('DefaultCustomPage')) {
    function DefaultCustomPage()
    {
        $return = Page::where('enabled', 1)->whereIn('id', [1, 2])->get();
        return $return;
    }
}

if (!function_exists('DefaultBankTransferPayment')) {
    function DefaultBankTransferPayment()
    {
        $bankArray = [
            'bank_transfer_payment' => 'on',
            'bank_name' => 'Bank of America',
            'bank_holder_name' => 'SmartWeb Infotech',
            'bank_account_number' => '4242 4242 4242 4242',
            'bank_ifsc_code' => 'BOA45678',
            'bank_other_details' => '',
        ];

        foreach ($bankArray as $key => $val) {
            \DB::insert(
                'insert into settings (`value`, `name`, `type`,`parent_id`) values (?, ?, ?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                [
                    $val,
                    $key,
                    'payment',
                    1,
                ]
            );
        }

        return '';
    }
}

if (!function_exists('QrCode2FA')) {
    function QrCode2FA()
    {
        $user = Auth::user();

        $google2fa = new Google2FA();

        // generate a secret
        $secret = $google2fa->generateSecretKey();

        // generate the QR code, indicating the address
        // of the web application and the user name
        // or email in this case
        $company = env('APP_NAME');
        if ($user->type != 'super admin') {
            $company = isset(settings()['company_name']) && !empty(settings()['company_name']) ? settings()['company_name'] : $company;
        }

        $qr_code = $google2fa->getQRCodeInline(
            $company,
            $user->email,
            $secret
        );

        // store the current secret in the session
        // will be used when we enable 2FA (see below)
        session(["2fa_secret" => $secret]);

        return $qr_code;
    }
}

if (!function_exists('authPage')) {
    function authPage($id)
    {

        $templateData = [
            'title' => [
                "Secure Access, Seamless Experience.",
                "Your Trusted Gateway to Digital Security.",
                "Fast, Safe & Effortless Login."
            ],
            'description' => [
                "Securely access your account with ease. Whether you're logging in, signing up, or resetting your password, we ensure a seamless and protected experience. Your data, your security, our priority.",
                "Fast, secure, and hassle-free authentication. Sign in with confidence and experience a seamless way to access your account—because your security matters.",
                "A seamless and secure way to access your account. Whether you're logging in, signing up, or recovering your password, we ensure your data stays protected at every step."
            ],
        ];

        $authPage = new AuthPage();
        $authPage->title = json_encode($templateData['title']);
        $authPage->description = json_encode($templateData['description']);
        $authPage->section = 1;
        $authPage->image = 'upload/images/auth_page.svg';
        $authPage->parent_id = $id;
        $authPage->save();

        $createdTemplates[] = $authPage;

        return $createdTemplates;
    }
}

if (!function_exists('FilesExtension')) {
    function FilesExtension()
    {
        return [
            "jpeg" => "jpeg",
            "jpg" => "jpg",
            "png" => "png",
            "doc" => "doc",
            "csv" => "csv",
            "docx" => "docx",
            "mp4" => "mp4",
            "mp3" => "mp3",
            "xls" => "xls",
            "pdf" => "pdf",
            "zip" => "zip",
            "json" => "json",
            "txt" => "txt",
            "svg" => "svg",
            "ppt" => "ppt",
            "3dmf" => "3dmf",
            "3dm" => "3dm",
            "gtar" => "gtar",
            "flv" => "flv",
            "fh4" => "fh4",
            "fh5" => "fh5",
            "fhc" => "fhc",
            "help" => "help",
            "hlp" => "hlp",
            "avi" => "avi",
            "ai" => "ai",
            "bin" => "bin",
            "bmp" => "bmp",
            "cab" => "cab",
            "c" => "c",
            "c++" => "c++",
            "class" => "class",
            "css" => "css",
            "cdr" => "cdr",
            "dot" => "dot",
            "dwg" => "dwg",
            "eps" => "eps",
            "exe" => "exe",
            "gif" => "gif",
            "gz" => "gz",
            "js" => "js",
            "java" => "java",
            "latex" => "latex",
            "log" => "log",
            "m3u" => "m3u",
            "midi" => "midi",
            "mid" => "mid",
            "mov" => "mov",
            "ppz" => "ppz",
            "pot" => "pot",
            "ps" => "ps",
            "qt" => "qt",
            "qd3d" => "qd3d",
            "qd3" => "qd3",
            "qxd" => "qxd",
            "rar" => "rar",
            "sgml" => "sgml",
            "sgm" => "sgm",
            "tar" => "tar",
            "tiff" => "tiff",
            "tif" => "tif",
            "tgz" => "tgz",
            "tex" => "tex",
            "html" => "html",
            "htm" => "htm",
            "ico" => "ico",
            "vob" => "vob",
            "wav" => "wav",
            "wrl" => "wrl",
            "xla" => "xla",
            "xlc" => "xlc",
            "xml" => "xml",
            "imap" => "imap",
            "inf" => "inf",
            "jpe" => "jpe",
            "mpeg" => "mpeg",
            "mpg" => "mpg",
            "mp2" => "mp2",
            "ogg" => "ogg",
            "phtml" => "phtml",
            "php" => "php",
            "pgp" => "pgp",
            "pps" => "pps",
            "ra" => "ra",
            "ram" => "ram",
            "rm" => "rm",
            "rtf" => "rtf",
            "spr" => "spr",
            "sprite" => "sprite",
            "stream" => "stream",
            "swf" => "swf",
        ];
    }
}

if (!function_exists('fetch_file')) {
    function fetch_file($filename = '', $path = '')
    {
        $settings = settings(1);

        try {
            if ($settings['storage_type'] == 'wasabi') {
                config(
                    [
                        'filesystems.disks.wasabi.key' => $settings['wasabi_key'],
                        'filesystems.disks.wasabi.secret' => $settings['wasabi_secret'],
                        'filesystems.disks.wasabi.region' => $settings['wasabi_region'],
                        'filesystems.disks.wasabi.bucket' => $settings['wasabi_bucket'],
                        'filesystems.disks.wasabi.endpoint' => 'https://s3.' . $settings['wasabi_region'] . '.wasabisys.com',
                    ]
                );
            } elseif ($settings['storage_type'] == 's3') {
                config(
                    [
                        'filesystems.disks.s3.key' => $settings['aws_s3_key'],
                        'filesystems.disks.s3.secret' => $settings['aws_s3_secret'],
                        'filesystems.disks.s3.region' => $settings['aws_s3_region'],
                        'filesystems.disks.s3.bucket' => $settings['aws_s3_bucket'],
                        'filesystems.disks.s3.use_path_style_endpoint' => false,
                    ]
                );
            }

            return \Storage::disk($settings['storage_type'])->url($path . $filename);
        } catch (\Throwable $th) {
            return '';
        }
    }
}


if (!function_exists('handleFileUpload')) {
    function handleFileUpload($file, $uploadPath, $customValidation = [])
    {
        try {
            $settings = settings(1);

            if (empty($settings['storage_type'])) {
                throw new \Exception(__('Please set proper configuration for storage.'));
            }

            // Setup filename
            $originalName = $file->getClientOriginalName();
            $fileNameOnly = pathinfo($originalName, PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $fileName = $fileNameOnly . '_' . time() . '.' . $extension;

            // Determine disk and MIME types
            switch ($settings['storage_type']) {
                case 'wasabi':
                    config([
                        'filesystems.disks.wasabi.key' => $settings['wasabi_key'],
                        'filesystems.disks.wasabi.secret' => $settings['wasabi_secret'],
                        'filesystems.disks.wasabi.region' => $settings['wasabi_region'],
                        'filesystems.disks.wasabi.bucket' => $settings['wasabi_bucket'],
                        'filesystems.disks.wasabi.endpoint' => 'https://s3.' . $settings['wasabi_region'] . '.wasabisys.com',
                    ]);
                    $disk = 'wasabi';
                    $mimes = $settings['wasabi_file_type'] ?? '';
                    break;

                case 's3':
                    config([
                        'filesystems.disks.s3.key' => $settings['aws_s3_key'],
                        'filesystems.disks.s3.secret' => $settings['aws_s3_secret'],
                        'filesystems.disks.s3.region' => $settings['aws_s3_region'],
                        'filesystems.disks.s3.bucket' => $settings['aws_s3_bucket'],
                    ]);
                    $disk = 's3';
                    $mimes = $settings['aws_s3_file_type'] ?? '';
                    break;

                default:
                    $disk = 'local';
                    $mimes = $settings['local_file_type'] ?? '';
                    break;
            }

            // Validate file
            $validation = count($customValidation) > 0 ? $customValidation : ['mimes:' . $mimes];
            $validator = \Validator::make(
                ['upload_file' => $file],
                ['upload_file' => $validation]
            );

            if ($validator->fails()) {
                return [
                    'flag' => 0,
                    'msg' => $validator->messages()->first()
                ];
            }

            // Upload logic
            if ($disk === 'local') {
                $destination = storage_path($uploadPath);
                if (!file_exists($destination)) {
                    mkdir($destination, 0777, true);
                }
                $file->move($destination, $fileName);
            } else {
                \Storage::disk($disk)->putFileAs($uploadPath, $file, $fileName);
            }

            return [
                'flag' => 1,
                'msg' => 'Upload successful',
                'path' => $uploadPath . '/' . $fileName,
                'filename' => $fileName,
                'disk' => $disk,
            ];
        } catch (\Exception $e) {
            return [
                'flag' => 0,
                'msg' => $e->getMessage()
            ];
        }
    }
}


if (!function_exists('uploadLogoFile')) {
    function uploadLogoFile($file, $fieldName, $parentId, $userType)
    {
        try {
            $settings = settings(1);

            if (empty($settings['storage_type'])) {
                throw new \Exception(__('Please set proper configuration for storage.'));
            }

            // Determine disk and config
            switch ($settings['storage_type']) {
                case 'wasabi':
                    config([
                        'filesystems.disks.wasabi.key' => $settings['wasabi_key'],
                        'filesystems.disks.wasabi.secret' => $settings['wasabi_secret'],
                        'filesystems.disks.wasabi.region' => $settings['wasabi_region'],
                        'filesystems.disks.wasabi.bucket' => $settings['wasabi_bucket'],
                        'filesystems.disks.wasabi.endpoint' => 'https://s3.' . $settings['wasabi_region'] . '.wasabisys.com',
                    ]);
                    $disk = 'wasabi';
                    break;

                case 's3':
                    config([
                        'filesystems.disks.s3.key' => $settings['aws_s3_key'],
                        'filesystems.disks.s3.secret' => $settings['aws_s3_secret'],
                        'filesystems.disks.s3.region' => $settings['aws_s3_region'],
                        'filesystems.disks.s3.bucket' => $settings['aws_s3_bucket'],
                    ]);
                    $disk = 's3';
                    break;

                default:
                    $disk = 'local';
                    break;
            }

            // Validate PNG file
            $validator = \Validator::make(
                ['upload_file' => $file],
                ['upload_file' => 'required|mimes:png']
            );

            if ($validator->fails()) {
                return [
                    'flag' => 0,
                    'msg' => $validator->messages()->first()
                ];
            }

            $uploadPath = 'upload/logo/';
            $filename = ($userType === 'super admin')
                ? "{$fieldName}.png"
                : "{$parentId}_{$fieldName}.png";

            if ($disk === 'local') {
                $destination = public_path($uploadPath);
                if (!file_exists($destination)) {
                    if (!mkdir($destination, 0777, true) && !is_dir($destination)) {
                        throw new \Exception("Unable to create directory: $destination");
                    }
                }
                $file->storeAs($uploadPath, $filename);
            } else {
                \Storage::disk($disk)->putFileAs($uploadPath, $file, $filename);
            }

            return [
                'flag' => 1,
                'msg' => 'Upload successful',
                'filename' => $filename,
                'path' => $uploadPath . '/' . $filename,
                'disk' => $disk
            ];
        } catch (\Exception $e) {
            \Log::error('Logo upload error: ' . $e->getMessage());
            return [
                'flag' => 0,
                'msg' => $e->getMessage()
            ];
        }
    }
}


if (!function_exists('deleteOldFile')) {
    function deleteOldFile($imgName, $path)
    {
        try {
            $settings = settings(1);
            $disk = $settings['storage_type'] ?? 'local';

            if ($disk === 'wasabi') {
                config([
                    'filesystems.disks.wasabi.key' => $settings['wasabi_key'],
                    'filesystems.disks.wasabi.secret' => $settings['wasabi_secret'],
                    'filesystems.disks.wasabi.region' => $settings['wasabi_region'],
                    'filesystems.disks.wasabi.bucket' => $settings['wasabi_bucket'],
                    'filesystems.disks.wasabi.endpoint' => 'https://s3.' . $settings['wasabi_region'] . '.wasabisys.com',
                ]);
            } elseif ($disk === 's3') {
                config([
                    'filesystems.disks.s3.key' => $settings['aws_s3_key'],
                    'filesystems.disks.s3.secret' => $settings['aws_s3_secret'],
                    'filesystems.disks.s3.region' => $settings['aws_s3_region'],
                    'filesystems.disks.s3.bucket' => $settings['aws_s3_bucket'],
                ]);
            }

            \Storage::disk($disk)->delete($path . $imgName);
        } catch (\Exception $e) {
            \Log::error("Failed to delete file: " . $e->getMessage());
        }
    }
}





if (!function_exists('FrontHomePageSection')) {
    function FrontHomePageSection($id)
    {
        $return = [
            [
                'title' => 'Banner',
                'section' => 'Section 0',
                'content' => '',
                'content_value' => '{"name":"Banner","section_enabled":"active","title":"Smart Tenant - Property Management System","sub_title":"Property management refers to the administration, operation, and oversight of real estate properties on behalf of property owners. It involves various tasks such as marketing rental properties, finding tenants, collecting rent and ensuring legal compliance.","banner_image1":{},"banner_image1_path":"upload\/fronthomepage\/prop_banner_image1_20250811051336pm.jpg"}',
            ],
            [
                'title' => 'Offer',
                'section' => 'Section 1',
                'content' => '',
                'content_value' => '{"name":"Offer","section_enabled":"active","Sec1_title":"Property Highlights","Sec1_info":"Top reasons this property is a smart investment.","Sec1_box1_title":"Eco-Friendly Design","Sec1_box1_enabled":"active","Sec1_box1_info":"Energy-efficient appliances and solar panels","Sec1_box2_title":"Modern Amenities","Sec1_box2_enabled":"active","Sec1_box2_info":"Swimming pool, gym, and clubhouse included","Sec1_box3_title":"24\/7 Security","Sec1_box3_enabled":"active","Sec1_box3_info":"Round-the-clock surveillance and gated entry","Sec1_box4_title":"Low Maintenance Cost","Sec1_box4_enabled":"active","Sec1_box4_info":"Affordable society charges and upkeep","Sec1_box1_image_path":"upload\/fronthomepage\/eco_Section_4_image_120250801055311pm.jpg","Sec1_box2_image_path":"upload\/fronthomepage\/ame_Section_4_image_220250801055311pm.jpg","Sec1_box3_image_path":"upload\/fronthomepage\/sec_Section_4_image_320250801055311pm.jpg","Sec1_box4_image_path":"upload\/fronthomepage\/main_Section_4_image_420250801055311pm.jpg"}',
            ],
            [
                'title' => 'OverView',
                'section' => 'Section 2',
                'content' => '',
                'content_value' => '{"name":"OverView","section_enabled":"active","Box1_title":"Total Property","Box1_number":"850","Box2_title":"Total Tenant","Box2_number":"1500","Box3_title":"Total Amenities","Box3_number":"500","Box4_title":"Years of Experience","Box4_number":"10"}',
            ],
            [
                'title' => 'Category',
                'section' => 'Section 3',
                'content' => '',
                'content_value' => '{"name":"Amenity","section_enabled":"active","Sec3_title":"Available Amenities","Sec3_info":"Experience premium facilities that enhance your stay"}',
            ],
            [
                'title' => 'AboutUs',
                'section' => 'Section 4',
                'content' => '',
                'content_value' => '{"name":"AboutUs","section_enabled":"active","Sec4_title":"A whole world of freelance talent at your fingertips","Sec4_Box_title":["Proof of quality","No cost until you hire","Safe and secure"],"Sec4_Box_subtitle":["Check any pro\u2019s work samples, client reviews, and identity verification.","Interview potential fits for your job, negotiate rates, and only pay for work you approve.","Focus on your work knowing we help protect your data and privacy. We\u2019re here with 24\/7 support if you need it."],"about_image":{},"about_image_path":"upload\/fronthomepage\/home1_about_image_20250811033554pm.jpg"}',
            ],
            [
                'title' => 'PopularService',
                'section' => 'Section 5',
                'content' => '',
                'content_value' => '{"name":"PopularService","section_enabled":"active","Sec5_title":"Find Your Perfect Property","Sec5_info":"Explore residential and commercial spaces that suit your needs."}',
            ],
            [
                'title' => 'Banner2',
                'section' => 'Section 6',
                'content' => '',
                'content_value' => '{"name":"Banner2","section_enabled":"active","Sec6_title":"Simplify, Organize, Grow","Sec6_info":"Effortlessly manage every aspect of your property .Our all-in-one system turns complex tasks into simple wins \u2705","sec6_btn_name":"Get Started","sec6_btn_link":"#","banner_image2":{},"banner_image2_path":"upload\/fronthomepage\/we3_banner_image2_20250811051154pm.png"}',
            ],
            [
                'title' => 'Testimonials',
                'section' => 'Section 7',
                'content' => '',
                'content_value' => '{"name":"Testimonials","section_enabled":"active","Sec7_title":"Testimonials","Sec7_info":"Interdum et malesuada fames ac ante ipsum","Sec7_box1_name":"Rohit Sharma","Sec7_box1_tag":"Property Owner","Sec7_box1_Enabled":"active","Sec7_box1_review":"\"This platform has completely transformed how I manage my rentals \ud83c\udfe1\ud83d\udcbc. What used to take hours now takes minutes \u2013 simply amazing!\"","Sec7_box2_name":"Priya Mehta","Sec7_box2_tag":"Real Estate Manager","Sec7_box2_Enabled":"active","Sec7_box2_review":"\"The automated reminders and easy tracking save me so much time \u23f3\ud83d\udc4c. I can finally focus on growing my business instead of chasing paperwork.\"","Sec7_box3_name":"Anna M\u00fcller","Sec7_box3_tag":"Landlord","Sec7_box3_Enabled":"active","Sec7_box3_review":"\"From tenant communication to payment collection, everything is smooth and hassle-free \ud83d\udcac\ud83d\udcb3. I wish I had started using this years ago!\"","Sec7_box4_name":"Chloe Dubois","Sec7_box4_tag":"Property Consultant","Sec7_box4_Enabled":"active","Sec7_box4_review":"\"User-friendly, reliable, and packed with powerful features \ud83d\ude80\ud83d\udcca. It\u2019s like having a full team working for me 24\/7!\"","Sec7_box5_name":"Isabella Rossi","Sec7_box5_tag":"Building Owner","Sec7_box5_Enabled":"active","Sec7_box5_review":"\"The best investment I\u2019ve made for my properties \ud83d\udca1\ud83d\udcb0. My tenants are happier, and my workflow is 10x faster.\"","Sec7_box1_image_path":"upload\/fronthomepage\/user_Section_7_image_120250805121658pm.png","Sec7_box2_image_path":"upload\/fronthomepage\/user1_Section_7_image_220250805123651pm.jpg","Sec7_box3_image_path":"upload\/fronthomepage\/user2_Section_7_image_320250805123651pm.png","Sec7_box4_image_path":"upload\/fronthomepage\/user4_Section_7_image_420250805123651pm.jpg","Sec7_box5_image_path":"upload\/fronthomepage\/user6_Section_7_image_520250805123651pm.png","Sec7_box6_image_path":"","Sec7_box7_image_path":"","Sec7_box8_image_path":""}',
            ],
            [
                'title' => 'AboutUS - Footer',
                'section' => 'Section 8',
                'content' => '',
                'content_value' => '{"name":"AboutUS - Footer","section_enabled":"active","Sec8_info":"Property management refers to the administration, operation, and oversight of real estate properties on behalf of property owners. It involves various tasks such as marketing rental properties, finding tenants, collecting rent and ensuring legal compliance.","fb_link":"#","twitter_link":"#","insta_link":"#","linkedin_link":"#"}',
            ],
        ];

        foreach ($return as $key => $value) {
            $FrontHomePage = new FrontHomePage();
            $FrontHomePage->title = $value['title'];
            $FrontHomePage->content = $value['content'];
            $FrontHomePage->section = $value['section'];
            if (!empty($value['content_value'])) {
                $FrontHomePage->content_value = $value['content_value'];
            }
            $FrontHomePage->enabled = 1;
            $FrontHomePage->parent_id = $id;
            $FrontHomePage->save();
        }
        // newer section types (FAQ, team, gallery...) and the current storage format
        \App\Support\WebsiteSections::ensure($id);
        return '';
    }
}


if (!function_exists('AdditionalPageSection')) {
    function AdditionalPageSection($id)
    {
        $return = [
            [
                'title' => 'Blog Page',
                'section' => 'Section 0',
                'content' => '',
                'content_value' => '{"name":"Blog Page","section_enabled":"active","title":"Your Property Management Blog","sub_title":"Practical advice, market updates, and success stories for property owners, tenants, and investors.","banner_image1":{},"banner_image1_path":"upload\/additional\/banner3_banner_image1_20250811045343pm.jpg"}',
            ],


            [
                'title' => 'Property Page',
                'section' => 'Section 3',
                'content' => '',
                'content_value' => '{"name":"Property Page","section_enabled":"active","sec3_title":"Explore Available Properties Instantly","sec3_sub_title":"Your complete property overview with photos, amenities, and key details.","sec3_banner_image":{},"sec3_banner_image_path":"upload\/additional\/banner3_sec3_banner_image_20250811042217pm.jpg"}',
            ],

        ];

        foreach ($return as $key => $value) {
            $AdditionalPage = new Additional();
            $AdditionalPage->title = $value['title'];
            $AdditionalPage->content = $value['content'];
            $AdditionalPage->section = $value['section'];
            if (!empty($value['content_value'])) {
                $AdditionalPage->content_value = $value['content_value'];
            }
            $AdditionalPage->enabled = 1;
            $AdditionalPage->parent_id = $id;
            $AdditionalPage->save();
        }
        return '';
    }
}


if (!function_exists('NewPermission')) {
    function NewPermission()
    {

        $permissions = [
            ['name' => 'manage storage settings', 'guard_name' => 'web', 'roles' => ['super admin']],
            ['name' => 'show unit', 'guard_name' => 'web', 'roles' => ['owner', 'manager']],
            ['name' => 'manage income report', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'manage expense report', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'manage profit loss report', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'manage property unit report', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'manage tenant history report', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'manage maintenance report', 'guard_name' => 'web', 'roles' => ['owner']],

            ['name' => 'manage agreement', 'guard_name' => 'web', 'roles' => ['owner', 'tenant']],
            ['name' => 'create agreement', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'edit agreement', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'show agreement', 'guard_name' => 'web', 'roles' => ['owner', 'tenant']],
            ['name' => 'delete agreement', 'guard_name' => 'web', 'roles' => ['owner']],

            ['name' => 'manage amenity', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'create amenity', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'edit amenity', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'delete amenity', 'guard_name' => 'web', 'roles' => ['owner']],

            ['name' => 'manage advantage', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'create advantage', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'edit advantage', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'delete advantage', 'guard_name' => 'web', 'roles' => ['owner']],

            ['name' => 'manage front home page', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'edit front home page', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'manage blog', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'create blog', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'edit blog', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'delete blog', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'manage additional', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'edit additional', 'guard_name' => 'web', 'roles' => ['owner']],

            ['name' => 'manage n8n', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'create n8n', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'edit n8n', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'delete n8n', 'guard_name' => 'web', 'roles' => ['owner']],

            ['name' => 'manage twilio settings', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'manage password settings', 'guard_name' => 'web', 'roles' => ['tenant', 'maintainer', 'manager']],
            ['name' => 'manage account settings', 'guard_name' => 'web', 'roles' => ['tenant', 'maintainer', 'manager']],
            ['name' => 'manage 2FA settings', 'guard_name' => 'web', 'roles' => ['tenant', 'maintainer']],
            ['name' => 'manage openai settings', 'guard_name' => 'web', 'roles' => ['super admin', 'owner']],

            ['name' => 'manage razorpay settings', 'guard_name' => 'web', 'roles' => ['super admin', 'owner']],
            ['name' => 'manage whatsapp settings', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'assign maintainer', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'manage calendar', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'manage cronjob settings', 'guard_name' => 'web', 'roles' => ['super admin']],
            ['name' => 'manage lease renew', 'guard_name' => 'web', 'roles' => ['owner']],

            // v2.8
            ['name' => 'manage deposit', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'create deposit', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'delete deposit', 'guard_name' => 'web', 'roles' => ['owner']],

            // v2.9
            ['name' => 'manage lead', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'create lead', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'edit lead', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'delete lead', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'manage rental application', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'review rental application', 'guard_name' => 'web', 'roles' => ['owner']],

            // v3.0
            ['name' => 'manage inspection', 'guard_name' => 'web', 'roles' => ['owner', 'tenant']],
            ['name' => 'create inspection', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'edit inspection', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'delete inspection', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'manage vendor', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'create vendor', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'edit vendor', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'delete vendor', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'manage maintenance schedule', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'create maintenance schedule', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'edit maintenance schedule', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'delete maintenance schedule', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'manage meter reading', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'create meter reading', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'delete meter reading', 'guard_name' => 'web', 'roles' => ['owner']],

            // v3.1
            ['name' => 'manage landlord', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'create landlord', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'edit landlord', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'delete landlord', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'manage ledger report', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'manage tax report', 'guard_name' => 'web', 'roles' => ['owner']],

            // v3.3
            ['name' => 'manage facility', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'book facility', 'guard_name' => 'web', 'roles' => ['tenant']],
            ['name' => 'manage visitor', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'create visitor pass', 'guard_name' => 'web', 'roles' => ['tenant']],
            ['name' => 'manage parking', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'manage poll', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'vote poll', 'guard_name' => 'web', 'roles' => ['tenant']],
            // v3.4
            ['name' => 'manage assistant chats', 'guard_name' => 'web', 'roles' => ['owner']],
            // v3.5
            ['name' => 'manage arrears report', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'manage rent roll report', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'manage payout report', 'guard_name' => 'web', 'roles' => ['owner']],
        ];

        if (!empty($permissions)) {
            foreach ($permissions as $permData) {
                // Create new Permission
                Permission::firstOrCreate([
                    'name' => $permData['name'],
                    'guard_name' => $permData['guard_name']
                ]);
            }

            // new permissions are not in the cached list yet; givePermissionTo() below would not find them
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            $permissionsByRole = [];

            foreach ($permissions as $permData) {
                foreach ($permData['roles'] as $roleName) {
                    $permissionsByRole[$roleName][] = $permData['name'];
                }
            }

            foreach ($permissionsByRole as $roleName => $permNames) {
                $roles = Role::where('name', $roleName)->get();

                foreach ($roles as $role) {
                    // assign permissions to role
                    $role->givePermissionTo($permNames);
                }
            }
        }

        $removePermissions = [
            ['name' => 'create note', 'guard_name' => 'web', 'roles' => ['tenant', 'maintainer', 'manager']],
            ['name' => 'edit note', 'guard_name' => 'web', 'roles' => ['tenant', 'maintainer', 'manager']],
            ['name' => 'delete note', 'guard_name' => 'web', 'roles' => ['tenant', 'maintainer', 'manager']],
            ['name' => 'show note', 'guard_name' => 'web', 'roles' => ['tenant', 'maintainer', 'manager']],
        ];

        foreach ($removePermissions as $permData) {
            $permission = Permission::where('name', $permData['name'])
                ->where('guard_name', $permData['guard_name'])
                ->first();

            if ($permission) {
                foreach ($permData['roles'] as $roleName) {
                    $roles = Role::where('name', $roleName)->get();
                    foreach ($roles as $role) {
                        // remove permissions to role
                        $role->revokePermissionTo($permission);
                    }
                }
            }
        }

        // v3.5 ready-made staff roles and the default Landlord role for every owner account
        foreach (User::where('type', 'owner')->pluck('id') as $ownerId) {
            defaultStaffRolesCreate($ownerId);
            defaultLandlordCreate($ownerId);
        }

        defaultSMSTemplate();


        return true;
    }
}
if (!function_exists('currentSubscription')) {
    function currentSubscription()
    {
        $ids = parentId();
        $authUser = User::find($ids);
        return [
            'subscription' => Subscription::find($authUser->subscription),
            'pricing_feature_settings' => getSettingsValByIdName(1, 'pricing_feature')
        ];
    }
}


if (!function_exists('n8nAllowed')) {
    // n8n is a plan feature: on for everyone when the pricing feature is off, else only on plans that include it
    function n8nAllowed($ownerId): bool
    {
        $owner = \App\Models\User::find($ownerId);
        if (!$owner || $owner->type === 'super admin') {
            return false;
        }
        if (getSettingsValByIdName(1, 'pricing_feature') === 'off') {
            return true;
        }
        return (int) optional(\App\Models\Subscription::find($owner->subscription))->enabled_n8n === 1;
    }
}

if (!function_exists('triggerN8n')) {
    function triggerN8n(string $module, array $payload): bool
    {
        if (!n8nAllowed(parentId())) {
            return false;
        }
        $webhook = N8n::where([
            'module' => $module,
            'status' => 1,
            'parent_id' => parentId(),
        ])->first();

        if (empty($webhook)) {
            return false;
        }
        try {
            $method = strtolower($webhook->method);

            $response = Http::asJson()
                        ->timeout(10)
                ->$method($webhook->url, $payload);

            if ($response->failed()) {
                Log::error('N8n webhook failed', [
                    'module' => $module,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('N8n webhook exception', [
                'module' => $module,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
if (!function_exists('invoiceNumber')) {
    function invoiceNumber($parentId = null)
    {
        try {
            return DB::transaction(function () use ($parentId) {
                $resolvedId = $parentId ?? parentId();
                $maxInvoiceId = Invoice::where('parent_id', $resolvedId)
                    ->lockForUpdate()
                    ->max('invoice_id');

                return $maxInvoiceId ? $maxInvoiceId + 1 : 1;
            });
        } catch (\Exception $e) {
            \Log::error('Invoice number generation failed: ' . $e->getMessage());
            throw $e;
        }
    }
}

function notificationChannels()
{
    $email = emailSettings(parentId());

    $emailConfigured =
        !empty($email['SERVER_DRIVER']) &&
        !empty($email['SERVER_HOST']) &&
        !empty($email['SERVER_PORT']) &&
        !empty($email['SERVER_USERNAME']) &&
        !empty($email['SERVER_PASSWORD']) &&
        !empty($email['FROM_EMAIL']);

    $settings = settings();

    $sid = $settings['twilio_sid'] ?? '';
    $token = $settings['twilio_token'] ?? '';
    $smsConfigured = !empty($sid) && !empty($token) && !empty($settings['twilio_from_number']);
    $whatsappConfigured = !empty($sid) && !empty($token) && !empty($settings['twilio_whatsapp_number']);
    return ['email' => $emailConfigured,'sms' => $smsConfigured,'whatsapp' => $whatsappConfigured];
}
