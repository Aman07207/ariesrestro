<?php

namespace App\Http\Controllers\HotelAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\HotelAdmin\UpdateProfileRequest;
use App\Services\HotelAdmin\ProfileService;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function __construct(private readonly ProfileService $profile)
    {
    }

    public function show()
    {
        return view('hoteladmin.profile', ['admin' => Auth::user()->load('hotel')]);
    }

    public function update(UpdateProfileRequest $request)
    {
        $this->profile->update(Auth::user(), $request->validated());

        return redirect()->route('hoteladmin.profile')->with('status', 'Profile updated.');
    }
}
