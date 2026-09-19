<?php

namespace App\Services\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Roles managed entirely by their Hotel Admin — password resets for these accounts
     * happen via Hotel Admin > Staff > Edit, not self-service, so no reset email is sent.
     */
    private const HOTEL_MANAGED_ROLES = [UserRole::Waiter, UserRole::Chef, UserRole::Manager];

    public function attempt(string $identifier, string $password): User
    {
        $user = User::where('email', $identifier)->orWhere('employee_id', $identifier)->first();

        if (! $user || ! Auth::attempt(['email' => $user->email, 'password' => $password])) {
            throw ValidationException::withMessages([
                'identifier' => 'These credentials do not match our records.',
            ]);
        }

        return $user;
    }

    public function logout(): void
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }

    public function redirectPathFor(User $user): string
    {
        return match ($user->role) {
            UserRole::SuperAdmin => route('superadmin.dashboard'),
            UserRole::HotelAdmin => route('hoteladmin.dashboard'),
            UserRole::Waiter => route('waiter.tables'),
            UserRole::Chef => route('chef.queue'),
            UserRole::Manager => route('hoteladmin.dashboard'),
        };
    }

    /**
     * Always returns a generic, user-facing message — never reveals whether the
     * identifier matched an account (no user enumeration).
     */
    public function sendPasswordResetLink(string $identifier): string
    {
        $user = User::where('email', $identifier)->orWhere('employee_id', $identifier)->first();

        if (! $user) {
            return 'If an account matches those details, a password reset link has been sent.';
        }

        if (in_array($user->role, self::HOTEL_MANAGED_ROLES, true)) {
            return 'Waiter/Chef/Manager passwords are reset by your Hotel Admin — ask them to update it from Staff > Edit.';
        }

        Password::sendResetLink(['email' => $user->email]);

        return 'If an account matches those details, a password reset link has been sent.';
    }

    /**
     * @return string Password::PASSWORD_RESET on success, otherwise a broker status string.
     */
    public function resetPassword(array $data): string
    {
        return Password::reset(
            $data,
            function (User $user, string $password) {
                $user->update(['password' => $password]);
            }
        );
    }
}
