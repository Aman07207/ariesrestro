<?php

namespace App\Services\HotelAdmin;

use App\Models\MenuCategory;
use Illuminate\Database\QueryException;

class MenuCategoryService
{
    public function create(array $data): MenuCategory
    {
        return MenuCategory::create($data);
    }

    public function update(MenuCategory $category, array $data): MenuCategory
    {
        $category->update($data);

        return $category;
    }

    /**
     * True on success, false when blocked by order history (cascades to its menu items,
     * which are restrictOnDelete against order_items — a category with any item that has
     * order history can't be removed outright).
     */
    public function delete(MenuCategory $category): bool
    {
        try {
            $category->delete();
        } catch (QueryException $e) {
            return false;
        }

        return true;
    }
}
