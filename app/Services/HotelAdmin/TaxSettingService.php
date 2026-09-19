<?php

namespace App\Services\HotelAdmin;

use App\Models\Hotel;

class TaxSettingService
{
    public function update(Hotel $hotel, array $data): Hotel
    {
        $hotel->update($data);

        return $hotel;
    }
}
