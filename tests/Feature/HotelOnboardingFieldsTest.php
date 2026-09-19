<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Hotel;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HotelOnboardingFieldsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        Storage::fake('public');
    }

    private function makeSuperAdmin(): User
    {
        $user = User::factory()->create(['role' => UserRole::SuperAdmin, 'employee_id' => 'SA-'.fake()->unique()->numberBetween(1000, 9999)]);
        $user->assignRole(UserRole::SuperAdmin->value);

        return $user;
    }

    public function test_super_admin_can_set_gstin_logo_and_google_review_link_on_a_hotel(): void
    {
        $superAdmin = $this->makeSuperAdmin();
        $logo = UploadedFile::fake()->create('logo.png', 50, 'image/png');

        $this->actingAs($superAdmin)->post(route('superadmin.hotels.store'), [
            'name' => 'Test Hotel', 'slug' => 'test-hotel', 'subscription_plan' => 'standard',
            'gst_rate' => 5, 'gstin' => '24AAAAA0000A1Z5',
            'google_review_link' => 'https://g.page/r/testhotel',
            'logo' => $logo,
        ])->assertRedirect(route('superadmin.hotels.index'));

        $hotel = Hotel::where('slug', 'test-hotel')->firstOrFail();
        $this->assertEquals('24AAAAA0000A1Z5', $hotel->gstin);
        $this->assertEquals('https://g.page/r/testhotel', $hotel->google_review_link);
        $this->assertNotNull($hotel->logo);
        Storage::disk('public')->assertExists($hotel->logo);
    }

    public function test_an_invalid_gstin_is_rejected(): void
    {
        $superAdmin = $this->makeSuperAdmin();

        $this->actingAs($superAdmin)->post(route('superadmin.hotels.store'), [
            'name' => 'Test Hotel', 'slug' => 'test-hotel-2', 'subscription_plan' => 'standard',
            'gst_rate' => 5, 'gstin' => 'not-a-real-gstin',
        ])->assertSessionHasErrors('gstin');

        $this->assertDatabaseMissing('hotels', ['slug' => 'test-hotel-2']);
    }

    public function test_super_admin_can_replace_a_hotels_logo_and_the_old_one_is_removed(): void
    {
        $superAdmin = $this->makeSuperAdmin();
        $hotel = Hotel::factory()->create(['gst_rate' => 5]);

        $firstLogo = UploadedFile::fake()->create('first.png', 50, 'image/png');
        $this->actingAs($superAdmin)->put(route('superadmin.hotels.update', $hotel), [
            'name' => $hotel->name, 'slug' => $hotel->slug, 'subscription_plan' => 'standard',
            'status' => 'active', 'gst_rate' => 5, 'logo' => $firstLogo,
        ])->assertRedirect(route('superadmin.hotels.index'));

        $hotel->refresh();
        $originalPath = $hotel->logo;
        Storage::disk('public')->assertExists($originalPath);

        $secondLogo = UploadedFile::fake()->create('second.png', 50, 'image/png');
        $this->actingAs($superAdmin)->put(route('superadmin.hotels.update', $hotel), [
            'name' => $hotel->name, 'slug' => $hotel->slug, 'subscription_plan' => 'standard',
            'status' => 'active', 'gst_rate' => 5, 'logo' => $secondLogo,
        ])->assertRedirect(route('superadmin.hotels.index'));

        $hotel->refresh();
        Storage::disk('public')->assertMissing($originalPath);
        Storage::disk('public')->assertExists($hotel->logo);
    }
}
