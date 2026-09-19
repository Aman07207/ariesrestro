<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\DemoRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\DemoRequest;

class DemoRequestController extends Controller
{
    public function index()
    {
        return view('superadmin.demo-requests.index', [
            'requests' => DemoRequest::latest()->paginate(20),
        ]);
    }

    public function markContacted(DemoRequest $demoRequest)
    {
        $demoRequest->update(['status' => DemoRequestStatus::Contacted]);

        return back()->with('status', 'Marked as contacted.');
    }
}
