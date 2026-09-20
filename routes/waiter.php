<?php

use App\Http\Controllers\Waiter\CallController;
use App\Http\Controllers\Waiter\ManualOrderController;
use App\Http\Controllers\Waiter\OrderItemController;
use App\Http\Controllers\Waiter\ProfileController;
use App\Http\Controllers\Waiter\TableController;
use Illuminate\Support\Facades\Route;

Route::prefix('waiter')->name('waiter.')->middleware(['auth', 'role:waiter'])->group(function () {
    Route::get('/tables', [TableController::class, 'index'])->name('tables');
    Route::get('/tables/{table}', [TableController::class, 'show'])->name('tables.show');
    Route::post('/order-items/{orderItem}/cancel', [OrderItemController::class, 'cancel'])->name('order-items.cancel');
    Route::get('/manual-order', [ManualOrderController::class, 'create'])->name('manual-order');
    Route::post('/manual-order', [ManualOrderController::class, 'store'])->name('manual-order.store');
    Route::get('/calls', [CallController::class, 'index'])->name('calls');
    Route::post('/calls/{call}/attend', [CallController::class, 'attend'])->name('calls.attend');
    Route::get('/calls/pending', [CallController::class, 'pending'])->name('calls.pending');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
});
