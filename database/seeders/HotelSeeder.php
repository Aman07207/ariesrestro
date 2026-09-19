<?php

namespace Database\Seeders;

use App\Enums\HotelStatus;
use App\Models\Hotel;
use Illuminate\Database\Seeder;

class HotelSeeder extends Seeder
{
    public function run(): void
    {
        Hotel::updateOrCreate(
            ['slug' => 'aries-cafe'],
            [
                'name' => 'Aries Café',
                'owner_name' => 'Aries Innovation',
                'owner_email' => 'tech.ariesinnovation@gmail.com',
                'address' => 'Ahmedabad, Gujarat, India',
                'subscription_plan' => 'standard',
                'status' => HotelStatus::Active,
                // Matches the billing-engine spec's own worked example exactly, so the
                // ₹1,080 figure is reproducible by hand in the live app, not just in tests.
                'gst_rate' => 5,
                'vat_rate' => 20,
                'is_luxury_hotel' => false,
                'default_service_charge_percent' => 10,
            ]
        );
    }
}
