<?php

namespace Database\Factories;

use App\Enums\TableStatus;
use App\Models\Hotel;
use App\Models\Table;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Table>
 */
class TableFactory extends Factory
{
    protected $model = Table::class;

    public function definition(): array
    {
        return [
            'hotel_id' => Hotel::factory(),
            'table_number' => fake()->unique()->numberBetween(1, 500),
            'seating_capacity' => 4,
            'status' => TableStatus::Available,
        ];
    }
}
