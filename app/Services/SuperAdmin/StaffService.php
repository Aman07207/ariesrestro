<?php

namespace App\Services\SuperAdmin;

use App\Models\User;

/**
 * Platform-level staff management: unlike Hotel Admin's StaffService there is no
 * "same hotel" restriction — Super Admin manages any hotel's accounts.
 */
class StaffService
{
    public function create(array $data): User
    {
        $data['email_verified_at'] = now();

        $user = User::create($data);
        $user->syncRoles([$data['role']]);

        return $user;
    }

    public function update(User $staff, array $data): User
    {
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $staff->update($data);
        $staff->syncRoles([$data['role']]);

        return $staff;
    }

    public function delete(User $staff): void
    {
        $staff->delete();
    }
}
