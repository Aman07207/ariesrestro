<?php

namespace Database\Seeders;

use App\Enums\TableStatus;
use App\Models\Hotel;
use App\Models\Table;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TableSeeder extends Seeder
{
    public function run(): void
    {
        $hotel = Hotel::where('slug', 'aries-cafe')->firstOrFail();

        for ($number = 1; $number <= 8; $number++) {
            Table::updateOrCreate(
                ['hotel_id' => $hotel->id, 'table_number' => $number],
                [
                    'table_uuid' => (string) Str::uuid(),
                    'seating_capacity' => 4,
                    'status' => TableStatus::Available,
                ]
            );
        }
    }
}
