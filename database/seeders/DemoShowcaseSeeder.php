<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Optional, on-demand seeder that reproduces the finalized-UI demo state:
 * Table 5 mid-order, Tables 2/3/7 with kitchen activity, two pending waiter
 * calls. Not run by default (see DatabaseSeeder) — run explicitly with:
 * php artisan db:seed --class=DemoShowcaseSeeder
 * Requires the base DatabaseSeeder to have already run (hotel/tables/menu must exist).
 */
class DemoShowcaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DemoOrderSessionSeeder::class,
            WaiterCallSeeder::class,
            DemoKitchenActivitySeeder::class,
        ]);
    }
}
