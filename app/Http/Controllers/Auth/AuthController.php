<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\AuthService;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $auth)
    {
    }

    public function show()
    {
        if (Auth::check()) {
            return redirect($this->auth->redirectPathFor(Auth::user()));
        }

        return view('auth.login');
    }

    public function store(LoginRequest $request)
    {
        $user = $this->auth->attempt($request->validated('identifier'), $request->validated('password'));

        $request->session()->regenerate();

        return redirect()->intended($this->auth->redirectPathFor($user));
    }

    public function destroy()
    {
        $this->auth->logout();

        return redirect()->route('login');
    }
}
