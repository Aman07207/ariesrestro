<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index()
    {
        return view('superadmin.activity.index', [
            'activities' => Activity::with('causer', 'subject')->latest()->paginate(25),
        ]);
    }
}
