<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MaintainerApiController;
use App\Http\Controllers\Api\TenantApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile app API (v1) — tenants and maintainers
|--------------------------------------------------------------------------
| Sign in with POST /api/v1/auth/login to get a token, then send "Authorization: Bearer <token>"
| and "Accept: application/json" with every request. Full reference: docs/mobile-api.md
*/

Route::prefix('v1')->group(function () {
    Route::get('app-config', [AuthController::class, 'appConfig'])->middleware('throttle:60,1');
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:mobile-login');
    Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:mobile-login');

    Route::middleware(['auth:sanctum', 'mobile'])->group(function () {
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::post('auth/profile', [AuthController::class, 'updateProfile']);
        Route::post('devices', [AuthController::class, 'registerDevice']);
        Route::post('devices/remove', [AuthController::class, 'removeDevice']);
        Route::post('devices/test', [AuthController::class, 'testPush'])->middleware('throttle:5,1');
        Route::get('notifications', [AuthController::class, 'notifications']);
        Route::get('notifications/unread', [AuthController::class, 'unreadCount']);
        Route::post('notifications/read', [AuthController::class, 'markRead']);
    });

    Route::middleware(['auth:sanctum', 'mobile:tenant'])->prefix('tenant')->group(function () {
        Route::get('home', [TenantApiController::class, 'home']);
        Route::get('invoices', [TenantApiController::class, 'invoices']);
        Route::get('invoices/{id}', [TenantApiController::class, 'invoice']);
        Route::get('invoices/{id}/payments/{payment}/receipt', [TenantApiController::class, 'receipt']);
        Route::get('repairs', [TenantApiController::class, 'repairs']);
        Route::get('repair-types', [TenantApiController::class, 'repairTypes']);
        Route::post('repairs', [TenantApiController::class, 'createRepair'])->middleware('throttle:20,1');
        Route::get('repairs/{id}', [TenantApiController::class, 'repair']);
        Route::post('repairs/{id}/comments', [TenantApiController::class, 'comment'])->middleware('throttle:30,1');
        Route::post('repairs/{id}/rate', [TenantApiController::class, 'rate']);
        Route::get('notices', [TenantApiController::class, 'notices']);
        Route::get('chat', [TenantApiController::class, 'chat']);
        Route::post('chat', [TenantApiController::class, 'sendChat'])->middleware('throttle:assistant-message');
        Route::post('chat/request', [TenantApiController::class, 'chatRequest'])->middleware('throttle:assistant-request');
        Route::get('agreements', [TenantApiController::class, 'agreements']);
        Route::get('agreements/{id}/pdf', [TenantApiController::class, 'agreementPdf']);
        Route::post('agreements/{id}/sign', [TenantApiController::class, 'signAgreement']);
        Route::get('facilities', [TenantApiController::class, 'facilities']);
        Route::get('facilities/{id}/slots', [TenantApiController::class, 'slots']);
        Route::post('facilities/{id}/book', [TenantApiController::class, 'book']);
        Route::get('bookings', [TenantApiController::class, 'bookings']);
        Route::post('bookings/{id}/cancel', [TenantApiController::class, 'cancelBooking']);
        Route::get('visitors', [TenantApiController::class, 'visitors']);
        Route::post('visitors', [TenantApiController::class, 'createVisitor'])->middleware('throttle:30,1');
        Route::post('visitors/{id}/cancel', [TenantApiController::class, 'cancelVisitor']);
        Route::get('payments', [TenantApiController::class, 'payments']);
        Route::get('deposit', [TenantApiController::class, 'deposit']);
        Route::get('documents', [TenantApiController::class, 'documents']);
        Route::get('inspections', [TenantApiController::class, 'inspections']);
        Route::get('inspections/{id}', [TenantApiController::class, 'inspection']);
        Route::post('inspections/{id}/sign', [TenantApiController::class, 'signInspection']);
        Route::get('meter-readings', [TenantApiController::class, 'meterReadings']);
        Route::get('polls', [TenantApiController::class, 'polls']);
        Route::post('polls/{id}/vote', [TenantApiController::class, 'vote']);
    });

    Route::middleware(['auth:sanctum', 'mobile:maintainer'])->prefix('maintainer')->group(function () {
        Route::get('home', [MaintainerApiController::class, 'home']);
        Route::get('jobs', [MaintainerApiController::class, 'index']);
        Route::get('schedule', [MaintainerApiController::class, 'schedule']);
        Route::get('profile', [MaintainerApiController::class, 'profile']);
        Route::get('jobs/{id}', [MaintainerApiController::class, 'show']);
        Route::post('jobs/{id}/on-the-way', [MaintainerApiController::class, 'onTheWay']);
        Route::post('jobs/{id}/status', [MaintainerApiController::class, 'update']);
        Route::post('jobs/{id}/comments', [MaintainerApiController::class, 'comment'])->middleware('throttle:30,1');
    });
});
