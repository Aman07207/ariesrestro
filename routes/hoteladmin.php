<?php

use App\Http\Controllers\HotelAdmin\DashboardController;
use App\Http\Controllers\HotelAdmin\MenuCategoryController;
use App\Http\Controllers\HotelAdmin\MenuItemController;
use App\Http\Controllers\HotelAdmin\ProfileController;
use App\Http\Controllers\HotelAdmin\StaffController;
use App\Http\Controllers\HotelAdmin\TableController;
use App\Http\Controllers\HotelAdmin\TableQrController;
use App\Http\Controllers\HotelAdmin\TaxSettingController;
use Illuminate\Support\Facades\Route;

Route::prefix('hotel-admin')->name('hoteladmin.')->middleware(['auth', 'role:hotel_admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/tax-settings', [TaxSettingController::class, 'show'])->name('tax-settings');
    Route::put('/tax-settings', [TaxSettingController::class, 'update'])->name('tax-settings.update');
    Route::resource('tables', TableController::class)->except(['show']);
    Route::get('/tables/{table}/qr', [TableQrController::class, 'show'])->name('tables.qr');
    Route::get('/tables/{table}/qr-image', [TableQrController::class, 'image'])->name('tables.qr-image');
    Route::resource('menu-categories', MenuCategoryController::class)->except(['show']);
    Route::resource('menu-items', MenuItemController::class)->except(['show']);
    Route::resource('staff', StaffController::class)->except(['show']);
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});
