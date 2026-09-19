<?php

namespace App\Services\SuperAdmin;

use App\Enums\HotelStatus;
use App\Models\Hotel;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class HotelService
{
    public function create(array $data, ?UploadedFile $logo = null): Hotel
    {
        // gst_rate/is_luxury_hotel/vat_rate/default_service_charge_percent all come
        // straight from the onboarding form now — no separate defaults table to seed
        // from; the migration's own column defaults (5% GST, not luxury) cover a hotel
        // onboarded without touching those fields at all.
        if ($logo) {
            $data['logo'] = $logo->store('hotel-logos', 'public');
        }

        return Hotel::create($data);
    }

    public function update(Hotel $hotel, array $data, ?UploadedFile $logo = null): Hotel
    {
        if ($logo) {
            if ($hotel->logo) {
                Storage::disk('public')->delete($hotel->logo);
            }
            $data['logo'] = $logo->store('hotel-logos', 'public');
        }

        $hotel->update($data);

        return $hotel;
    }

    /**
     * Deactivate rather than hard-delete: our FKs cascade, so a real delete would
     * wipe every table/order/menu/staff row for this hotel — not something a single
     * click should be able to do irreversibly.
     */
    public function deactivate(Hotel $hotel): Hotel
    {
        $hotel->update(['status' => HotelStatus::Inactive]);

        return $hotel;
    }
}
