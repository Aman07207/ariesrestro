<?php

namespace App\Services\HotelAdmin;

use App\Models\MenuItem;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MenuItemService
{
    public function create(array $data, ?UploadedFile $image): MenuItem
    {
        $data['is_available'] = (bool) ($data['is_available'] ?? false);
        $data['is_popular'] = (bool) ($data['is_popular'] ?? false);

        if ($image) {
            // Stored via the storage disk abstraction, never a raw executable public path,
            // and never trusting the client's original filename.
            $data['image'] = $image->store('menu-items', 'public');
        }

        return MenuItem::create($data);
    }

    public function update(MenuItem $item, array $data, ?UploadedFile $image): MenuItem
    {
        $data['is_available'] = (bool) ($data['is_available'] ?? false);
        $data['is_popular'] = (bool) ($data['is_popular'] ?? false);

        if ($image) {
            if ($item->image) {
                Storage::disk('public')->delete($item->image);
            }
            $data['image'] = $image->store('menu-items', 'public');
        }

        $item->update($data);

        return $item;
    }

    /**
     * True = deleted. False = blocked by order history (menu_item_id on order_items is
     * restrictOnDelete) — marked unavailable instead so it never disappears out from
     * under past orders.
     */
    public function delete(MenuItem $item): bool
    {
        try {
            $item->delete();
        } catch (QueryException $e) {
            $item->update(['is_available' => false]);

            return false;
        }

        if ($item->image) {
            Storage::disk('public')->delete($item->image);
        }

        return true;
    }
}
