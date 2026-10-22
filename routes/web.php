<?php

use App\Http\Controllers\AdditionalController;
use App\Http\Controllers\AdvantageController;
use App\Http\Controllers\AgreementController;
use App\Http\Controllers\AmenityController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\AuthPageController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CronjobController;
use App\Http\Controllers\N8nController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\NoticeBoardController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\FAQController;
use App\Http\Controllers\HomePageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OTPController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentController;
use App\Models\User;

use App\Http\Controllers\PropertyController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\TenantDepositController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\RentalApplicationController;
use App\Http\Controllers\PublicLeadController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\MaintenanceScheduleController;
use App\Http\Controllers\MeterReadingController;
use App\Http\Controllers\AutopayController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\AssistantChatController;
use App\Http\Controllers\InvoicePayLinkController;
use App\Http\Controllers\InvoiceToolsController;
use App\Http\Controllers\TenantLifecycleController;
use App\Http\Controllers\LandlordController;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\WhatsappWebhookController;
use App\Http\Controllers\PwaController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\VisitorController;
use App\Http\Controllers\ParkingController;
use App\Http\Controllers\PollController;
use App\Http\Controllers\TypeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\InvoicePaymentController;
use App\Http\Controllers\MaintainerController;
use App\Http\Controllers\MaintenanceRequestController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

require __DIR__ . '/auth.php';

Route::get('dashboard', [HomeController::class, 'index'])->name('dashboard')->middleware(
    [
        'XSS',
        'CheckSubscription',
    ]
);
Route::get('/', [HomeController::class, 'index'])->middleware(
    [
        'XSS',

    ]
);
Route::get('home', [HomeController::class, 'index'])->name('home')->middleware(
    [
        'XSS',

    ]
);
// routes/web.php
Route::get('/global-search', [HomeController::class, 'search'])->name('global.search')->middleware(['auth', 'XSS', 'CheckSubscription']);
Route::get('/revenue-analytics', [HomeController::class, 'revenueAnalytics'])->name('revenue.analytics')
    ->middleware(['auth', 'XSS', 'CheckSubscription']);

Route::get('/property-unit-analytics', [HomeController::class, 'propertyUnitAnalytics'])
    ->name('property.unit.analytics');


//-------------------------------User-------------------------------------------
Route::resource('users', UserController::class)->middleware(
    [
        'auth',
        'XSS',
        'CheckSubscription'
    ]
);


Route::get('login/otp', [OTPController::class, 'show'])->name('otp.show')->middleware(
    [

        'XSS',
    ]
);
Route::post('login/otp', [OTPController::class, 'check'])->name('otp.check')->middleware(
    [

        'XSS',
    ]
);
Route::get('login/2fa/disable', [OTPController::class, 'disable'])->name('2fa.disable')->middleware(['XSS',]);

//-------------------------------Subscription-------------------------------------------

Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ],
    function () {

        Route::resource('subscriptions', SubscriptionController::class);
        Route::get('coupons/history', [CouponController::class, 'history'])->name('coupons.history');
        Route::delete('coupons/history/{id}/destroy', [CouponController::class, 'historyDestroy'])->name('coupons.history.destroy');
        Route::get('coupons/apply', [CouponController::class, 'apply'])->name('coupons.apply');
        Route::resource('coupons', CouponController::class);
        Route::get('subscription/transaction', [SubscriptionController::class, 'transaction'])->name('subscription.transaction');
        Route::get('subscription/free-trial/{id}/{trial}', [SubscriptionController::class, 'freeTrial'])->name('subscription.free.trial');
    }
);

