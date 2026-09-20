<?php

namespace Tests\Feature;

use App\Enums\HotelStatus;
use App\Enums\UserRole;
use App\Models\Hotel;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\OrderItem;
use App\Models\Table;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Drives the real /livewire/update endpoint (snapshot round-trip) rather than Livewire::test(). */
class LivewireHttpUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function staff(UserRole $role, Hotel $hotel): User
    {
        $u = User::factory()->create(['role' => $role, 'hotel_id' => $hotel->id, 'employee_id' => strtoupper($role->value).'-'.fake()->unique()->numberBetween(1000, 9999)]);
        $u->assignRole($role->value);

        return $u;
    }

    private function snapshots(string $html): array
    {
        preg_match_all('/wire:snapshot="([^"]+)"/', $html, $m);

        return array_map(fn ($s) => html_entity_decode($s, ENT_QUOTES), $m[1]);
    }

    private function update(string $snapshot, array $calls = [])
    {
        return $this->withHeaders(['X-Livewire' => '1'])->postJson(route('default-livewire.update'), [
            'components' => [['snapshot' => $snapshot, 'updates' => (object) [], 'calls' => $calls]],
        ]);
    }

    public function test_staff_and_customer_live_components_round_trip_over_http(): void
    {
        $this->seed(RoleSeeder::class);
        $hotel = Hotel::factory()->create(['status' => HotelStatus::Active]);
        $cat = MenuCategory::create(['hotel_id' => $hotel->id, 'name' => 'Main', 'type' => 'food', 'display_order' => 1]);
        $item = MenuItem::create(['hotel_id' => $hotel->id, 'category_id' => $cat->id, 'name' => 'Butter Chicken', 'price' => 350, 'veg_type' => 'non-veg', 'is_available' => true, 'tax_track' => 'gst']);
        $table = Table::factory()->create(['hotel_id' => $hotel->id, 'table_number' => 4]);

        $scan = $this->get(route('customer.scan', ['hotel' => $hotel->slug, 'table_uuid' => $table->table_uuid]));
        $cookies = [];
        foreach ($scan->headers->getCookies() as $c) {
            $cookies[$c->getName()] = $c->getValue();
        }
        $this->withUnencryptedCookies($cookies)->withCredentials()->postJson(route('customer.order.place'), ['items' => [['menu_item_id' => $item->id, 'quantity' => 1]]])->assertOk();

        // Customer tracker (guest) — poll + echo-triggered method.
        $html = $this->withUnencryptedCookies($cookies)->get(route('customer.track'))->assertOk()->getContent();
        [$snap] = $this->snapshots($html);
        $this->update($snap, [['path' => '', 'method' => 'detect', 'params' => []]])->assertOk();

        // Chef board: poll, then advance through the real endpoint.
        $chef = $this->staff(UserRole::Chef, $hotel);
        $html = $this->actingAs($chef)->get(route('chef.queue'))->assertOk()->getContent();
        [$snap] = $this->snapshots($html);
        $this->actingAs($chef)->update($snap, [['path' => '', 'method' => 'detect', 'params' => []]])->assertOk();
        $this->actingAs($chef)->update($snap, [['path' => '', 'method' => 'advance', 'params' => [OrderItem::firstOrFail()->id]]])->assertOk();
        $this->assertEquals('preparing', OrderItem::firstOrFail()->status->value);

        // Waiter grid + table detail.
        $waiter = $this->staff(UserRole::Waiter, $hotel);
        $html = $this->actingAs($waiter)->get(route('waiter.tables'))->assertOk()->getContent();
        foreach ($this->snapshots($html) as $snap) {
            // The calls badge is on this page too; it has no watchKitchen.
            if (str_contains($snap, 'calls-badge')) {
                continue;
            }
            $this->actingAs($waiter)->update($snap, [['path' => '', 'method' => 'watchKitchen', 'params' => []]])->assertOk();
        }
        $html = $this->actingAs($waiter)->get(route('waiter.tables.show', $table))->assertOk()->getContent();
        foreach ($this->snapshots($html) as $snap) {
            $this->actingAs($waiter)->update($snap, [['path' => '', 'method' => 'watchKitchen', 'params' => []]])->assertOk();
        }
    }
}
