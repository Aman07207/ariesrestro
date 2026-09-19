<?php

namespace Tests\Feature;

use App\Enums\HotelStatus;
use App\Enums\OrderItemStatus;
use App\Models\Hotel;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderSession;
use App\Models\Table;
use App\Services\Customer\BillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The spec's own worked example (Section 4.3): Paneer Tikka ₹500 (food) + Fresh
     * Lime Soda ₹100 (non-alcoholic) + Kingfisher Beer ₹300 (alcohol). Standalone
     * restaurant, 5% GST, 20% state VAT, customer agrees to a 10% service charge.
     * Must reproduce exactly ₹1,080 — if this fails, the calculation order is wrong.
     */
    public function test_the_spec_worked_example_reproduces_exactly_1080(): void
    {
        $hotel = Hotel::factory()->create([
            'status' => HotelStatus::Active,
            'gst_rate' => 5,
            'vat_rate' => 20,
            'is_luxury_hotel' => false,
            'default_service_charge_percent' => 10,
        ]);
        $foodCat = MenuCategory::create(['hotel_id' => $hotel->id, 'name' => 'Main Course', 'type' => 'food', 'display_order' => 1]);
        $drinkCat = MenuCategory::create(['hotel_id' => $hotel->id, 'name' => 'Beverages', 'type' => 'beverage', 'display_order' => 2]);
        $barCat = MenuCategory::create(['hotel_id' => $hotel->id, 'name' => 'Bar', 'type' => 'beverage', 'display_order' => 3]);

        $paneer = MenuItem::create(['hotel_id' => $hotel->id, 'category_id' => $foodCat->id, 'name' => 'Paneer Tikka', 'price' => 500, 'veg_type' => 'veg', 'is_available' => true, 'tax_track' => 'gst']);
        $soda = MenuItem::create(['hotel_id' => $hotel->id, 'category_id' => $drinkCat->id, 'name' => 'Fresh Lime Soda', 'price' => 100, 'veg_type' => 'veg', 'is_available' => true, 'tax_track' => 'gst']);
        $beer = MenuItem::create(['hotel_id' => $hotel->id, 'category_id' => $barCat->id, 'name' => 'Kingfisher Beer', 'price' => 300, 'veg_type' => 'non-veg', 'is_available' => true, 'tax_track' => 'vat']);

        $table = Table::factory()->create(['hotel_id' => $hotel->id]);
        $session = OrderSession::create(['hotel_id' => $hotel->id, 'table_id' => $table->id, 'member_count' => 1]);

        $order = Order::create([
            'session_id' => $session->id, 'table_id' => $table->id, 'member_no' => 1,
            'order_number' => 'ORD-TEST-1', 'status' => 'pending', 'total_amount' => 900,
        ]);
        foreach ([$paneer, $soda, $beer] as $item) {
            $order->orderItems()->create([
                'menu_item_id' => $item->id, 'name' => $item->name, 'price' => $item->price,
                'quantity' => 1, 'status' => OrderItemStatus::Pending,
            ]);
        }

        $result = app(BillingService::class)->preview($session, serviceChargeConsent: true);

        $this->assertEquals(600.0, $result['food_base_amount']);
        $this->assertEquals(300.0, $result['alcohol_base_amount']);
        $this->assertEquals(90.0, $result['service_charge_amount']);
        $this->assertEquals(15.0, $result['cgst_amount']);
        $this->assertEquals(15.0, $result['sgst_amount']);
        $this->assertEquals(60.0, $result['vat_amount']);
        $this->assertEquals(1080.0, $result['grand_total']);
    }

    public function test_service_charge_is_zero_without_consent_and_gst_never_taxes_the_service_charge(): void
    {
        $hotel = Hotel::factory()->create(['gst_rate' => 5, 'default_service_charge_percent' => 10]);
        $foodCat = MenuCategory::create(['hotel_id' => $hotel->id, 'name' => 'Main Course', 'type' => 'food', 'display_order' => 1]);
        $item = MenuItem::create(['hotel_id' => $hotel->id, 'category_id' => $foodCat->id, 'name' => 'Dal Makhani', 'price' => 600, 'veg_type' => 'veg', 'is_available' => true, 'tax_track' => 'gst']);
        $table = Table::factory()->create(['hotel_id' => $hotel->id]);
        $session = OrderSession::create(['hotel_id' => $hotel->id, 'table_id' => $table->id, 'member_count' => 1]);
        $order = Order::create(['session_id' => $session->id, 'table_id' => $table->id, 'member_no' => 1, 'order_number' => 'ORD-TEST-2', 'status' => 'pending', 'total_amount' => 600]);
        $order->orderItems()->create(['menu_item_id' => $item->id, 'name' => $item->name, 'price' => $item->price, 'quantity' => 1, 'status' => OrderItemStatus::Pending]);

        $withoutConsent = app(BillingService::class)->preview($session, serviceChargeConsent: false);
        $this->assertEquals(0.0, $withoutConsent['service_charge_amount']);
        // GST on food_base (600) alone = 30, never (600+service_charge) x 5%.
        $this->assertEquals(15.0, $withoutConsent['cgst_amount']);
        $this->assertEquals(15.0, $withoutConsent['sgst_amount']);
        $this->assertEquals(630.0, $withoutConsent['grand_total']);

        $withConsent = app(BillingService::class)->preview($session, serviceChargeConsent: true);
        $this->assertEquals(60.0, $withConsent['service_charge_amount']);
        // GST/CGST/SGST must be identical whether or not the service charge was added.
        $this->assertEquals(15.0, $withConsent['cgst_amount']);
        $this->assertEquals(15.0, $withConsent['sgst_amount']);
    }

    public function test_no_alcohol_ordered_means_no_vat_and_no_alcohol_base(): void
    {
        $hotel = Hotel::factory()->create(['gst_rate' => 5, 'vat_rate' => 20]);
        $foodCat = MenuCategory::create(['hotel_id' => $hotel->id, 'name' => 'Main Course', 'type' => 'food', 'display_order' => 1]);
        $item = MenuItem::create(['hotel_id' => $hotel->id, 'category_id' => $foodCat->id, 'name' => 'Veg Biryani', 'price' => 260, 'veg_type' => 'veg', 'is_available' => true, 'tax_track' => 'gst']);
        $table = Table::factory()->create(['hotel_id' => $hotel->id]);
        $session = OrderSession::create(['hotel_id' => $hotel->id, 'table_id' => $table->id, 'member_count' => 1]);
        $order = Order::create(['session_id' => $session->id, 'table_id' => $table->id, 'member_no' => 1, 'order_number' => 'ORD-TEST-3', 'status' => 'pending', 'total_amount' => 260]);
        $order->orderItems()->create(['menu_item_id' => $item->id, 'name' => $item->name, 'price' => $item->price, 'quantity' => 1, 'status' => OrderItemStatus::Pending]);

        $result = app(BillingService::class)->preview($session, serviceChargeConsent: false);

        $this->assertEquals(0.0, $result['alcohol_base_amount']);
        $this->assertEquals(0.0, $result['vat_amount']);
    }

    public function test_a_cancelled_item_is_excluded_from_the_base_amounts(): void
    {
        $hotel = Hotel::factory()->create(['gst_rate' => 5]);
        $foodCat = MenuCategory::create(['hotel_id' => $hotel->id, 'name' => 'Main Course', 'type' => 'food', 'display_order' => 1]);
        $item = MenuItem::create(['hotel_id' => $hotel->id, 'category_id' => $foodCat->id, 'name' => 'Dal Makhani', 'price' => 240, 'veg_type' => 'veg', 'is_available' => true, 'tax_track' => 'gst']);
        $table = Table::factory()->create(['hotel_id' => $hotel->id]);
        $session = OrderSession::create(['hotel_id' => $hotel->id, 'table_id' => $table->id, 'member_count' => 1]);
        $order = Order::create(['session_id' => $session->id, 'table_id' => $table->id, 'member_no' => 1, 'order_number' => 'ORD-TEST-4', 'status' => 'pending', 'total_amount' => 240]);
        $order->orderItems()->create([
            'menu_item_id' => $item->id, 'name' => $item->name, 'price' => $item->price,
            'quantity' => 1, 'status' => OrderItemStatus::Cancelled,
        ]);

        $result = app(BillingService::class)->preview($session, serviceChargeConsent: false);

        $this->assertEquals(0.0, $result['food_base_amount']);
        $this->assertEquals(0.0, $result['grand_total']);
    }
}
