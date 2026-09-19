<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('waiter-calls.{hotelId}', function ($user, int $hotelId) {
    return (int) $user->hotel_id === $hotelId;
});