//-------------------------------Subscription Payment-------------------------------------------

Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ],
    function () {

        Route::post('subscription/{id}/stripe/payment', [SubscriptionController::class, 'stripePayment'])->name('subscription.stripe.payment');
    }
);
//-------------------------------Settings-------------------------------------------
Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
            'CheckSubscription'
        ],
    ],
    function () {
        Route::get('settings', [SettingController::class, 'index'])->name('setting.index');

        Route::post('settings/account', [SettingController::class, 'accountData'])->name('setting.account');
        Route::delete('settings/account/delete', [SettingController::class, 'accountDelete'])->name('setting.account.delete');
        Route::post('settings/password', [SettingController::class, 'passwordData'])->name('setting.password');
        Route::post('settings/general', [SettingController::class, 'generalData'])->name('setting.general');
        Route::post('settings/smtp', [SettingController::class, 'smtpData'])->name('setting.smtp');
        Route::get('settings/smtp-test', [SettingController::class, 'smtpTest'])->name('setting.smtp.test');
        Route::post('settings/smtp-test', [SettingController::class, 'smtpTestMailSend'])->name('setting.smtp.testing');
        Route::post('settings/payment', [SettingController::class, 'paymentData'])->name('setting.payment');
        Route::post('settings/site-seo', [SettingController::class, 'siteSEOData'])->name('setting.site.seo');
        Route::post('settings/google-recaptcha', [SettingController::class, 'googleRecaptchaData'])->name('setting.google.recaptcha');
        Route::post('settings/company', [SettingController::class, 'companyData'])->name('setting.company');
        Route::post('settings/2fa', [SettingController::class, 'twofaEnable'])->name('setting.twofa.enable');
        Route::post('settings/agreement', [SettingController::class, 'agreement'])->name('setting.agreement');

        Route::get('footer-setting', [SettingController::class, 'footerSetting'])->name('footerSetting');
        Route::post('settings/footer', [SettingController::class, 'footerData'])->name('setting.footer');

        Route::get('language/{lang}', [SettingController::class, 'lanquageChange'])->name('language.change');
        Route::post('theme/settings', [SettingController::class, 'themeSettings'])->name('theme.settings');

        Route::post('storage/settings', [SettingController::class, 'storageSetting'])->name('storage.setting');

        Route::post('settings/twilio', [SettingController::class, 'twilio'])->name('setting.twilio');

        Route::post('openai/settings', [SettingController::class, 'openai'])->name('openai.settings');
        Route::post('mobile/settings', [SettingController::class, 'mobile'])->name('mobile.settings');

        Route::post('/cronjob-token-generate', [SettingController::class, 'keyGenerate'])->name('cronjob.token.generate');
        Route::post('/cronjob-token-remove', [SettingController::class, 'keyRemove'])->name('cronjob.token.remove');
    }
);


//-------------------------------Role & Permissions-------------------------------------------
Route::resource('permission', PermissionController::class)->middleware(
    [
        'auth',
        'XSS',
        'CheckSubscription'
    ]
);

Route::resource('role', RoleController::class)->middleware(
    [
        'auth',
        'XSS',
        'CheckSubscription'
    ]
);

//-------------------------------Note-------------------------------------------
Route::resource('note', NoticeBoardController::class)->middleware(
    [
        'auth',
        'XSS',
        'CheckSubscription'
    ]
);

//-------------------------------Contact-------------------------------------------
Route::resource('contact', ContactController::class)->middleware(
    [
        'auth',
        'XSS',
        'CheckSubscription'

     ]
);

//-------------------------------logged History-------------------------------------------

Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
            'CheckSubscription'
        ],
    ],
    function () {

        Route::get('logged/history', [UserController::class, 'loggedHistory'])->name('logged.history');
        Route::get('logged/{id}/history/show', [UserController::class, 'loggedHistoryShow'])->name('logged.history.show');
        Route::delete('logged/{id}/history', [UserController::class, 'loggedHistoryDestroy'])->name('logged.history.destroy');
    }
);


//-------------------------------Plan Payment-------------------------------------------
Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ],
    function () {
        Route::post('subscription/{id}/bank-transfer', [PaymentController::class, 'subscriptionBankTransfer'])->name('subscription.bank.transfer');
        Route::get('subscription/{id}/bank-transfer/action/{status}', [PaymentController::class, 'subscriptionBankTransferAction'])->name('subscription.bank.transfer.action');
        Route::post('subscription/{id}/paypal', [PaymentController::class, 'subscriptionPaypal'])->name('subscription.paypal');
        Route::get('subscription/{id}/paypal/{status}', [PaymentController::class, 'subscriptionPaypalStatus'])->name('subscription.paypal.status');
        Route::post('subscription/{id}/{user_id}/manual-assign-package', [PaymentController::class, 'subscriptionManualAssignPackage'])->name('subscription.manual_assign_package');
    }
);

//-------------------------------Notification-------------------------------------------
Route::resource('notification', NotificationController::class)->middleware(
    [
        'auth',
        'XSS',
        'CheckSubscription'
    ]
);

Route::get('email-verification/{token}', [VerifyEmailController::class, 'verifyEmail'])->name('email-verification')->middleware(
    [
        'XSS',
    ]
);

//-------------------------------FAQ-------------------------------------------
Route::resource('FAQ', FAQController::class)->middleware(
    [
        'auth',
        'XSS',
        'CheckSubscription'
    ]
);

