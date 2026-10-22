<?php

namespace App\Http\Controllers;

use App\Models\Custom;
use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FAQRCode\Google2FA;

class SettingController extends Controller
{

    //    ---------------------- Account --------------------------------------------------------
    public function index()
    {
        $loginUser = \Auth::user();
        $settings = settings();
        $timezones = config('timezones');
        return view('settings.index', compact('loginUser', 'settings', 'timezones'));
    }

    public function accountData(Request $request)
    {
        if (!\Auth::user()->can('manage account settings')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }

        // dd($request->all());
        $loginUser = \Auth::user();
        $user = User::find($loginUser->id);
        $validator = \Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'email' => 'required|email|unique:users,email,' . $user->id,
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        if ($request->hasFile('profile')) {
            if (!empty($user->profile)) {
                deleteOldFile($user->profile, 'upload/profile/');
            }
            $uploadResult = handleFileUpload($request->file('profile'), 'upload/profile/');

            if ($uploadResult['flag'] == 0) {
                return redirect()->back()->with('error', $uploadResult['msg']);
            }

            $user->profile = $uploadResult['filename'];
        }

        $user->first_name = $request->name;
        $user->email = $request->email;
        $user->phone_number = $request->phone_number;
        $user->save();


        return redirect()->back()->with('success', 'User profile settings successfully updated.')->with('tab', 'user_profile_settings');
    }

    public function accountDelete(Request $request)
    {
        if (!\Auth::user()->can('manage account settings')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $loginUser = \Auth::user();
        $loginUser->delete();

        return redirect()->back()->with('success', 'Your account successfully deleted.');
    }

    //    ---------------------- Password --------------------------------------------------------



    public function passwordData(Request $request)
    {
        if (!\Auth::user()->can('manage password settings')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        if (\Auth::Check()) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'current_password' => 'required',
                    'new_password' => 'required|min:6',
                    'confirm_password' => 'required|same:new_password',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $loginUser = \Auth::user();
            $data = $request->All();

            $current_password = $loginUser->password;
            if (Hash::check($data['current_password'], $current_password)) {
                $user_id = $loginUser->id;
                $user = User::find($user_id);
                $user->password = Hash::make($data['new_password']);
                ;
                $user->save();

                return redirect()->back()->with('success', __('Password successfully updated.'))->with('tab', 'password_settings');
            } else {
                return redirect()->back()->with('error', __('Please enter valid current password.'))->with('tab', 'password_settings');
            }
        } else {
            return redirect()->back()->with('error', __('Invalid user.'))->with('tab', 'password_settings');
        }
    }

    //    ---------------------- General --------------------------------------------------------



    public function generalData(Request $request)
    {
        if (!\Auth::user()->can('manage general settings')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $user = \Auth::user();
        $userType = $user->type;
        $parentId = parentId(); // Assume 1 for super admin

        $validator = \Validator::make($request->all(), [
            'application_name' => 'required',
        ]);

        $fileFields = ['logo', 'favicon', 'light_logo', 'landing_logo'];


        // Add file validation for each file field
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $validator->addRules([$field => 'mimes:png']);
            }
        }

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }

        // Save App Name
        if (!empty($request->application_name)) {
            Custom::setCommon(['APP_NAME' => $request->application_name]);

            \DB::insert(
                'INSERT INTO settings (`value`, `name`, `parent_id`) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
                [$request->application_name, 'app_name', $parentId]
            );
        }

        // Save Copyright
        if (!empty($request->copyright)) {
            \DB::insert(
                'INSERT INTO settings (`value`, `name`, `parent_id`) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
                [$request->copyright, 'copyright', $parentId]
            );
        }

        // Upload and store logo file names in DB
        foreach ($fileFields as $key => $field) {
            if ($request->hasFile($field)) {

                $upload = uploadLogoFile($request->file($field), $field, $parentId, $userType);

                if ($upload['flag'] != 1) {
                    return redirect()->back()->with('error', __($upload['msg']));
                }

                $filename = $upload['filename'];
                $settingKey = ($userType === 'super admin') ? $field : 'company_' . $field;

                \DB::insert(
                    'INSERT INTO settings (`value`, `name`, `parent_id`) VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
                    [$filename, $settingKey, $parentId]
                );
            }
        }

