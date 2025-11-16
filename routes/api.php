<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\MomoController;
use App\Http\Controllers\Api\NetflixProviderController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\StatisticController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\SubscriptionHistoryController;
use App\Http\Controllers\Api\YoutubeProviderController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->prefix('auth')->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('verify-otp', 'verifyOtp');
    Route::post('resend-otp', 'resendOtp');
    Route::post('login-google', 'loginGoogle');

    Route::middleware('auth:api')->group(function () {
        Route::post('logout', 'logout');
        Route::get('me', [AccountController::class, 'me']);
    });
});

Route::middleware('auth:api')->group(function () {
    // Service Routes
    Route::apiResource('services', ServiceController::class);
    Route::controller(ServiceController::class)->prefix('services')->group(function () {
        Route::get('filter/base', 'getBase');
        Route::delete('', 'bulkDestroy');
    });
    Route::get('admin/services/{id}/subscriptions', [SubscriptionController::class, 'getSubscriptionByServiceId']);

    // Subscription Routes
    Route::apiResource('subscriptions', SubscriptionController::class);
    Route::get('subscriptions/{id}/history', [SubscriptionHistoryController::class, 'getBySubscriptionId']);
    Route::post('subscriptions/{id}/renew', [SubscriptionController::class, 'renew']);
    Route::post('subscriptions/{id}/unsubscribe', [SubscriptionController::class, 'unsubscribe']);
    Route::post('subscriptions/{id}/mark-as-paid', [SubscriptionController::class, 'mark_as_paid']);

    // Notification Routes
    Route::controller(NotificationController::class)->prefix('notifications')->group(function () {
        Route::get('/', 'index');
        Route::get('/{id}', 'show');
        Route::get('/admin/get', 'getAdminLogNotifications');
        Route::post('/{id}/read', 'markAsRead');
        Route::post('/read-all', 'markAllAsRead');
        Route::post('/admin/send-reminder', 'reminderSubscription');
        Route::post('/admin/send-message', 'sendMessage');
        Route::delete('/{id}', 'destroy');
    });

    // Account Routes
    Route::controller(AccountController::class)->prefix('admin/accounts')->group(function () {
        Route::get('', 'getAccountPaginated');
        Route::get('/selectable', 'getSelectableAccounts');
        Route::post('/status', 'updateAccountActiveState');
    });
    // Statistic Routes
    Route::controller(AccountController::class)->prefix('admin/statistics')->group(function () {
        Route::get('/renew-cancel', [StatisticController::class, 'getRenewCancelStatisticByPeriod']);
        Route::get('/revenue', [StatisticController::class, 'getRevenueStatisticByPeriod']);
    });

    // Momo Routes
    Route::post('momo/pay', [MomoController::class, 'pay']);
});

Route::post('momo/callback/{id}', [MomoController::class, 'notify']);

Route::post('/provider/youtube/inquiry', [YoutubeProviderController::class, 'inquiry']);
Route::post('/provider/netflix/inquiry', [NetflixProviderController::class, 'inquiry']);
