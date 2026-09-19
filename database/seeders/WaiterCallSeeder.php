<?php

namespace Database\Seeders;

use App\Enums\SessionStatus;
use App\Enums\WaiterCallStatus;
use App\Models\Hotel;
use App\Models\OrderSession;
use App\Models\Table;
use App\Models\WaiterCall;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WaiterCallSeeder extends Seeder
{
    public function run(): void
    {
        $hotel = Hotel::where('slug', 'aries-cafe')->firstOrFail();

        foreach ([3, 7] as $tableNumber) {
            $table = Table::where('hotel_id', $hotel->id)->where('table_number', $tableNumber)->firstOrFail();

            $session = OrderSession::firstOrCreate(
                ['hotel_id' => $hotel->id, 'table_id' => $table->id, 'status' => SessionStatus::Active],
                ['session_token' => (string) Str::uuid(), 'member_count' => 1]
            );

            $call = WaiterCall::updateOrCreate(
                ['session_id' => $session->id, 'table_id' => $table->id, 'status' => WaiterCallStatus::Pending],
                []
            );
            $call->forceFill(['created_at' => now()->subMinutes($tableNumber === 3 ? 4 : 6)])->save();
        }
    }
}
