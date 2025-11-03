<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\NetflixProviderController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\StatisticController;
use App\Http\Controllers\Api\SubscriptionController;
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
    Route::get('services/filter/base', [ServiceController::class, 'getBase']);

    // Subscription Routes
    Route::apiResource('subscriptions', SubscriptionController::class);

    // Account Routes
    Route::get('admin/accounts', [AccountController::class, 'getAccountWithSubscription']);

    // Statistic Routes
    Route::get('admin/statistics/renew-cancel', [StatisticController::class, 'getRenewCancelStatistic']);
});

Route::get("/provider/youtube/inquiry/{customerCode}", [YoutubeProviderController::class, "inquiry"]);
Route::get("/provider/netflix/inquiry/{customerCode}", [NetflixProviderController::class, "inquiry"]);



