<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Hotel;
use App\Models\Table;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    private function makeStaff(UserRole $role, ?Hotel $hotel = null): User
    {
        $user = User::factory()->create([
            'role' => $role,
            'hotel_id' => $hotel?->id,
            'employee_id' => strtoupper($role->value).'-'.fake()->unique()->numberBetween(1000, 9999),
        ]);
        $user->assignRole($role->value);

        return $user;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_super_admin_reaches_the_platform_dashboard(): void
    {
        $superAdmin = $this->makeStaff(UserRole::SuperAdmin);

        $this->actingAs($superAdmin)->get(route('superadmin.dashboard'))->assertOk();
        $this->actingAs($superAdmin)->get(route('superadmin.hotels.index'))->assertOk();
    }

    public function test_hotel_admin_cannot_reach_super_admin_routes(): void
    {
        $hotel = Hotel::factory()->create();
        $hotelAdmin = $this->makeStaff(UserRole::HotelAdmin, $hotel);

        $this->actingAs($hotelAdmin)->get(route('superadmin.dashboard'))->assertForbidden();
    }

    public function test_waiter_cannot_reach_chef_routes(): void
    {
        $hotel = Hotel::factory()->create();
        $waiter = $this->makeStaff(UserRole::Waiter, $hotel);

        $this->actingAs($waiter)->get(route('chef.queue'))->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('hoteladmin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_hotel_admin_only_sees_their_own_hotels_tables(): void
    {
        $hotelA = Hotel::factory()->create();
        $hotelB = Hotel::factory()->create();
        $adminA = $this->makeStaff(UserRole::HotelAdmin, $hotelA);

        Table::factory()->create(['hotel_id' => $hotelA->id, 'table_number' => 1]);
        Table::factory()->create(['hotel_id' => $hotelB->id, 'table_number' => 1]);

        $response = $this->actingAs($adminA)->get(route('hoteladmin.tables.index'));

        $response->assertOk();
        $this->assertCount(1, $response->viewData('tables'));
    }

    public function test_hotel_admin_cannot_edit_another_hotels_staff_member_by_id(): void
    {
        $hotelA = Hotel::factory()->create();
        $hotelB = Hotel::factory()->create();
        $adminA = $this->makeStaff(UserRole::HotelAdmin, $hotelA);
        $waiterB = $this->makeStaff(UserRole::Waiter, $hotelB);

        $this->actingAs($adminA)->get(route('hoteladmin.staff.edit', $waiterB))->assertForbidden();
    }
}
