<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Database\Seeder;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $hotel = Hotel::where('slug', 'aries-cafe')->firstOrFail();

        $hotelAdmin = User::updateOrCreate(
            ['email' => 'admin@ariescafe.test'],
            [
                'name' => 'Priya Mehta',
                'password' => 'ChangeMe@123', // dev-only seed password
                'hotel_id' => $hotel->id,
                'employee_id' => 'ADM-0001',
                'role' => UserRole::HotelAdmin,
                'phone' => '+91 90000 11122',
                'section' => null,
                'shift' => null,
                'email_verified_at' => now(),
            ]
        );
        $hotelAdmin->syncRoles([UserRole::HotelAdmin->value]);

        $waiter = User::updateOrCreate(
            ['email' => 'rahul.sharma@ariesrestro.test'],
            [
                'name' => 'Rahul Sharma',
                'password' => 'ChangeMe@123', // dev-only seed password
                'hotel_id' => $hotel->id,
                'employee_id' => 'WTR-1042',
                'role' => UserRole::Waiter,
                'phone' => '+91 98765 43210',
                'section' => 'Ground floor · Tables 1-8',
                'shift' => '10 AM-8 PM',
                'email_verified_at' => now(),
            ]
        );
        $waiter->syncRoles([UserRole::Waiter->value]);

        $chef = User::updateOrCreate(
            ['email' => 'anita.verma@ariesrestro.test'],
            [
                'name' => 'Anita Verma',
                'password' => 'ChangeMe@123', // dev-only seed password
                'hotel_id' => $hotel->id,
                'employee_id' => 'CHF-0231',
                'role' => UserRole::Chef,
                'phone' => '+91 91234 56789',
                'section' => 'Main kitchen',
                'shift' => '11 AM-9 PM',
                'email_verified_at' => now(),
            ]
        );
        $chef->syncRoles([UserRole::Chef->value]);
    }
}
