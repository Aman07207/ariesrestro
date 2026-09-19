<?php

namespace Database\Seeders;

use App\Enums\SubscriptionStatus;
use App\Models\Hotel;
use App\Models\Subscription;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $hotel = Hotel::where('slug', 'aries-cafe')->firstOrFail();

        Subscription::updateOrCreate(
            ['hotel_id' => $hotel->id, 'plan' => 'standard'],
            [
                'billing_cycle' => 'yearly',
                'price' => 12000,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addYear()->toDateString(),
                'status' => SubscriptionStatus::Active,
            ]
        );
    }
}
