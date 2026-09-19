<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'tech.ariesinnovation@gmail.com'],
            [
                'name' => 'Aries Innovation Admin',
                'password' => 'ChangeMe@123', // dev-only seed password — rotate before any real deployment
                'hotel_id' => null,
                'employee_id' => null,
                'role' => UserRole::SuperAdmin,
                'email_verified_at' => now(),
            ]
        );

        $user->syncRoles([UserRole::SuperAdmin->value]);
    }
}
