<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\HotelStatus;
use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Subscription;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function index()
    {
        return view('superadmin.dashboard', [
            'totalHotels' => Hotel::count(),
            'activeHotels' => Hotel::where('status', HotelStatus::Active)->count(),
            'activeSubscriptions' => Subscription::where('status', SubscriptionStatus::Active)->count(),
            'totalStaff' => User::whereNotNull('hotel_id')->count(),
            'recentActivity' => Activity::latest()->take(8)->get(),
        ]);
    }
}
