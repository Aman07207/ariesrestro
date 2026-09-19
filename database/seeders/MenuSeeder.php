<?php

namespace Database\Seeders;

use App\Models\Hotel;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $hotel = Hotel::where('slug', 'aries-cafe')->firstOrFail();

        $categories = [
            ['name' => 'Starters', 'type' => 'food', 'display_order' => 1],
            ['name' => 'Main Course', 'type' => 'food', 'display_order' => 2],
            ['name' => 'Beverages', 'type' => 'beverage', 'display_order' => 3],
            ['name' => 'Desserts', 'type' => 'food', 'display_order' => 4],
            // A menu-organization category, distinct from the tax_track on the item
            // itself below — this is what actually drives GST vs VAT.
            ['name' => 'Bar', 'type' => 'beverage', 'display_order' => 5],
        ];

        $categoryModels = [];
        foreach ($categories as $category) {
            $categoryModels[$category['name']] = MenuCategory::updateOrCreate(
                ['hotel_id' => $hotel->id, 'name' => $category['name']],
                ['type' => $category['type'], 'display_order' => $category['display_order']]
            );
        }

        $items = [
            ['name' => 'Paneer Tikka', 'category' => 'Starters', 'price' => 220, 'veg_type' => 'veg', 'avg_rating' => 4.6, 'is_popular' => true],
            ['name' => 'Chicken Wings', 'category' => 'Starters', 'price' => 280, 'veg_type' => 'non-veg', 'avg_rating' => 4.4, 'is_popular' => false],
            ['name' => 'Butter Chicken', 'category' => 'Main Course', 'price' => 350, 'veg_type' => 'non-veg', 'avg_rating' => 4.8, 'is_popular' => true],
            ['name' => 'Dal Makhani', 'category' => 'Main Course', 'price' => 240, 'veg_type' => 'veg', 'avg_rating' => 4.5, 'is_popular' => false],
            ['name' => 'Veg Biryani', 'category' => 'Main Course', 'price' => 260, 'veg_type' => 'veg', 'avg_rating' => 4.3, 'is_popular' => false],
            ['name' => 'Masala Chai', 'category' => 'Beverages', 'price' => 60, 'veg_type' => 'veg', 'avg_rating' => 4.7, 'is_popular' => false],
            ['name' => 'Cold Coffee', 'category' => 'Beverages', 'price' => 120, 'veg_type' => 'veg', 'avg_rating' => 4.5, 'is_popular' => false],
            ['name' => 'Fresh Lime Soda', 'category' => 'Beverages', 'price' => 80, 'veg_type' => 'veg', 'avg_rating' => 4.2, 'is_popular' => false],
            ['name' => 'Gulab Jamun', 'category' => 'Desserts', 'price' => 90, 'veg_type' => 'veg', 'avg_rating' => 4.6, 'is_popular' => false],
            ['name' => 'Chocolate Brownie', 'category' => 'Desserts', 'price' => 150, 'veg_type' => 'veg', 'avg_rating' => 4.4, 'is_popular' => false],
            // Alcohol: taxed under state VAT, not GST — the reachable path for that
            // half of the billing engine. Nothing else in the seed data is alcoholic.
            ['name' => 'Kingfisher Beer', 'category' => 'Bar', 'price' => 300, 'veg_type' => 'non-veg', 'avg_rating' => 4.3, 'is_popular' => false, 'tax_track' => 'vat'],
        ];

        foreach ($items as $item) {
            MenuItem::updateOrCreate(
                ['hotel_id' => $hotel->id, 'name' => $item['name']],
                [
                    'category_id' => $categoryModels[$item['category']]->id,
                    'price' => $item['price'],
                    'veg_type' => $item['veg_type'],
                    'is_available' => true,
                    'avg_rating' => $item['avg_rating'],
                    'is_popular' => $item['is_popular'],
                    'tax_track' => $item['tax_track'] ?? 'gst',
                ]
            );
        }
    }
}
