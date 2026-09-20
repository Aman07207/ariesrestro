<?php

use App\Http\Controllers\Chef\ProfileController;
use App\Http\Controllers\Chef\QueueController;
use App\Http\Controllers\Chef\StockController;
use Illuminate\Support\Facades\Route;

Route::prefix('chef')->name('chef.')->middleware(['auth', 'role:chef'])->group(function () {
    Route::get('/queue', [QueueController::class, 'index'])->name('queue');
    Route::post('/queue/{orderItem}/advance', [QueueController::class, 'advance'])->name('queue.advance');
    Route::get('/stock', [StockController::class, 'index'])->name('stock');
    Route::post('/stock/{menuItem}/toggle', [StockController::class, 'toggle'])->name('stock.toggle');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
});
