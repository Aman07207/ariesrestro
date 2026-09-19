<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Hotel;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_hotel_admin_forgot_password_sends_a_real_reset_link(): void
    {
        Notification::fake();

        $hotel = Hotel::factory()->create();
        $admin = User::factory()->create(['role' => UserRole::HotelAdmin, 'hotel_id' => $hotel->id, 'employee_id' => 'ADM-1']);

        $this->post(route('password.email'), ['identifier' => $admin->email])
            ->assertRedirect();

        Notification::assertSentTo($admin, ResetPassword::class);
        $this->assertDatabaseHas('password_reset_tokens', ['email' => $admin->email]);
    }

    public function test_super_admin_forgot_password_sends_a_real_reset_link_via_employee_id(): void
    {
        Notification::fake();

        $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin, 'hotel_id' => null, 'employee_id' => 'SUP-1']);

        $this->post(route('password.email'), ['identifier' => 'SUP-1'])
            ->assertRedirect();

        Notification::assertSentTo($superAdmin, ResetPassword::class);
    }

    public function test_waiter_forgot_password_is_told_to_contact_hotel_admin_and_gets_no_token(): void
    {
        Notification::fake();

        $hotel = Hotel::factory()->create();
        $waiter = User::factory()->create(['role' => UserRole::Waiter, 'hotel_id' => $hotel->id, 'employee_id' => 'WTR-9']);

        $response = $this->post(route('password.email'), ['identifier' => $waiter->email]);
        $response->assertRedirect();
        $response->assertSessionHas('status', fn ($msg) => str_contains($msg, 'Hotel Admin'));

        Notification::assertNothingSent();
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $waiter->email]);
    }

    public function test_unknown_identifier_gets_the_same_generic_message_as_a_real_one(): void
    {
        Notification::fake();

        $response = $this->post(route('password.email'), ['identifier' => 'nobody@nowhere.test']);

        $response->assertRedirect();
        $response->assertSessionHas('status', fn ($msg) => str_contains($msg, 'a password reset link has been sent'));
        $this->assertEquals(0, DB::table('password_reset_tokens')->count());
    }
}
