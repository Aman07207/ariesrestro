<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Services\Customer\CustomerSessionService;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function __construct(private readonly CustomerSessionService $sessions)
    {
    }

    public function help()
    {
        return view('customer.scan', [
            'heading' => session('scan_reason') ? 'Session ended' : null,
            'message' => session('scan_reason'),
        ]);
    }

    /**
     * The real QR-scan entry point: /order/{hotel:slug}/{table_uuid}.
     */
    public function start(Request $request, Hotel $hotel, string $table_uuid)
    {
        $this->sessions->startSession($request, $hotel, $table_uuid);

        return redirect()->route('customer.menu');
    }
}
