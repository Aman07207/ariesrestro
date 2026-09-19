<?php

use App\Http\Controllers\Chef\ProfileController;
use App\Http\Controllers\Chef\QueueController;
use App\Http\Controllers\Chef\StockController;
use Illuminate\Support\Facades\Route;

Route::prefix('chef')->name('chef.')->middleware(['auth', 'role:chef'])->group(function () {
    Route::get('/queue', [QueueController::class, 'index'])->name('queue');
    Route::get('/stock', [StockController::class, 'index'])->name('stock');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
});
