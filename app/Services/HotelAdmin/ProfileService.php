<?php

namespace App\Services\HotelAdmin;

use App\Models\User;

class ProfileService
{
    public function update(User $admin, array $data): User
    {
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $admin->update($data);

        return $admin;
    }
}
