<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('waiter-calls.{hotelId}', function ($user, int $hotelId) {
    return (int) $user->hotel_id === $hotelId;
});

// Kitchen/floor staff of one hotel only — customers never get this channel.
Broadcast::channel('hotel.{hotelId}.orders', function ($user, int $hotelId) {
    return (int) $user->hotel_id === $hotelId
        && $user->hasAnyRole(['chef', 'waiter', 'manager', 'hotel_admin']);
});
