<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Enforces multi-tenant isolation: a query automatically only sees rows for the
 * authenticated staff member's own hotel. Super Admins (hotel_id === null) and
 * unauthenticated/customer-facing contexts (no auth session at all — those are
 * scoped separately by order_sessions.session_token) are left unrestricted.
 */
trait BelongsToHotel
{
    public static function bootBelongsToHotel(): void
    {
        static::addGlobalScope('hotel', function (Builder $builder) {
            $user = auth()->user();

            if ($user && $user->hotel_id) {
                $builder->where($builder->getModel()->qualifyColumn('hotel_id'), $user->hotel_id);
            }
        });

        static::creating(function (Model $model) {
            $user = auth()->user();

            if ($user && $user->hotel_id && ! $model->hotel_id) {
                $model->hotel_id = $user->hotel_id;
            }
        });
    }
}
