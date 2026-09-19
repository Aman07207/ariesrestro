<?php

namespace Tests\Feature;

use App\Enums\HotelStatus;
use App\Enums\UserRole;
use App\Enums\WaiterCallStatus;
use App\Livewire\Waiter\CallsBadge;
use App\Livewire\Waiter\CallsIndex;
use App\Models\Hotel;
use App\Models\OrderSession;
use App\Models\Table;
use App\Models\User;
use App\Models\WaiterCall;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WaiterCallsLivewireTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function makeStaff(UserRole $role, Hotel $hotel): User
    {
        $user = User::factory()->create([
            'role' => $role,
            'hotel_id' => $hotel->id,
            'employee_id' => strtoupper($role->value).'-'.fake()->unique()->numberBetween(1000, 9999),
        ]);
        $user->assignRole($role->value);

        return $user;
    }

    private function makeCall(Table $table, WaiterCallStatus $status): WaiterCall
    {
        $session = OrderSession::create(['hotel_id' => $table->hotel_id, 'table_id' => $table->id]);

        return WaiterCall::create(['session_id' => $session->id, 'table_id' => $table->id, 'status' => $status]);
    }

    public function test_waiter_pages_render_with_the_livewire_components_mounted(): void
    {
        $hotel = Hotel::factory()->create(['status' => HotelStatus::Active]);
        $waiter = $this->makeStaff(UserRole::Waiter, $hotel);

        $this->actingAs($waiter)->get(route('waiter.tables'))->assertOk();
        $this->actingAs($waiter)->get(route('waiter.calls'))->assertOk();
    }

    public function test_calls_index_lists_only_this_hotels_calls_and_can_attend(): void
    {
        $hotel = Hotel::factory()->create(['status' => HotelStatus::Active]);
        $otherHotel = Hotel::factory()->create(['status' => HotelStatus::Active]);
        $table = Table::factory()->create(['hotel_id' => $hotel->id]);
        $otherTable = Table::factory()->create(['hotel_id' => $otherHotel->id]);
        $waiter = $this->makeStaff(UserRole::Waiter, $hotel);

        $call = $this->makeCall($table, WaiterCallStatus::Pending);
        $this->makeCall($otherTable, WaiterCallStatus::Pending);

        Livewire::actingAs($waiter)
            ->test(CallsIndex::class)
            ->assertSee('Table '.$table->table_number)
            ->assertViewHas('calls', fn ($calls) => $calls->count() === 1 && $calls->first()->id === $call->id)
            ->call('attend', $call->id);

        $this->assertSame(WaiterCallStatus::Attended, $call->fresh()->status);
    }

    public function test_calls_index_rejects_attending_a_call_from_another_hotel(): void
    {
        $hotel = Hotel::factory()->create(['status' => HotelStatus::Active]);
        $otherHotel = Hotel::factory()->create(['status' => HotelStatus::Active]);
        $otherTable = Table::factory()->create(['hotel_id' => $otherHotel->id]);
        $waiter = $this->makeStaff(UserRole::Waiter, $hotel);

        $call = $this->makeCall($otherTable, WaiterCallStatus::Pending);

        Livewire::actingAs($waiter)
            ->test(CallsIndex::class)
            ->call('attend', $call->id)
            ->assertForbidden();
    }

    public function test_badge_counts_only_pending_calls_for_the_waiters_own_hotel(): void
    {
        $hotel = Hotel::factory()->create(['status' => HotelStatus::Active]);
        $otherHotel = Hotel::factory()->create(['status' => HotelStatus::Active]);
        $table = Table::factory()->create(['hotel_id' => $hotel->id]);
        $otherTable = Table::factory()->create(['hotel_id' => $otherHotel->id]);
        $waiter = $this->makeStaff(UserRole::Waiter, $hotel);

        $this->makeCall($table, WaiterCallStatus::Pending);
        $this->makeCall($table, WaiterCallStatus::Attended);
        $this->makeCall($otherTable, WaiterCallStatus::Pending);

        Livewire::actingAs($waiter)
            ->test(CallsBadge::class)
            ->assertViewHas('count', 1);
    }
}
