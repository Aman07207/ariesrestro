<?php

namespace App\Support;

use Throwable;

/**
 * Broadcasts are best-effort: the database write is the source of truth and every live
 * screen polls as a fallback, so a down Reverb server must never fail the action itself.
 */
class Realtime
{
    public static function dispatch(object $event): void
    {
        try {
            event($event);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
