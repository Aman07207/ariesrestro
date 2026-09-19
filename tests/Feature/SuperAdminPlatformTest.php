<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Hotel;
use App\Models\Table;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminPlatformTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_super_admin_sees_tables_sales_and_customers_across_hotels_they_did_not_create(): void
    {
        $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin, 'hotel_id' => null, 'employee_id' => 'SUP-1']);
        $superAdmin->assignRole(UserRole::SuperAdmin->value);

        // A hotel the Super Admin had no hand in creating in this test.
        $otherHotel = Hotel::factory()->create(['name' => 'Someone Else\'s Diner']);
        $otherTable = Table::factory()->create(['hotel_id' => $otherHotel->id, 'table_number' => 3]);

        $this->actingAs($superAdmin)->get(route('superadmin.tables.index'))
            ->assertOk()
            ->assertSee('Someone Else\'s Diner');

        $this->actingAs($superAdmin)->get(route('superadmin.sales.index'))->assertOk();
        $this->actingAs($superAdmin)->get(route('superadmin.customers.index'))->assertOk();

        $this->actingAs($superAdmin)->get(route('superadmin.tables.qr', $otherTable))->assertOk();
        $this->actingAs($superAdmin)->get(route('superadmin.tables.qr-image', $otherTable))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/svg+xml');
    }

    public function test_hotel_admin_cannot_reach_the_platform_wide_super_admin_views(): void
    {
        $hotel = Hotel::factory()->create();
        $admin = User::factory()->create(['role' => UserRole::HotelAdmin, 'hotel_id' => $hotel->id, 'employee_id' => 'ADM-2']);
        $admin->assignRole(UserRole::HotelAdmin->value);

        $this->actingAs($admin)->get(route('superadmin.tables.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('superadmin.sales.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('superadmin.customers.index'))->assertForbidden();
    }
}