//-------------------------------Home Page-------------------------------------------
Route::resource('homepage', HomePageController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

Route::post('/ckeditor/upload', [PageController::class, 'upload'])->name('ckeditor.upload')->middleware(
    [
        'auth',
        'XSS',
    ]
);

//-------------------------------FAQ-------------------------------------------
Route::resource('pages', PageController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

//-------------------------------Auth page-------------------------------------------
Route::resource('authPage', AuthPageController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);


//-------------------------------Property-------------------------------------------
Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
            'CheckSubscription'
        ],
    ],
    function () {
        Route::resource('property', PropertyController::class);
        Route::get('property/{pid}/unit/create', [PropertyController::class, 'unitCreate'])->name('unit.create');
        Route::post('property/{pid}/unit/store', [PropertyController::class, 'unitStore'])->name('unit.store');
        Route::get('units/direct-create', [PropertyController::class, 'unitdirectCreate'])->name('unit.direct-create');
        Route::get('units/bulk-create', [\App\Http\Controllers\UnitBulkController::class, 'create'])->name('unit.bulk.create');
        Route::post('units/bulk-store', [\App\Http\Controllers\UnitBulkController::class, 'store'])->name('unit.bulk.store');
        Route::post('unit/direct-store', [PropertyController::class, 'unitdirectStore'])->name('unit.direct-store');
        Route::get('property/{pid}/unit/{id}/edit', [PropertyController::class, 'unitEdit'])->name('unit.edit');
        Route::get('unit/{id}/show', [PropertyController::class, 'unitShow'])->name('unit.show');
        Route::get('units', [PropertyController::class, 'units'])->name('unit.index');
        Route::put('property/{pid}/unit/{id}/update', [PropertyController::class, 'unitUpdate'])->name('unit.update');
        Route::delete('property/{pid}/unit/{id}/destroy', [PropertyController::class, 'unitDestroy'])->name('unit.destroy');
        Route::get('property/{pid}/unit', [PropertyController::class, 'getPropertyUnit'])->name('property.unit');
        Route::delete('/property/document/{pid}', [PropertyController::class, 'fileDestroy'])->name('property.image.delete');
    }
);

