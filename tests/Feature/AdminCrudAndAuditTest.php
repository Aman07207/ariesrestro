<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Hotel;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Subscription;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class AdminCrudAndAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

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

    public function test_hotel_admin_can_create_edit_and_remove_a_menu_item_with_activity_logged(): void
    {
        $hotel = Hotel::factory()->create();
        $admin = $this->makeStaff(UserRole::HotelAdmin, $hotel);
        $category = MenuCategory::create(['hotel_id' => $hotel->id, 'name' => 'Starters', 'type' => 'food', 'display_order' => 1]);

        $this->actingAs($admin)->post(route('hoteladmin.menu-items.store'), [
            'category_id' => $category->id,
            'name' => 'Test Samosa',
            'price' => 90,
            'veg_type' => 'veg',
            'tax_track' => 'gst',
            'is_available' => '1',
        ])->assertRedirect(route('hoteladmin.menu-items.index'));

        $item = MenuItem::where('name', 'Test Samosa')->firstOrFail();
        $this->assertEquals($hotel->id, $item->hotel_id);
        $this->assertTrue(Activity::where('log_name', 'menu_item')->where('subject_id', $item->id)->exists());

        $this->actingAs($admin)->put(route('hoteladmin.menu-items.update', $item), [
            'category_id' => $category->id,
            'name' => 'Test Samosa',
            'price' => 110,
            'veg_type' => 'veg',
            'tax_track' => 'gst',
            'is_available' => '0',
        ])->assertRedirect(route('hoteladmin.menu-items.index'));

        $this->assertEquals(110, $item->fresh()->price);

        $this->actingAs($admin)->delete(route('hoteladmin.menu-items.destroy', $item))
            ->assertRedirect(route('hoteladmin.menu-items.index'));
        $this->assertDatabaseMissing('menu_items', ['id' => $item->id]);
    }

    public function test_super_admin_can_create_edit_and_remove_a_subscription_with_activity_logged(): void
    {
        $hotel = Hotel::factory()->create();
        $superAdmin = $this->makeStaff(UserRole::SuperAdmin);

        $this->actingAs($superAdmin)->post(route('superadmin.subscriptions.store'), [
            'hotel_id' => $hotel->id,
            'plan' => 'standard',
            'billing_cycle' => 'yearly',
            'price' => 12000,
            'start_date' => now()->toDateString(),
            'status' => 'active',
        ])->assertRedirect(route('superadmin.subscriptions.index'));

        $subscription = Subscription::where('hotel_id', $hotel->id)->firstOrFail();
        $this->assertTrue(Activity::where('log_name', 'subscription')->where('subject_id', $subscription->id)->exists());

        $this->actingAs($superAdmin)->put(route('superadmin.subscriptions.update', $subscription), [
            'hotel_id' => $hotel->id,
            'plan' => 'premium',
            'billing_cycle' => 'yearly',
            'price' => 18000,
            'start_date' => now()->toDateString(),
            'status' => 'active',
        ])->assertRedirect(route('superadmin.subscriptions.index'));

        $this->assertEquals('premium', $subscription->fresh()->plan);

        $this->actingAs($superAdmin)->delete(route('superadmin.subscriptions.destroy', $subscription))
            ->assertRedirect(route('superadmin.subscriptions.index'));
        $this->assertDatabaseMissing('subscriptions', ['id' => $subscription->id]);
    }

    public function test_deactivating_a_hotel_does_not_delete_it(): void
    {
        $hotel = Hotel::factory()->create();
        $superAdmin = $this->makeStaff(UserRole::SuperAdmin);

        $this->actingAs($superAdmin)->delete(route('superadmin.hotels.destroy', $hotel))
            ->assertRedirect(route('superadmin.hotels.index'));

        $this->assertDatabaseHas('hotels', ['id' => $hotel->id, 'status' => 'inactive']);
    }
}
