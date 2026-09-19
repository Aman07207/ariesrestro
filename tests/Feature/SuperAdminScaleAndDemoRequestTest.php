<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Hotel;
use App\Models\Order;
use App\Models\OrderSession;
use App\Models\Table;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminScaleAndDemoRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function makeSuperAdmin(): User
    {
        $user = User::factory()->create(['role' => UserRole::SuperAdmin, 'hotel_id' => null, 'employee_id' => 'SUP-'.fake()->unique()->numberBetween(1000, 9999)]);
        $user->assignRole(UserRole::SuperAdmin->value);

        return $user;
    }

    public function test_super_admin_hotel_picker_only_shows_that_hotels_tables(): void
    {
        $superAdmin = $this->makeSuperAdmin();
        $hotelA = Hotel::factory()->create(['name' => 'Hotel A']);
        $hotelB = Hotel::factory()->create(['name' => 'Hotel B']);
        Table::factory()->create(['hotel_id' => $hotelA->id, 'table_number' => 1]);
        Table::factory()->create(['hotel_id' => $hotelB->id, 'table_number' => 1]);
        Table::factory()->create(['hotel_id' => $hotelB->id, 'table_number' => 2]);

        $this->actingAs($superAdmin)->get(route('superadmin.tables.index'))
            ->assertOk()->assertSee('Hotel A')->assertSee('Hotel B');

        $response = $this->actingAs($superAdmin)->get(route('superadmin.hotels.tables', $hotelB));
        $response->assertOk();
        $this->assertCount(2, $response->viewData('tables'));
    }

    public function test_sales_and_customers_hotel_filter_narrows_results(): void
    {
        $superAdmin = $this->makeSuperAdmin();
        $hotelA = Hotel::factory()->create(['name' => 'Hotel A']);
        $hotelB = Hotel::factory()->create(['name' => 'Hotel B']);
        $tableA = Table::factory()->create(['hotel_id' => $hotelA->id, 'table_number' => 1]);
        $tableB = Table::factory()->create(['hotel_id' => $hotelB->id, 'table_number' => 1]);
        $sessionA = OrderSession::create(['hotel_id' => $hotelA->id, 'table_id' => $tableA->id, 'session_token' => (string) \Illuminate\Support\Str::uuid(), 'member_count' => 1, 'status' => 'active']);
        $sessionB = OrderSession::create(['hotel_id' => $hotelB->id, 'table_id' => $tableB->id, 'session_token' => (string) \Illuminate\Support\Str::uuid(), 'member_count' => 1, 'status' => 'active']);

        Order::create(['table_id' => $tableA->id, 'session_id' => $sessionA->id, 'member_no' => 1, 'order_number' => 'A-1', 'status' => 'pending', 'total_amount' => 100]);
        Order::create(['table_id' => $tableB->id, 'session_id' => $sessionB->id, 'member_no' => 1, 'order_number' => 'B-1', 'status' => 'pending', 'total_amount' => 200]);

        $filtered = $this->actingAs($superAdmin)->get(route('superadmin.sales.index', ['hotel' => $hotelA->id]));
        $filtered->assertOk();
        $this->assertCount(1, $filtered->viewData('orders'));
    }

    public function test_demo_request_submission_creates_a_row_and_appears_in_super_admin_inbox(): void
    {
        $this->post(route('demo-requests.store'), [
            'name' => 'Priya Shah',
            'hotel_name' => 'Sunrise Café',
            'phone' => '9999999999',
            'email' => 'priya@sunrise.test',
            'message' => 'Interested in the standard plan',
        ])->assertRedirect();

        $this->assertDatabaseHas('demo_requests', ['email' => 'priya@sunrise.test', 'status' => 'new']);

        $superAdmin = $this->makeSuperAdmin();
        $this->actingAs($superAdmin)->get(route('superadmin.demo-requests.index'))
            ->assertOk()->assertSee('Priya Shah')->assertSee('Sunrise Café');
    }

    public function test_non_super_admin_cannot_reach_the_demo_requests_inbox(): void
    {
        $hotel = Hotel::factory()->create();
        $admin = User::factory()->create(['role' => UserRole::HotelAdmin, 'hotel_id' => $hotel->id, 'employee_id' => 'ADM-1']);
        $admin->assignRole(UserRole::HotelAdmin->value);

        $this->actingAs($admin)->get(route('superadmin.demo-requests.index'))->assertForbidden();
    }
}