//-------------------------------Tenant-------------------------------------------
Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
            'CheckSubscription'
        ],
    ],
    function () {
        Route::resource('tenant', TenantController::class);

        Route::get('tenant/{tid}/exit', [TenantController::class, 'tenantExit'])->name('tenant.exit');
        Route::put('tenant/{tid}/update', [TenantController::class, 'tenantExitUpdate'])->name('tenant.exitupdate');

        Route::delete('/tenant/document/{id}', [TenantController::class, 'fileDestroy'])->name('tenant.document.delete');
        Route::get('tenant/{tid}/document/create', [TenantDepositController::class, 'documentCreate'])->name('tenant.document.create');
        Route::post('tenant/{tid}/document', [TenantDepositController::class, 'documentStore'])->name('tenant.document.store');
        Route::get('tenant/document/{id}/edit', [TenantDepositController::class, 'documentEdit'])->name('tenant.document.edit');
        Route::put('tenant/document/{id}', [TenantDepositController::class, 'documentUpdate'])->name('tenant.document.update');
        Route::get('tenant/{tid}/deposit/create', [TenantDepositController::class, 'create'])->name('tenant.deposit.create');
        // v3.5 move-in checklist and rent changes
        Route::post('tenant/{tid}/checklist/{item}', [TenantLifecycleController::class, 'toggleChecklist'])->name('tenant.checklist.toggle');
        Route::get('tenant/{tid}/rent-increase/create', [TenantLifecycleController::class, 'increaseCreate'])->name('tenant.rent-increase.create');
        Route::post('tenant/{tid}/rent-increase', [TenantLifecycleController::class, 'increaseStore'])->name('tenant.rent-increase.store');
        Route::post('tenant/{tid}/rent-increase/{id}/cancel', [TenantLifecycleController::class, 'increaseCancel'])->name('tenant.rent-increase.cancel');
        Route::get('tenant/{tid}/rent-increase/{id}/letter', [TenantLifecycleController::class, 'increaseLetter'])->name('tenant.rent-increase.letter');
        Route::post('tenant/{tid}/deposit', [TenantDepositController::class, 'store'])->name('tenant.deposit.store');
        Route::delete('tenant/deposit/{id}', [TenantDepositController::class, 'destroy'])->name('tenant.deposit.destroy');

        // v2.9 leads, viewings and rental applications
        Route::get('lead/{id}/viewing/create', [LeadController::class, 'viewingCreate'])->name('lead.viewing.create');
        Route::post('lead/{id}/viewing', [LeadController::class, 'viewingStore'])->name('lead.viewing.store');
        Route::get('lead/viewing/{id}/edit', [LeadController::class, 'viewingEdit'])->name('lead.viewing.edit');
        Route::put('lead/viewing/{id}', [LeadController::class, 'viewingUpdate'])->name('lead.viewing.update');
        Route::delete('lead/viewing/{id}', [LeadController::class, 'viewingDestroy'])->name('lead.viewing.destroy');
        Route::resource('lead', LeadController::class);
        Route::get('rental-application', [RentalApplicationController::class, 'index'])->name('rental-application.index');
        Route::get('rental-application/{id}', [RentalApplicationController::class, 'show'])->name('rental-application.show');
        Route::post('rental-application/{id}/approve', [RentalApplicationController::class, 'approve'])->name('rental-application.approve');
        Route::post('rental-application/{id}/reject', [RentalApplicationController::class, 'reject'])->name('rental-application.reject');

        // v3.0 operations
        Route::post('inspection/{id}/sign', [InspectionController::class, 'sign'])->name('inspection.sign');
        Route::resource('inspection', InspectionController::class);
        // 'vendor' would clash with the vendor/ directory when the app is served from this folder
        Route::resource('vendors', VendorController::class)->names('vendor');
        Route::resource('maintenance-schedule', MaintenanceScheduleController::class)->except(['show']);
        Route::get('meter-reading/last', [MeterReadingController::class, 'last'])->name('meter-reading.last');
        Route::post('meter-reading/{id}/bill', [MeterReadingController::class, 'bill'])->name('meter-reading.bill');
        Route::resource('meter-reading', MeterReadingController::class)->only(['index', 'create', 'store', 'destroy']);
        Route::post('ai-writer', [\App\Http\Controllers\AiWriterController::class, 'generate'])->name('ai.writer')->middleware('throttle:ai-writer');
        Route::post('property-ai-description', [PropertyController::class, 'aiDescription'])->name('property.ai.description')->middleware('throttle:ai-writer');

        // v3.3 community
        Route::get('facility/{id}/slots', [FacilityController::class, 'slots'])->name('facility.slots');
        Route::post('facility/{id}/book', [FacilityController::class, 'book'])->name('facility.book');
        Route::post('facility/booking/{id}/cancel', [FacilityController::class, 'cancel'])->name('facility.booking.cancel');
        Route::post('facility/booking/{id}/decide', [FacilityController::class, 'decide'])->name('facility.booking.decide');
        Route::resource('facility', FacilityController::class)->except(['show']);
        Route::get('visitors', [VisitorController::class, 'index'])->name('visitor.index');
        Route::post('visitors', [VisitorController::class, 'store'])->name('visitor.store');
        Route::post('visitors/{id}/cancel', [VisitorController::class, 'cancel'])->name('visitor.cancel');
        Route::post('visitors/check-in', [VisitorController::class, 'checkIn'])->name('visitor.checkin');
        Route::post('visitors/walk-in', [VisitorController::class, 'walkIn'])->name('visitor.walkin');
        Route::post('visitors/{id}/check-out', [VisitorController::class, 'checkOut'])->name('visitor.checkout');
        Route::get('parking', [ParkingController::class, 'index'])->name('parking.index');
        Route::post('parking', [ParkingController::class, 'store'])->name('parking.store');
        Route::delete('parking/{id}', [ParkingController::class, 'destroy'])->name('parking.destroy');
        Route::post('parking/{id}/assign', [ParkingController::class, 'assign'])->name('parking.assign');
        Route::post('parking/{id}/release', [ParkingController::class, 'release'])->name('parking.release');
        Route::post('poll/{id}/vote', [PollController::class, 'vote'])->name('poll.vote');
        Route::post('poll/{id}/close', [PollController::class, 'close'])->name('poll.close');
        Route::resource('poll', PollController::class)->only(['index', 'create', 'store', 'show', 'destroy']);

        // v3.2 AI assistant
        Route::get('assistant', [AssistantController::class, 'index'])->name('assistant.index');
        Route::post('assistant/message', [AssistantController::class, 'message'])->name('assistant.message')->middleware('throttle:assistant-message');
        Route::post('assistant/request', [AssistantController::class, 'createRequest'])->name('assistant.request')->middleware('throttle:assistant-request');
        Route::get('assistant/clear', [AssistantController::class, 'clear'])->name('assistant.clear');
        Route::get('assistant/updates', [AssistantController::class, 'updates'])->name('assistant.updates');

        // v3.1 money
        Route::get('autopay', [AutopayController::class, 'index'])->name('autopay.index');
        Route::post('autopay/setup-intent', [AutopayController::class, 'setupIntent'])->name('autopay.setup-intent');
        Route::post('autopay/save', [AutopayController::class, 'save'])->name('autopay.save');
        Route::post('autopay/disable', [AutopayController::class, 'disable'])->name('autopay.disable');
        Route::post('autopay/razorpay/order', [AutopayController::class, 'razorpayOrder'])->name('autopay.razorpay.order');
        Route::post('autopay/razorpay/save', [AutopayController::class, 'razorpaySave'])->name('autopay.razorpay.save');

        // v3.4 push notifications, assistant chats, invoice pay links
        Route::get('push/key', [PushSubscriptionController::class, 'key'])->name('push.key');
        Route::post('push/subscribe', [PushSubscriptionController::class, 'store'])->name('push.subscribe');
        Route::post('push/unsubscribe', [PushSubscriptionController::class, 'destroy'])->name('push.unsubscribe');
        Route::post('push/test', [PushSubscriptionController::class, 'test'])->name('push.test')->middleware('throttle:6,1');
        Route::get('assistant-chats', [AssistantChatController::class, 'index'])->name('assistant-chats.index');
        Route::get('assistant-chats/{tenant}', [AssistantChatController::class, 'show'])->name('assistant-chats.show');
        Route::post('assistant-chats/{tenant}/reply', [AssistantChatController::class, 'reply'])->name('assistant-chats.reply');
        Route::post('assistant-chats/{tenant}/resolve', [AssistantChatController::class, 'resolve'])->name('assistant-chats.resolve');
        Route::post('invoice/{id}/pay-link', [InvoicePayLinkController::class, 'send'])->name('invoice.pay-link.send');
        Route::post('landlord/{id}/payout', [LandlordController::class, 'payoutStore'])->name('landlord.payout.store');
        Route::delete('landlord/payout/{id}', [LandlordController::class, 'payoutDestroy'])->name('landlord.payout.destroy');
        Route::resource('landlord', LandlordController::class);
        Route::get('report/ledger', [\App\Http\Controllers\ReportController::class, 'ledger'])->name('report.ledger');
        Route::get('report/tax-summary', [\App\Http\Controllers\ReportController::class, 'taxSummary'])->name('report.tax_summary');
        Route::get('report/arrears', [\App\Http\Controllers\FinancialReportController::class, 'arrears'])->name('report.arrears');
        Route::get('report/rent-roll', [\App\Http\Controllers\FinancialReportController::class, 'rentRoll'])->name('report.rent_roll');
        Route::get('report/landlord-payouts', [\App\Http\Controllers\FinancialReportController::class, 'payouts'])->name('report.payouts');
        Route::post('maintenance-request/{id}/rate', [\App\Http\Controllers\MaintenanceRequestController::class, 'rate'])->name('maintenance-request.rate');
        Route::post('maintenance-request/{id}/on-the-way', [\App\Http\Controllers\MaintenanceRequestController::class, 'onTheWay'])->name('maintenance-request.on-the-way');
        Route::get('tenant/{pid}/unit', [TenantController::class, 'getPropertyUnit'])->name('tenant.unit');

        Route::get('/tenant/unit-details/{id}', [TenantController::class, 'getUnitDetails'])->name('tenant.unit.details');
        Route::get('lease-renew', [TenantController::class, 'leaseRenew'])->name('lease.renew');
        Route::post('lease-renew-store', [TenantController::class, 'leaseRenewStore'])->name('lease.renew.store');
        Route::get('calendar', [TenantController::class, 'calendar'])->name('calendar');
    }
);

