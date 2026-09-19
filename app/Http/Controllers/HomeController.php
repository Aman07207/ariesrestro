<?php

namespace App\Http\Controllers;

use App\Http\Requests\DemoRequestRequest;
use App\Services\DemoRequestService;

class HomeController extends Controller
{
    public function __construct(private readonly DemoRequestService $demoRequests)
    {
    }

    public function index()
    {
        return view('home.index');
    }

    public function submitDemoRequest(DemoRequestRequest $request)
    {
        $this->demoRequests->create($request->validated());

        return back()->with('status', "Thanks! We'll be in touch shortly to set up your demo.");
    }
}
