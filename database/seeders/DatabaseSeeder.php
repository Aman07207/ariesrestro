<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with the structural, real-world starting
     * state only — hotel, tables (all available), menu, staff, one subscription.
     * No pre-existing orders/sessions/calls, so a fresh install is ready for
     * genuine end-to-end testing (scan a real QR, place a real order, etc.)
     * rather than always showing pre-populated demo activity.
     *
     * For the "looks like the finalized UI screenshots" showcase state instead,
     * run: php artisan db:seed --class=DemoShowcaseSeeder
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SuperAdminSeeder::class,
            HotelSeeder::class,
            TableSeeder::class,
            MenuSeeder::class,
            StaffSeeder::class,
            SubscriptionSeeder::class,
        ]);
    }
}