//-------------------------------Type-------------------------------------------
Route::resource('type', TypeController::class)->middleware(
    [
        'auth',
        'XSS',
        'CheckSubscription'
    ]
);

//-------------------------------Invoice-------------------------------------------

Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
            'CheckSubscription'
        ],
    ],
    function () {
        Route::get('invoice/{id}/payment/create', [InvoiceController::class, 'invoicePaymentCreate'])->name('invoice.payment.create');
        Route::post('invoice/{id}/payment/store', [InvoiceController::class, 'invoicePaymentStore'])->name('invoice.payment.store');
        Route::delete('invoice/{id}/payment/{pid}/destroy', [InvoiceController::class, 'invoicePaymentDestroy'])->name('invoice.payment.destroy');
        Route::delete('invoice/type/destroy', [InvoiceController::class, 'invoiceTypeDestroy'])->name('invoice.type.destroy');
        Route::get('invoice/{id}/reminder', [InvoiceController::class, 'invoicePaymentRemind'])->name('invoice.reminder');
        Route::post('invoice/{id}/reminder', [InvoiceController::class, 'invoicePaymentRemindData'])->name('invoice.sendEmail');
        // v3.5 invoice tools (before the resource so "invoice/generate" is not read as an invoice id)
        Route::get('invoice/generate', [InvoiceToolsController::class, 'generate'])->name('invoice.generate');
        Route::post('invoice/generate', [InvoiceToolsController::class, 'generateStore'])->name('invoice.generate.store');
        Route::get('invoice/{id}/payment/{payment}/receipt', [InvoiceToolsController::class, 'receipt'])->name('invoice.payment.receipt');
        Route::get('invoice/{id}/credit/create', [InvoiceToolsController::class, 'creditCreate'])->name('invoice.credit.create');
        Route::post('invoice/{id}/credit', [InvoiceToolsController::class, 'creditStore'])->name('invoice.credit.store');
        Route::delete('invoice/{id}/credit/{note}', [InvoiceToolsController::class, 'creditDestroy'])->name('invoice.credit.destroy');
        Route::get('invoice/{id}/credit/{note}/pdf', [InvoiceToolsController::class, 'creditPdf'])->name('invoice.credit.pdf');
        Route::resource('invoice', InvoiceController::class);
    }
);

