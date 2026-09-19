<?php

use App\Http\Controllers\SuperAdmin\ActivityLogController;
use App\Http\Controllers\SuperAdmin\CustomerSessionController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\DemoRequestController;
use App\Http\Controllers\SuperAdmin\HotelController;
use App\Http\Controllers\SuperAdmin\PaymentSettingController;
use App\Http\Controllers\SuperAdmin\PlatformTableController;
use App\Http\Controllers\SuperAdmin\SalesController;
use App\Http\Controllers\SuperAdmin\SubscriptionController;
use App\Http\Controllers\SuperAdmin\TableQrController;
use Illuminate\Support\Facades\Route;

Route::prefix('super-admin')->name('superadmin.')->middleware(['auth', 'role:super_admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('hotels', HotelController::class)->except(['show']);
    Route::resource('subscriptions', SubscriptionController::class)->except(['show']);
    Route::resource('payment-settings', PaymentSettingController::class)->except(['show'])->parameters(['payment-settings' => 'payment_setting']);
    Route::get('/activity', [ActivityLogController::class, 'index'])->name('activity.index');
    Route::get('/demo-requests', [DemoRequestController::class, 'index'])->name('demo-requests.index');
    Route::post('/demo-requests/{demoRequest}/contacted', [DemoRequestController::class, 'markContacted'])->name('demo-requests.contacted');

    // Platform-wide, read-only reporting across every hotel. Tables is "pick a hotel,
    // then see its tables" rather than one flat list — that stops scaling past a
    // handful of hotels; a searchable hotel picker doesn't.
    Route::get('/tables', [PlatformTableController::class, 'index'])->name('tables.index');
    Route::get('/hotels/{hotel}/tables', [PlatformTableController::class, 'show'])->name('hotels.tables');
    Route::get('/sales', [SalesController::class, 'index'])->name('sales.index');
    Route::get('/customers', [CustomerSessionController::class, 'index'])->name('customers.index');

    // QR generation for any hotel's table (Hotel Admin gets the same under /hotel-admin,
    // scoped to their own hotel — this is the unscoped, any-hotel version).
    Route::get('/tables/{table}/qr', [TableQrController::class, 'show'])->name('tables.qr');
    Route::get('/tables/{table}/qr-image', [TableQrController::class, 'image'])->name('tables.qr-image');
});