        // Extra toggles for super admin
        if ($userType === 'super admin') {
            $toggles = [
                'landing_page' => $request->landing_page ?? 'off',
                'register_page' => $request->register_page ?? 'off',
                'owner_email_verification' => $request->owner_email_verification ?? 'off',
                'pricing_feature' => $request->pricing_feature,
            ];

            foreach ($toggles as $key => $val) {
                \DB::insert(
                    'INSERT INTO settings (`value`, `name`, `type`, `parent_id`) VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
                    [$val, $key, 'common', $parentId]
                );
            }
        }

        return redirect()->back()
            ->with('success', __('General setting successfully saved.'))
            ->with('tab', 'general_settings');
    }






    //    ---------------------- SMTP --------------------------------------------------------



    public function smtpData(Request $request)
    {
        if (!\Auth::user()->can('manage email settings')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        if (\Auth::Check()) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'sender_name' => 'required',
                    'sender_email' => 'required',
                    'server_driver' => 'required',
                    'server_host' => 'required',
                    'server_port' => 'required',
                    'server_username' => 'required',
                    'server_password' => 'required',
                    'server_encryption' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $smtpArray = [
                'FROM_NAME' => $request->sender_name,
                'FROM_EMAIL' => $request->sender_email,
                'SERVER_DRIVER' => $request->server_driver,
                'SERVER_HOST' => $request->server_host,
                'SERVER_PORT' => $request->server_port,
                'SERVER_USERNAME' => $request->server_username,
                'SERVER_PASSWORD' => $request->server_password,
                'SERVER_ENCRYPTION' => $request->server_encryption,
            ];
            foreach ($smtpArray as $key => $val) {
                if (!empty($val)) {
                    \DB::insert(
                        'insert into settings (`value`, `name`, `type`,`parent_id`) values (?, ?, ?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                        [
                            $val,
                            $key,
                            'smtp',
                            parentId(),
                        ]
                    );
                }
            }

            return redirect()->back()->with('success', __('SMTP settings successfully saved.'))->with('tab', 'email_SMTP_settings');
        } else {
            return redirect()->back()->with('error', __('Invalid user.'))->with('tab', 'email_SMTP_settings');
        }
    }

    public function smtpTest(Request $request)
    {
        if (!\Auth::user()->can('manage email settings')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        return view('settings.testmail');
    }

    public function smtpTestMailSend(Request $request)
    {
        if (!\Auth::user()->can('manage email settings')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        if (\Auth::check()) {
            $to = $request->email;
            $errorMessage = '';
            // Data for email
            $data = [
                'module' => 'test_mail',
                'subject' => 'Test Mail',
                'message' => __('This is a test mail.'),
            ];

            // Send email
            $response = sendEmail($to, $data);
            if ($response['status'] == 'error') {
                $errorMessage = $response['message'];
                return redirect()->back()->with('error', $errorMessage)->with('tab', 'email_SMTP_settings');
                ;
            } else {
                $errorMessage = $response['message'];
                return redirect()->back()->with('success', $errorMessage)->with('tab', 'email_SMTP_settings');
                ;
            }
        }
    }

    //    ---------------------- Payment --------------------------------------------------------



    public function paymentData(Request $request)
    {
        if (!\Auth::user()->can('manage payment settings')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $validator = \Validator::make(
            $request->all(),
            [
                'CURRENCY' => 'required',
                'CURRENCY_SYMBOL' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $currencyArray = [
            'CURRENCY' => $request->CURRENCY,
            'CURRENCY_SYMBOL' => $request->CURRENCY_SYMBOL,
            'bank_transfer_payment' => $request->bank_transfer_payment ?? 'off',
            'STRIPE_PAYMENT' => $request->stripe_payment ?? 'off',
            'paypal_payment' => $request->paypal_payment ?? 'off',
            'flutterwave_payment' => $request->flutterwave_payment ?? 'off',
            'paystack_payment' => $request->paystack_payment ?? 'off',
            'razorpay_payment' => $request->razorpay_payment ?? 'off',
        ];
        foreach ($currencyArray as $key => $val) {
            if (!empty($val)) {
                \DB::insert(
                    'insert into settings (`value`, `name`, `type`,`parent_id`) values (?, ?, ?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $val,
                        $key,
                        'payment',
                        parentId(),
                    ]
                );
            }
        }

        //        For Bank Transfer Settings
        if (isset($request->bank_transfer_payment)) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'bank_name' => 'required',
                    'bank_holder_name' => 'required',
                    'bank_account_number' => 'required',
                    'bank_ifsc_code' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $bankArray = [
                'bank_transfer_payment' => $request->bank_transfer_payment ?? 'off',
                'bank_name' => $request->bank_name,
                'bank_holder_name' => $request->bank_holder_name,
                'bank_account_number' => $request->bank_account_number,
                'bank_ifsc_code' => $request->bank_ifsc_code,
                'bank_other_details' => !empty($request->bank_other_details) ? $request->bank_other_details : '',
            ];

            foreach ($bankArray as $key => $val) {
                if (!empty($val)) {
                    \DB::insert(
                        'insert into settings (`value`, `name`, `type`,`parent_id`) values (?, ?, ?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                        [
                            $val,
                            $key,
                            'payment',
                            parentId(),
                        ]
                    );
                }
            }
        }

        // For Strip Settings
        if (isset($request->stripe_payment)) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'stripe_key' => 'required',
                    'stripe_secret' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $stripeArray = [
                'STRIPE_PAYMENT' => $request->stripe_payment ?? 'off',
                'STRIPE_KEY' => $request->stripe_key,
                'STRIPE_SECRET' => $request->stripe_secret,
            ];

            foreach ($stripeArray as $key => $val) {
                if (!empty($val)) {
                    \DB::insert(
                        'insert into settings (`value`, `name`, `type`,`parent_id`) values (?, ?, ?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                        [
                            $val,
                            $key,
                            'payment',
                            parentId(),
                        ]
                    );
                }
            }
        }


        // For Paypal Settings
        if (isset($request->paypal_payment)) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'paypal_mode' => 'required',
                    'paypal_client_id' => 'required',
                    'paypal_secret_key' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $paypalArray = [
                'paypal_payment' => $request->paypal_payment ?? 'off',
                'paypal_mode' => $request->paypal_mode,
                'paypal_client_id' => $request->paypal_client_id,
                'paypal_secret_key' => $request->paypal_secret_key,
            ];

            foreach ($paypalArray as $key => $val) {
                if (!empty($val)) {
                    \DB::insert(
                        'insert into settings (`value`, `name`, `type`,`parent_id`) values (?, ?, ?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                        [
                            $val,
                            $key,
                            'payment',
                            parentId(),
                        ]
                    );
                }
            }
        }


        // For Flutterwave Settings
        if (isset($request->flutterwave_payment)) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'flutterwave_public_key' => 'required',
                    'flutterwave_secret_key' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $flutterwaveArray = [
                'flutterwave_payment' => $request->flutterwave_payment ?? 'off',
                'flutterwave_public_key' => $request->flutterwave_public_key,
                'flutterwave_secret_key' => $request->flutterwave_secret_key,
            ];

            foreach ($flutterwaveArray as $key => $val) {
                if (!empty($val)) {
                    \DB::insert(
                        'insert into settings (`value`, `name`, `type`,`parent_id`) values (?, ?, ?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                        [
                            $val,
                            $key,
                            'payment',
                            parentId(),
                        ]
                    );
                }
            }
        }


        // For paystack Settings
        if (isset($request->paystack_payment)) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'paystack_public_key' => 'required',
                    'paystack_secret_key' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $paystackArray = [
                'paystack_payment' => $request->paystack_payment ?? 'off',
                'paystack_public_key' => $request->paystack_public_key,
                'paystack_secret_key' => $request->paystack_secret_key,
            ];

            foreach ($paystackArray as $key => $val) {
                if (!empty($val)) {

                    \DB::insert(
                        'insert into settings (`value`, `name`, `type`,`parent_id`) values (?, ?, ?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                        [
                            $val,
                            $key,
                            'payment',
                            parentId(),
                        ]
                    );
                }
            }
        }

        // For razorpay Settings
        if (isset($request->razorpay_payment)) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'razorpay_api_key' => 'required',
                    'razorpay_secret_key' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $razorpayArray = [
                'razorpay_payment' => $request->razorpay_payment ?? 'off',
                'razorpay_api_key' => $request->razorpay_api_key,
                'razorpay_secret_key' => $request->razorpay_secret_key,
            ];

            foreach ($razorpayArray as $key => $val) {
                if (!empty($val)) {

                    \DB::insert(
                        'insert into settings (`value`, `name`, `type`,`parent_id`) values (?, ?, ?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                        [
                            $val,
                            $key,
                            'payment',
                            parentId(),
                        ]
                    );
                }
            }
        }


        return redirect()->back()->with('success', __('Payment successfully saved.'))->with('tab', 'payment_settings');
    }

    //    ---------------------- Company  --------------------------------------------------------

    public function companyData(Request $request)
    {
        if (!\Auth::user()->can('manage company settings')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $validator = \Validator::make(
            $request->all(),
            [
                'company_name' => 'required',
                'company_email' => 'required',
                'support_email' => 'nullable|email',
                'company_phone' => 'required',
                'company_address' => 'required',
                'timezone' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $settings = $request->all();
        unset($settings['_token']);
        // these may legitimately be "off" or 0, which the empty() check below would skip
        $settings['late_fee_enabled'] = $request->late_fee_enabled == 'on' ? 'on' : 'off';
        $settings['late_fee_grace_days'] = (string) max(0, (int) $request->late_fee_grace_days);
        $settings['document_expiry_reminder_days'] = (string) max(1, (int) ($request->document_expiry_reminder_days ?: 30));
        $settings['lead_expiry_days'] = (string) max(0, (int) ($request->lead_expiry_days ?? 30));
        if (!empty($settings['landlord_signature'])) {
            $settings['landlord_signature_date'] = \Carbon\Carbon::now(getSettingsValByName('timezone'))->format('Y-m-d H:i:s');
        }

        foreach ($settings as $key => $val) {
            if (!empty($val) || $val === '0') {
                \DB::insert(
                    'insert into settings (`value`, `name`,`parent_id`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $val,
                        $key,
                        parentId(),
                    ]
                );
            }
        }
        return redirect()->back()->with('success', __('Company setting successfully saved.'))->with('tab', 'company_settings');
    }

    //    ---------------------- Language --------------------------------------------------------

    public function lanquageChange($lang)
    {
        $user = \Auth::user();
        $user->lang = $lang;
        $user->save();

        return redirect()->back()->with('success', __('Language successfully changed.'));
    }

    public function themeSettings(Request $request)
    {
        if (!\Auth::user()->can('manage general settings')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }

        $themeSettings = $request->only(['accent_color', 'color_type', 'custom_color', 'custom_color_code', 'layout_font', 'layout_width', 'sidebar_caption', 'theme_layout', 'theme_mode']);

        foreach ($themeSettings as $key => $val) {
            if (!empty($val)) {
                \DB::insert(
                    'insert into settings (`value`, `name`,`type`,`parent_id`) values (?, ?, ?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $val,
                        $key,
                        'common',
                        parentId(),
                    ]
                );
            }
        }

        return redirect()->back()->with('success', __('Theme settings save successfully.'));
    }

    //    ---------------------- SEO Settings --------------------------------------------------------



    public function siteSEOData(Request $request)
    {
        if (!\Auth::user()->can('manage seo settings')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }

        // dd($request->all());
        $validator = \Validator::make(
            $request->all(),
            [
                'meta_seo_title' => 'required',
                'meta_seo_keyword' => 'required',
                'meta_seo_description' => 'required',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $settings = $request->except(['_token', 'meta_seo_image']);

        if ($request->hasFile('meta_seo_image')) {
            $setting = \DB::table('settings')
                ->where('name', 'meta_seo_image')
                ->where('parent_id', parentId())
                ->first();

            if (!empty($setting) && !empty($setting->value)) {
                deleteOldFile($setting->value, 'upload/seo/');
            }

            $uploadResult = handleFileUpload($request->file('meta_seo_image'), 'upload/seo/');

            if ($uploadResult['flag'] === 0) {
                return redirect()->back()->with('error', $uploadResult['msg']);
            }

            \DB::insert(
                'INSERT INTO settings (`value`, `name`, `type`, `parent_id`) VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
                [
                    $uploadResult['filename'],
                    'meta_seo_image',
                    'SEO',
                    parentId(),
                ]
            );
        }


        // Save other SEO settings
        foreach ($settings as $key => $val) {
            if (!empty($val)) {
                \DB::insert(
                    'INSERT INTO settings (`value`, `name`, `type`, `parent_id`) VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
                    [
                        $val,
                        $key,
                        'SEO',
                        parentId(),
                    ]
                );
            }
        }

        return redirect()->back()
            ->with('success', __('Site SEO settings saved successfully.'))
            ->with('tab', 'site_SEO_settings');
    }


    // ---------------------- Google ReCaptcha Settings ---------------------------------------------
    public function googleRecaptchaData(Request $request)
    {
        if (!\Auth::user()->can('manage google recaptcha settings')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'recaptcha_key' => 'required',
                'recaptcha_secret' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $settings = $request->all();
        unset($settings['_token']);

        $recaptchaArray = [
            'google_recaptcha' => $request->google_recaptcha ?? 'off',
            'recaptcha_key' => $request->recaptcha_key,
            'recaptcha_secret' => $request->recaptcha_secret,
        ];

        foreach ($recaptchaArray as $key => $val) {
            if (!empty($val)) {
                \DB::insert(
                    'insert into settings (`value`, `name`, `type`,`parent_id`) values (?, ?, ?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $val,
                        $key,
                        'recaptcha',
                        parentId(),
                    ]
                );
            }
        }

        return redirect()->back()->with('success', __('Google Recaptcha settings save successfully.'))->with('tab', 'google_recaptcha_settings');
    }

    // ---------------------- Footer Setting ---------------------------------------------
    public function footerSetting(Request $request)
    {
        if (!Auth::user()->can('manage footer')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
        $loginUser = Auth::user();
        $pages = Page::where('enabled', 1)->pluck('title', 'id');
        return view('home_pages.footerSetting', compact('loginUser', 'pages'));
    }

    public function footerData(Request $request)
    {
        if (!\Auth::user()->can('manage footer')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $settings = $request->all();
        unset($settings['_token']);
        unset($settings['tab']);
        foreach ($settings as $s_key => $s_value) {
            if (in_array($s_key, ['footer_column_1_pages', 'footer_column_2_pages', 'footer_column_3_pages', 'footer_column_4_pages'])) {
                $s_value = json_encode($s_value);
            }
            if (!empty($s_value)) {
                \DB::insert(
                    'insert into settings (`value`, `name`, `type`,`parent_id`) values (?, ?, ?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $s_value,
                        $s_key,
                        'footer',
                        parentId(),
                    ]
                );
            }
        }

        return redirect()->back()->with('success', __('Footer settings save successfully.'))->with('tab', $request->tab);
    }


    // ---------------------- 2FA Setting --------------------------------
    public function twofaEnable(Request $request)
    {
        if (!\Auth::user()->can('manage 2FA settings')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $google2fa = new Google2FA();

        // retrieve secret from the session
        $secret = session("2fa_secret");
        $user = Auth::user();
        if ($google2fa->verify($request->input('otp'), $secret)) {
            // store the secret in the user profile
            // this will enable 2FA for this user
            $user->twofa_secret = $secret;
            $user->save();

            // avoid double OTP check
            session(["2fa_checked" => true]);

            return redirect()->back()->with('success', __('2 FA successfully enabled.'));
        }

        throw ValidationException::withMessages(['otp' => 'Incorrect value. Please try again...']);
    }



    public function storageSetting(Request $request)
    {
        if (!\Auth::user()->can('manage storage settings')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }

        // dd($request->All());

        $storageSetting = $request->all();
        unset($storageSetting['_token']);
        if (!empty($storageSetting['local_file_type'])) {
            $storageSetting['local_file_type'] = implode(',', $storageSetting['local_file_type']);
        }
        if (!empty($storageSetting['aws_s3_file_type'])) {
            $storageSetting['aws_s3_file_type'] = implode(',', $storageSetting['aws_s3_file_type']);
        }
        if (!empty($storageSetting['wasabi_file_type'])) {
            $storageSetting['wasabi_file_type'] = implode(',', $storageSetting['wasabi_file_type']);
        }

        foreach ($storageSetting as $key => $val) {
            if (!empty($val)) {
                \DB::insert(
                    'insert into settings (`value`, `name`,`type`,`parent_id`) values (?, ?, ?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $val,
                        $key,
                        'storage',
                        parentId(),
                    ]
                );
            }
        }

        return redirect()->back()->with('success', __('Storage settings save successfully.'))->with('tab', 'storage');
    }


    // ---------------------- agreement ---------------------------------------------
    public function agreement(Request $request)
    {
        if (!\Auth::user()->can('manage agreement')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $validator = \Validator::make($request->all(), [
            'terms_condition' => 'required|string',
            'agreement_description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->messages()->first());
        }

        $agreementArray = [
            'terms_condition' => $request->terms_condition,
            'agreement_description' => $request->agreement_description,
        ];

        foreach ($agreementArray as $key => $val) {
            if (!empty($val)) {
                \DB::insert(
                    'INSERT INTO settings (`value`, `name`, `type`, `parent_id`)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
                    [
                        $val,
                        $key,
                        'agreement', // Consider renaming this type if unrelated to CAPTCHA
                        parentId(),
                    ]
                );
            }
        }

        return redirect()->back()
            ->with('success', __('Agreement saved successfully.'))
            ->with('tab', 'agreement');
    }

    // ---------------------- Twilio Setting --------------------------------
    public function twilio(Request $request)
    {
        if (!\Auth::user()->can('manage twilio settings')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        if (!Auth::check()) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'twilio_sid' => 'required',
            'twilio_token' => 'required',
            'twilio_from_number' => 'nullable|string',
            'twilio_whatsapp_number' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->messages()->first());
        }
 
        foreach (['twilio_sid', 'twilio_token', 'twilio_from_number', 'twilio_whatsapp_number'] as $key) {
            $value = $request->input($key, '');
             if (is_null($value)) {
                $value = '';
            }
            $value = trim($value);
            \DB::insert('INSERT INTO settings (`value`, `name`, `parent_id`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
            [$value,$key,parentId()]);
        }

        return redirect()->back()->with('success', 'twilio settings updated successfully.')->with('tab', 'twilio');
    }

    // ---------------------- Openai Setting --------------------------------
    // super admin: Firebase service account for mobile app push notifications
    public function mobile(Request $request)
    {
        if (\Auth::user()->type != 'super admin') {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $save = fn($name, $value) => \DB::table('settings')->updateOrInsert(['name' => $name, 'parent_id' => 1], ['value' => $value]);
        if ($request->remove) {
            \DB::table('settings')->where('parent_id', 1)->whereIn('name', ['fcm_service_account', 'fcm_client_config'])->delete();
            return redirect()->back()->with('success', __('Mobile push notifications turned off.'))->with('tab', 'mobile');
        }
        $done = [];

        // server key: lets the website send notifications
        if (trim((string) $request->fcm_service_account) !== '') {
            $data = json_decode((string) $request->fcm_service_account, true);
            if (!is_array($data) || empty($data['project_id']) || empty($data['client_email']) || empty($data['private_key']) || ($data['type'] ?? '') != 'service_account') {
                return redirect()->back()->with('error', __('That is not a Firebase service account key. Paste the whole JSON file.'))->with('tab', 'mobile');
            }
            if (!openssl_pkey_get_private($data['private_key'])) {
                return redirect()->back()->with('error', __('The private key in that file could not be read.'))->with('tab', 'mobile');
            }
            $save('fcm_service_account', json_encode(array_intersect_key($data, array_flip(['type', 'project_id', 'private_key_id', 'private_key', 'client_email', 'client_id', 'token_uri']))));
            $done[] = __('server key');
        }

        // app settings: let the phone app start Firebase without a rebuild
        $client = \App\Support\FcmPush::clientConfig() ?: [];
        if (trim((string) $request->google_services_json) !== '') {
            $android = \App\Support\FcmPush::parseGoogleServicesJson($request->google_services_json);
            if (!$android) {
                return redirect()->back()->with('error', __('That is not a google-services.json file from Firebase.'))->with('tab', 'mobile');
            }
            $client['android'] = $android;
            $done[] = __('Android app');
        }
        if (trim((string) $request->google_service_plist) !== '') {
            $ios = \App\Support\FcmPush::parseGoogleServicePlist($request->google_service_plist);
            if (!$ios) {
                return redirect()->back()->with('error', __('That is not a GoogleService-Info.plist file from Firebase.'))->with('tab', 'mobile');
            }
            $client['ios'] = $ios;
            $done[] = __('iPhone app');
        }
        if ($client) {
            $save('fcm_client_config', json_encode($client));
        }
        if (!$done) {
            return redirect()->back()->with('error', __('Paste at least one Firebase file.'))->with('tab', 'mobile');
        }
        $account = \App\Support\FcmPush::serviceAccount();
        foreach (['android', 'ios'] as $platform) {
            if ($account && !empty($client[$platform]['project_id']) && $client[$platform]['project_id'] != $account['project_id']) {
                return redirect()->back()->with('error', __('Saved, but the :platform file belongs to Firebase project ":a" while the server key is for ":b". Use files from the same project.',
                    ['platform' => $platform == 'ios' ? 'iPhone' : 'Android', 'a' => $client[$platform]['project_id'], 'b' => $account['project_id']]))->with('tab', 'mobile');
            }
        }
        return redirect()->back()->with('success', __('Saved: :parts.', ['parts' => implode(', ', $done)]))->with('tab', 'mobile');
    }

    public function openai(Request $request)
    {
        if (!\Auth::user()->can('manage openai settings')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        // AI providers are configured once for the whole platform (AiService reads the super admin's settings)
        if (\Auth::user()->type != 'super admin') {
            return redirect()->back()->with('error', __('AI providers are managed by the platform administrator.'))->with('tab', 'openai');
        }

        $providers = array_keys(\App\Support\AiService::PROVIDERS);
        $validator = \Validator::make($request->all(), [
            'ai_provider' => 'required|in:' . implode(',', $providers),
            'ai_backup_provider' => 'nullable|in:' . implode(',', $providers),
            'openai_model' => 'nullable|max:80|regex:/^[A-Za-z0-9._:\/-]+$/',
            'anthropic_model' => 'nullable|max:80|regex:/^[A-Za-z0-9._:\/-]+$/',
            'gemini_model' => 'nullable|max:80|regex:/^[A-Za-z0-9._:\/-]+$/',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->messages()->first())->with('tab', 'openai');
        }

        $values = [
            'ai_provider' => $request->ai_provider,
            'ai_backup_provider' => $request->ai_backup_provider == $request->ai_provider ? '' : (string) $request->ai_backup_provider,
            'openai_model' => (string) $request->openai_model,
            'anthropic_model' => (string) $request->anthropic_model,
            'gemini_model' => (string) $request->gemini_model,
        ];
        // keys are write-only: an empty field keeps the saved key
        foreach (\App\Support\AiService::KEY_SETTINGS as $provider => $setting) {
            if (in_array($provider, (array) $request->remove_key)) {
                $values[$setting] = '';
            } elseif (trim((string) $request->input($setting)) !== '') {
                $values[$setting] = trim($request->input($setting));
            }
        }
        foreach ($values as $name => $value) {
            \DB::insert(
                'INSERT INTO settings (`value`, `name`, `parent_id`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
                [$value, $name, 1]
            );
        }
        \App\Support\AiService::flushConfig();

        $active = \App\Support\AiService::activeProvider();
        $message = __('AI settings saved.');
        if (!\App\Support\AiService::key($active)) {
            $message .= ' ' . __('Note: :provider has no API key yet, so AI features stay off until you add one.', ['provider' => \App\Support\AiService::PROVIDERS[$active]]);
        }
        return redirect()->back()->with('success', $message)->with('tab', 'openai');
    }

    public function keyGenerate()
    {
        if (!\Auth::user()->can('manage cronjob settings')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $key = hash('sha256', uniqid(time(), true));
        Setting::updateSettings('cronjob', $key);
        return redirect()->back()->with('success', __('Cron Job key generated successfully'))->with('tab', 'cronjob');
    }

    public function keyRemove()
    {
        if (!\Auth::user()->can('manage cronjob settings')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $key = getSettingsValByName('cronjob');

        if (!empty($key)) {
            Setting::updateSettings('cronjob', '');
            return redirect()->back()->with('success', __('Cron Job key removed successfully'))->with('tab', 'cronjob');
        }

        return redirect()->back()->with('error', __('No Cron Job key found'));
    }

}