//-------------------------------Expense-------------------------------------------
Route::resource('expense', ExpenseController::class)->middleware(
    [
        'auth',
        'XSS',
        'CheckSubscription'
    ]
);

//-------------------------------Maintainer-------------------------------------------
Route::resource('maintainer', MaintainerController::class)->middleware(
    [
        'auth',
        'XSS',
        'CheckSubscription'
    ]
);

//-------------------------------Maintenance Request-------------------------------------------


Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
            'CheckSubscription'
        ],
    ],
    function () {
        Route::get('maintenance-request/pending', [MaintenanceRequestController::class, 'pendingRequest'])->name('maintenance-request.pending');
        Route::get('maintenance-request/in-progress', [MaintenanceRequestController::class, 'inProgressRequest'])->name('maintenance-request.inprogress');
        Route::get('maintenance-request/{id}/action', [MaintenanceRequestController::class, 'action'])->name('maintenance-request.action');
        Route::post('maintenance-request/{id}/action', [MaintenanceRequestController::class, 'actionData'])->name('maintenance-request.action');
        Route::resource('maintenance-request', MaintenanceRequestController::class);
        Route::post('maintenance-request/comment/{id}', [MaintenanceRequestController::class, 'comment'])->name('maintenance-request.comment');
        Route::delete('/maintenance-request/comment/{id}', [MaintenanceRequestController::class, 'commentDestroy'])->name('maintenance-request.comment.destroy');
        Route::post('maintainer/request/status/update', [MaintainerController::class, 'updateRequestStatus'])->name('maintainer.request.status.update');
        Route::get('maintenance-assign/', [MaintenanceRequestController::class, 'maintenanceAssign'])->name('maintenance.assign');
        Route::post('maintenance-assign-store/', [MaintenanceRequestController::class, 'maintenanceAssignStore'])->name('maintenance.assign.store');
        Route::get('property-based-maintainer', [MaintenanceRequestController::class, 'propertyBasedMaintainer'])->name('property.based.maintainer');
    }
);

//-------------------------------Plan Payment-------------------------------------------

Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',

        ],
    ],
    function () {
        Route::post('subscription/{id}/bank-transfer', [PaymentController::class, 'subscriptionBankTransfer'])->name('subscription.bank.transfer');
        Route::get('subscription/{id}/bank-transfer/action/{status}', [PaymentController::class, 'subscriptionBankTransferAction'])->name('subscription.bank.transfer.action');
        Route::post('subscription/{id}/paypal', [PaymentController::class, 'subscriptionPaypal'])->name('subscription.paypal');
        Route::get('subscription/{id}/paypal/{status}', [PaymentController::class, 'subscriptionPaypalStatus'])->name('subscription.paypal.status');
        Route::get('subscription/flutterwave/{sid}/{tx_ref}', [PaymentController::class, 'subscriptionFlutterwave'])->name('subscription.flutterwave');

        Route::post('/subscription-pay-with-paystack', [PaymentController::class, 'subscriptionPaystack'])->name('subscription.pay.with.paystack')->middleware(['auth', 'XSS']);
        Route::get('/subscription/paystack/{pay_id}/{s_id}', [PaymentController::class, 'subscriptionPaystackStatus'])->name('subscription.paystack');

        Route::post('/subscription-pay-with-razorpay', [PaymentController::class, 'subscriptionPayWithRazorpay'])->name('subscription.pay.with.razorpay')->middleware(['auth', 'XSS']);
        Route::get('/subscription/razorpay/{txref}/{subscription_id}', [PaymentController::class, 'subscriptionPaymentStatus'])->name('subscription.razorpay');
    }
);

//-------------------------------Reports-------------------------------------------
Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
            'CheckSubscription'
        ],
    ],
    function () {
        Route::get('report/income', [ReportController::class, 'income'])->name('report.income');
        Route::get('report/expense', [ReportController::class, 'expense'])->name('report.expense');
        Route::get('report/profit-loss', [ReportController::class, 'reportProfitLoss'])->name('report.profit_loss');
        Route::get('report/property-unit', [ReportController::class, 'reportPropertyUnit'])->name('report.property_unit');
        Route::get('report/tenant', [ReportController::class, 'tenant'])->name('report.tenant');
        Route::get('report/maintenance', [ReportController::class, 'maintenance'])->name('report.maintenance');
    }
);

