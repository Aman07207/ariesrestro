<?php

namespace App\Services\HotelAdmin;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class StaffService
{
    public function create(array $data): User
    {
        $data['hotel_id'] = Auth::user()->hotel_id;
        $data['email_verified_at'] = now();

        $user = User::create($data);
        $user->syncRoles([$data['role']]);

        return $user;
    }

    public function update(User $staff, array $data): User
    {
        $this->authorizeSameHotel($staff);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $staff->update($data);
        $staff->syncRoles([$data['role']]);

        return $staff;
    }

    public function delete(User $staff): void
    {
        $this->authorizeSameHotel($staff);

        $staff->delete();
    }

    public function ensureSameHotel(User $staff): void
    {
        $this->authorizeSameHotel($staff);
    }

    /**
     * User doesn't carry the BelongsToHotel global scope (it's staff identity, not
     * tenant data), so route-model binding alone won't stop a hotel admin reaching
     * another hotel's staff by ID — enforce it explicitly here.
     */
    private function authorizeSameHotel(User $staff): void
    {
        abort_unless($staff->hotel_id === Auth::user()->hotel_id, 403);
    }
}
