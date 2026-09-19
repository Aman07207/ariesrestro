<?php

namespace App\Services\Customer;

use App\Models\Hotel;
use Carbon\Carbon;

class GreetingService
{
    /**
     * @return array{text: string, icon: string, hotelName: string}
     */
    public function forHotel(Hotel $hotel): array
    {
        // Always India time, regardless of the visitor's own device clock/timezone.
        $hour = (int) Carbon::now('Asia/Kolkata')->format('G');

        [$text, $icon] = match (true) {
            $hour >= 4 && $hour < 12 => ['Good Morning', '☀️'],
            $hour >= 12 && $hour < 17 => ['Good Afternoon', '🌤️'],
            $hour >= 17 && $hour < 21 => ['Good Evening', '🌆'],
            default => ['Good Night', '🌙'],
        };

        return [
            'text' => $text,
            'icon' => $icon,
            'hotelName' => $hotel->name,
        ];
    }
}