//-------------------------------Agreement-------------------------------------------
Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
            'CheckSubscription'
        ],
    ],
    function () {
        Route::resource('agreement', AgreementController::class);
        Route::post('agreement/{id}/tenant-signature', [AgreementController::class, 'saveTenantSignature'])->name('agreement.saveTenantSignature');
        Route::get('agreement/{id}/pdf', [AgreementController::class, 'downloadPdf'])->name('agreement.pdf');
    }
);


//-------------------------------Amenity-------------------------------------------
Route::resource('amenity', AmenityController::class)->middleware(
    [
        'auth',
        'XSS',
        'CheckSubscription'
    ]
);

//-------------------------------Advantage-------------------------------------------
Route::resource('advantage', AdvantageController::class)->middleware(
    [
        'auth',
        'XSS',
        'CheckSubscription'
    ]
);



Route::prefix('web/{code}')->middleware('website.locale')->group(function () {
    Route::get('/', [FrontendController::class, 'themePage'])->name('web.page');
    Route::get('/lang/{lang}', [FrontendController::class, 'setLanguage'])->name('web.lang');
    Route::get('/page/{slug}', [\App\Http\Controllers\WebsitePageController::class, 'show'])->name('web.custom.page');
    Route::get('/sitemap.xml', [\App\Http\Controllers\WebsiteVisitorController::class, 'sitemap'])->name('web.sitemap');
    Route::get('/saved', [\App\Http\Controllers\WebsiteVisitorController::class, 'saved'])->name('web.saved');
    Route::get('/cards', [\App\Http\Controllers\WebsiteVisitorController::class, 'cards'])->name('web.cards');
    Route::get('/compare', [\App\Http\Controllers\WebsiteVisitorController::class, 'compare'])->name('web.compare');
    Route::post('/alerts', [\App\Http\Controllers\WebsiteVisitorController::class, 'alertStore'])->name('web.alerts.store')->middleware('throttle:public-forms');
    Route::get('/alerts/stop/{token}', [\App\Http\Controllers\WebsiteVisitorController::class, 'alertUnsubscribe'])->name('web.alerts.unsubscribe');

    Route::get('/search/location', [FrontendController::class, 'searchLocation'])->name('search.location');

    Route::get('/blog', [FrontendController::class, 'blogPage'])->name('blog.home');
    Route::get('/blog/{slug}', [FrontendController::class, 'blogDetailPage'])->name('blog.detail');

    Route::get('/contact', [FrontendController::class, 'contactPage'])->name('contact.home');

    Route::get('/properties', [FrontendController::class, 'propertyPage'])->name('property.home');
    Route::get('/search/filter', [FrontendController::class, 'search'])->name('search.filter');
    Route::get('/search/package', [FrontendController::class, 'search'])->name('search.package');

    Route::get('/property/{id}', [FrontendController::class, 'detailPage'])->name('property.detail');

    Route::post('contact-us', [ContactController::class, 'frontDetailStore'])->name('contact-us');

    Route::post('enquiry', [PublicLeadController::class, 'enquiryStore'])->name('web.enquiry')->middleware('throttle:public-forms');
    Route::get('apply/{id}', [PublicLeadController::class, 'applyForm'])->name('web.apply');
    Route::post('apply', [PublicLeadController::class, 'applyStore'])->name('web.apply.store')->middleware('throttle:public-forms');
});


Route::get('/get-states', [FrontendController::class, 'getStates'])->name('get-states');
Route::get('/get-cities', [FrontendController::class, 'getCities'])->name('get-cities');



//-------------------------------Front Home Page-------------------------------------------
Route::middleware(['auth', 'XSS', 'CheckSubscription'])->group(function () {
    Route::post('front-home-order', [FrontendController::class, 'order'])->name('front-home.order');
    Route::post('front-home/{id}/toggle', [FrontendController::class, 'toggle'])->name('front-home.toggle');
    Route::post('front-home-design', [FrontendController::class, 'design'])->name('front-home.design');
    Route::post('front-home-visitors', [FrontendController::class, 'visitors'])->name('front-home.visitors');
    Route::post('front-home-seo', [FrontendController::class, 'seo'])->name('front-home.seo');
    Route::get('website-stats', [FrontendController::class, 'stats'])->name('website-stats');
    Route::get('front-home-domain-check', [FrontendController::class, 'domainCheck'])->name('front-home.domain-check');
    Route::get('website-reviews', [\App\Http\Controllers\WebsiteReviewController::class, 'index'])->name('website-reviews.index');
    Route::post('website-reviews/{id}/status', [\App\Http\Controllers\WebsiteReviewController::class, 'status'])->name('website-reviews.status');
    Route::delete('website-reviews/{id}', [\App\Http\Controllers\WebsiteReviewController::class, 'destroy'])->name('website-reviews.destroy');
    Route::get('my-review', [\App\Http\Controllers\WebsiteReviewController::class, 'mine'])->name('my-review');
    Route::post('my-review', [\App\Http\Controllers\WebsiteReviewController::class, 'save'])->name('my-review.save');
    Route::resource('website-pages', \App\Http\Controllers\WebsitePageController::class)->except(['show']);
});
Route::resource('front-home', FrontendController::class)->middleware(
    [
        'auth',
        'XSS',
        'CheckSubscription'
    ]
);


