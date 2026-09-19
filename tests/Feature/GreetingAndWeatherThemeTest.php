<?php

namespace Tests\Feature;

use App\Enums\HotelStatus;
use App\Enums\Season;
use App\Enums\UserRole;
use App\Enums\WeatherThemeMode;
use App\Models\Hotel;
use App\Models\MenuCategory;
use App\Models\PlatformSetting;
use App\Models\Table;
use App\Models\User;
use App\Services\Customer\GreetingService;
use App\Services\WeatherThemeService;
use Carbon\Carbon;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class GreetingAndWeatherThemeTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function istInstant(int $hour): Carbon
    {
        return Carbon::create(2026, 4, 15, $hour, 0, 0, 'Asia/Kolkata');
    }

    public function test_greeting_time_bands_are_computed_in_ist(): void
    {
        $service = new GreetingService;
        $hotel = Hotel::factory()->make(['name' => 'Aries Café']);

        $cases = [
            4 => 'Good Morning',
            11 => 'Good Morning',
            12 => 'Good Afternoon',
            16 => 'Good Afternoon',
            17 => 'Good Evening',
            20 => 'Good Evening',
            21 => 'Good Night',
            3 => 'Good Night',
        ];

        foreach ($cases as $hour => $expectedText) {
            Carbon::setTestNow($this->istInstant($hour));
            $greeting = $service->forHotel($hotel);
            $this->assertSame($expectedText, $greeting['text'], "hour {$hour} should greet '{$expectedText}'");
            $this->assertSame('Aries Café', $greeting['hotelName']);
        }
    }

    public function test_hotel_override_wins_over_everything_else(): void
    {
        $hotel = Hotel::factory()->create(['weather_theme_mode' => WeatherThemeMode::Winter]);
        PlatformSetting::set('weather_theme_mode', WeatherThemeMode::Summer->value);
        Cache::put("weather:{$hotel->id}", Season::Monsoon->value, now()->addHour());

        $season = app(WeatherThemeService::class)->resolveForHotel($hotel);

        $this->assertSame(Season::Winter, $season);
    }

    public function test_platform_default_wins_when_hotel_is_auto(): void
    {
        $hotel = Hotel::factory()->create(['weather_theme_mode' => WeatherThemeMode::Auto]);
        PlatformSetting::set('weather_theme_mode', WeatherThemeMode::Monsoon->value);
        Cache::put("weather:{$hotel->id}", Season::Summer->value, now()->addHour());

        $season = app(WeatherThemeService::class)->resolveForHotel($hotel);

        $this->assertSame(Season::Monsoon, $season);
    }

    public function test_cached_weather_wins_when_both_hotel_and_platform_are_auto(): void
    {
        $hotel = Hotel::factory()->create(['weather_theme_mode' => WeatherThemeMode::Auto]);
        // No PlatformSetting row at all — defaults to 'auto'.
        Cache::put("weather:{$hotel->id}", Season::Summer->value, now()->addHour());

        $season = app(WeatherThemeService::class)->resolveForHotel($hotel);

        $this->assertSame(Season::Summer, $season);
    }

    public function test_calendar_fallback_used_when_nothing_else_is_set(): void
    {
        $hotel = Hotel::factory()->create(['weather_theme_mode' => WeatherThemeMode::Auto]);

        Carbon::setTestNow(Carbon::create(2026, 8, 1, 12, 0, 0, 'Asia/Kolkata')); // August -> monsoon
        $season = app(WeatherThemeService::class)->resolveForHotel($hotel);

        $this->assertSame(Season::Monsoon, $season);
    }

    public function test_refresh_skips_hotels_without_a_location_without_throwing(): void
    {
        $hotel = Hotel::factory()->create(['latitude' => null, 'longitude' => null]);

        app(WeatherThemeService::class)->refreshCacheForHotel($hotel);

        $this->assertNull(Cache::get("weather:{$hotel->id}"));
    }

    public function test_refresh_skips_silently_when_no_api_key_configured(): void
    {
        config(['services.openweathermap.key' => null]);
        $hotel = Hotel::factory()->create(['latitude' => 23.03, 'longitude' => 72.58]);

        app(WeatherThemeService::class)->refreshCacheForHotel($hotel);

        $this->assertNull(Cache::get("weather:{$hotel->id}"));
    }

    public function test_platform_setting_get_set_and_cache_invalidation(): void
    {
        $this->assertSame('fallback', PlatformSetting::get('weather_theme_mode', 'fallback'));

        PlatformSetting::set('weather_theme_mode', 'winter');
        $this->assertSame('winter', PlatformSetting::get('weather_theme_mode'));

        PlatformSetting::set('weather_theme_mode', 'summer');
        $this->assertSame('summer', PlatformSetting::get('weather_theme_mode'));
    }

    public function test_super_admin_can_set_the_platform_default_theme_mode(): void
    {
        $this->seed(RoleSeeder::class);
        $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $superAdmin->assignRole(UserRole::SuperAdmin->value);

        $this->actingAs($superAdmin)
            ->put(route('superadmin.platform-settings.update'), ['weather_theme_mode' => 'winter'])
            ->assertRedirect(route('superadmin.platform-settings'));

        $this->assertSame('winter', PlatformSetting::get('weather_theme_mode'));
    }

    public function test_hotel_admin_can_override_weather_theme_for_their_own_hotel(): void
    {
        $this->seed(RoleSeeder::class);
        $hotel = Hotel::factory()->create(['gst_rate' => 5, 'weather_theme_mode' => WeatherThemeMode::Auto]);
        $admin = User::factory()->create(['role' => UserRole::HotelAdmin, 'hotel_id' => $hotel->id]);
        $admin->assignRole(UserRole::HotelAdmin->value);

        $this->actingAs($admin)->put(route('hoteladmin.tax-settings.update'), [
            'gst_rate' => 5,
            'weather_theme_mode' => 'summer',
        ])->assertRedirect(route('hoteladmin.tax-settings'));

        $this->assertSame(WeatherThemeMode::Summer, $hotel->fresh()->weather_theme_mode);
    }

    public function test_customer_menu_renders_the_server_resolved_greeting_and_theme(): void
    {
        $this->seed(RoleSeeder::class);
        $hotel = Hotel::factory()->create([
            'status' => HotelStatus::Active,
            'name' => 'Aries Café',
            'weather_theme_mode' => WeatherThemeMode::Winter,
        ]);
        MenuCategory::create(['hotel_id' => $hotel->id, 'name' => 'Starters', 'type' => 'food', 'display_order' => 1]);
        $table = Table::factory()->create(['hotel_id' => $hotel->id, 'table_number' => 5]);

        Carbon::setTestNow($this->istInstant(9)); // morning

        $scan = $this->get(route('customer.scan', ['hotel' => $hotel->slug, 'table_uuid' => $table->table_uuid]));
        $cookies = [];
        foreach ($scan->headers->getCookies() as $cookie) {
            $cookies[$cookie->getName()] = $cookie->getValue();
        }

        $menu = $this->withUnencryptedCookies($cookies)->get(route('customer.menu'));

        $menu->assertOk();
        $menu->assertSee('theme-winter', false);
        $menu->assertSee('Howdy! Welcome to Aries Café');
        $menu->assertSee('Good Morning');
    }
}
