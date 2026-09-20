<?php

namespace App\Support;

/**
 * Public channel name for a customer's order-tracking page. The key is an HMAC of the
 * session id, never the session token itself (that stays the guest's httpOnly-cookie
 * credential), and payloads on it carry only item name + status.
 */
class TrackChannel
{
    public static function key(int $sessionId): string
    {
        return substr(hash_hmac('sha256', 'track:'.$sessionId, (string) config('app.key')), 0, 32);
    }

    public static function name(int $sessionId): string
    {
        return 'track.'.self::key($sessionId);
    }
}
