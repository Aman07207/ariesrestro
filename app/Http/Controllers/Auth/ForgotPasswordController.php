<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Services\Auth\AuthService;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function __construct(private readonly AuthService $auth)
    {
    }

    public function show()
    {
        return view('auth.forgot-password');
    }

    public function send(ForgotPasswordRequest $request)
    {
        $message = $this->auth->sendPasswordResetLink($request->validated('identifier'));

        return back()->with('status', $message);
    }

    public function showReset(string $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => request('email')]);
    }

    public function reset(ResetPasswordRequest $request)
    {
        $status = $this->auth->resetPassword($request->validated());

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withErrors(['email' => __($status)]);
        }

        return redirect()->route('login')->with('status', 'Password reset — you can log in now.');
    }
}
