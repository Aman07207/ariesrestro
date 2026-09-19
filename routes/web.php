<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Customer\BillController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\MenuController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Customer\PaymentController;
use App\Http\Controllers\Customer\SessionController;
use App\Http\Controllers\Customer\WaiterCallController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/demo-requests', [HomeController::class, 'submitDemoRequest'])
    ->middleware('throttle:5,1')
    ->name('demo-requests.store');

Route::get('/login', [AuthController::class, 'show'])->name('login');
Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('throttle:5,1')->group(function () {
    Route::get('/forgot-password', [ForgotPasswordController::class, 'show'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'send'])->name('password.email');
    Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update');
});

// Public, no auth — customers are identified by order_sessions.session_token, not a login.
Route::prefix('order')->name('customer.')->group(function () {
    Route::get('/scan-help', [SessionController::class, 'help'])->name('scan-help');

    // The real QR-scan entry point. Rate-limited: stops bots mass-creating fake sessions.
    Route::get('/{hotel:slug}/{table_uuid}', [SessionController::class, 'start'])
        ->middleware('throttle:20,1')
        ->name('scan');

    Route::middleware('resolve.session')->group(function () {
        Route::get('/menu', [MenuController::class, 'index'])->name('menu');
        Route::get('/cart', [CartController::class, 'show'])->name('cart');
        Route::get('/track', [OrderController::class, 'track'])->name('track');
        Route::post('/place', [OrderController::class, 'place'])->middleware('throttle:10,1')->name('order.place');
        Route::get('/bill', [BillController::class, 'show'])->name('bill');
        Route::post('/bill/service-charge', [BillController::class, 'updateServiceCharge'])->name('bill.service-charge');
        Route::get('/pay', [PaymentController::class, 'show'])->name('pay');
        Route::post('/pay/confirm', [PaymentController::class, 'confirm'])->name('pay.confirm');
        Route::get('/success', [PaymentController::class, 'success'])->name('success');

        // Rate-limited: stops spam-tapping.
        Route::post('/call-waiter', [WaiterCallController::class, 'store'])
            ->middleware('throttle:3,1')
            ->name('call-waiter');
    });
});
