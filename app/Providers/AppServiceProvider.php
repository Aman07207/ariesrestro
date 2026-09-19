<?php

namespace App\Providers;

use App\Enums\WaiterCallStatus;
use App\Models\WaiterCall;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('partials.bottomnav-waiter', function ($view) {
            $view->with('pendingCallCount', WaiterCall::where('status', WaiterCallStatus::Pending)->count());
        });
    }
}
