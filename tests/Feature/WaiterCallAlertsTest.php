<?php

namespace Tests\Feature;

use App\Enums\HotelStatus;
use App\Enums\UserRole;
use App\Models\Hotel;
use App\Models\MenuCategory;
use App\Models\Table;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WaiterCallAlertsTest extends TestCase
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

    private function startCustomerSession(Hotel $hotel, Table $table): array
    {
        $response = $this->get(route('customer.scan', ['hotel' => $hotel->slug, 'table_uuid' => $table->table_uuid]));
        $cookies = [];
        foreach ($response->headers->getCookies() as $cookie) {
            $cookies[$cookie->getName()] = $cookie->getValue();
        }

        return $cookies;
    }

    public function test_customer_calling_a_waiter_creates_a_real_row_and_the_waiter_sees_it_via_polling(): void
    {
        $hotel = Hotel::factory()->create(['status' => HotelStatus::Active]);
        MenuCategory::create(['hotel_id' => $hotel->id, 'name' => 'Starters', 'type' => 'food', 'display_order' => 1]);
        $table = Table::factory()->create(['hotel_id' => $hotel->id, 'table_number' => 1]);
        $waiter = $this->makeStaff(UserRole::Waiter, $hotel);

        $cookies = $this->startCustomerSession($hotel, $table);

        $this->withUnencryptedCookies($cookies)->withCredentials()
            ->postJson(route('customer.call-waiter'), ['note' => 'Need napkins'])
            ->assertOk();

        $this->assertDatabaseHas('waiter_calls', ['table_id' => $table->id, 'status' => 'pending', 'note' => 'Need napkins']);

        $pending = $this->actingAs($waiter)->getJson(route('waiter.calls.pending'));
        $pending->assertOk();
        $this->assertEquals(1, $pending->json('count'));
        $latestId = $pending->json('latestId');
        $this->assertNotNull($latestId);

        $this->actingAs($waiter)->post(route('waiter.calls.attend', $latestId))->assertRedirect();

        $this->assertDatabaseHas('waiter_calls', ['id' => $latestId, 'status' => 'attended']);
        $this->actingAs($waiter)->getJson(route('waiter.calls.pending'))->assertJson(['count' => 0]);
    }

    public function test_waiter_from_a_different_hotel_does_not_see_or_attend_this_hotels_call(): void
    {
        $hotelA = Hotel::factory()->create(['status' => HotelStatus::Active]);
        $hotelB = Hotel::factory()->create(['status' => HotelStatus::Active]);
        MenuCategory::create(['hotel_id' => $hotelA->id, 'name' => 'Starters', 'type' => 'food', 'display_order' => 1]);
        $tableA = Table::factory()->create(['hotel_id' => $hotelA->id, 'table_number' => 1]);
        $waiterB = $this->makeStaff(UserRole::Waiter, $hotelB);

        $cookies = $this->startCustomerSession($hotelA, $tableA);
        $this->withUnencryptedCookies($cookies)->withCredentials()->postJson(route('customer.call-waiter'), [])->assertOk();

        $pending = $this->actingAs($waiterB)->getJson(route('waiter.calls.pending'));
        $pending->assertJson(['count' => 0]);

        $call = \App\Models\WaiterCall::firstOrFail();
        $this->actingAs($waiterB)->post(route('waiter.calls.attend', $call))->assertForbidden();
    }
}