//-------------------------------Additional Page-------------------------------------------
Route::resource('additional', AdditionalController::class)->middleware(
    [
        'auth',
        'XSS',
        'CheckSubscription'
    ]
);


//-------------------------------Blog-------------------------------------------
Route::resource('blog', BlogController::class)->middleware(
    [
        'auth',
        'XSS',
        'CheckSubscription'
    ]
);



//-------------------------------Invoice Payment-------------------------------------------

Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
            'CheckSubscription'
        ],
    ],
    function () {

        Route::post('invoice/{id}/banktransfer/payment', [InvoicePaymentController::class, 'banktransferPayment'])->name('invoice.banktransfer.payment');
        Route::post('invoice/{id}/stripe/payment', [InvoicePaymentController::class, 'stripePayment'])->name('invoice.stripe.payment');
        Route::post('invoice/{id}/paypal', [InvoicePaymentController::class, 'invoicePaypal'])->name('invoice.paypal');
        Route::get('invoice/{id}/paypal/{status}', [InvoicePaymentController::class, 'invoicePaypalStatus'])->name('invoice.paypal.status');
        Route::get('invoice/flutterwave/{id}/{tx_ref}', [InvoicePaymentController::class, 'invoiceFlutterwave'])->name('invoice.flutterwave');
        Route::get('unit/{pid}/tenant', [InvoiceController::class, 'unitByTenant'])->name('unit.by.tenant');

        Route::post('invoice/{id}/paystack/payment', [InvoicePaymentController::class, 'invoicePaystack'])->name('invoice.paystack.payment');
        Route::get('/invoice/paystack/{pay_id}/{i_id}', [InvoicePaymentController::class, 'invoicePaystackStatus'])->name('invoice.paystack');

        Route::post('/invoice-pay-with-razorpay/{id}', [InvoicePaymentController::class, 'invoicePayWithRazorpay'])->name('invoice.razorpay.payment');
        Route::get('/invoice/razorpay/{txref}/{invoice_id}', [InvoicePaymentController::class, 'invoiceRazorpayStatus'])->name('invoice.razorpay');
    }
);
Route::get('cronjob', [CronjobController::class, 'run'])->name('cronjob');

Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
            'CheckSubscription'
        ],
    ],
    function () {
        Route::resource('n8n', N8nController::class);
    }
);

Route::get('page/{slug}', [PageController::class, 'page'])->name('page');
// built-in public pages linked from the landing page footer
Route::get('about-us', [\App\Http\Controllers\InfoPageController::class, 'about'])->name('info.about');
Route::get('contact-us', [\App\Http\Controllers\InfoPageController::class, 'contact'])->name('info.contact');
Route::get('support', [\App\Http\Controllers\InfoPageController::class, 'support'])->name('info.support');
//-------------------------------FAQ-------------------------------------------
Route::impersonate();
//-------------------------------v3.2 PWA & WhatsApp (public)-------------------------------------------
Route::get('manifest.webmanifest', [PwaController::class, 'manifest'])->name('pwa.manifest');
// no .js extension: the .htaccess serves *.js as static files and never reaches Laravel
Route::get('service-worker', [PwaController::class, 'serviceWorker'])->name('pwa.sw');
Route::get('offline', [PwaController::class, 'offline'])->name('pwa.offline');
//-------------------------------v3.4 public invoice pay link-------------------------------------------
Route::middleware(['throttle:pay-link', 'XSS'])->group(function () {
    Route::get('pay/{token}', [InvoicePayLinkController::class, 'show'])->name('pay.show');
    Route::post('pay/{token}/stripe', [InvoicePayLinkController::class, 'stripe'])->name('pay.stripe');
    Route::get('pay/{token}/stripe/return', [InvoicePayLinkController::class, 'stripeReturn'])->name('pay.stripe.return');
    Route::post('pay/{token}/razorpay', [InvoicePayLinkController::class, 'razorpayOrder'])->name('pay.razorpay');
    Route::post('pay/{token}/razorpay/verify', [InvoicePayLinkController::class, 'razorpayVerify'])->name('pay.razorpay.verify');
});
Route::post('webhook/whatsapp/{code}', [WhatsappWebhookController::class, 'handle'])->name('webhook.whatsapp')->middleware('throttle:whatsapp-webhook');
