<?php

namespace App\Services\Waiter;

use App\Enums\SessionStatus;
use App\Enums\TableStatus;
use App\Models\Order;
use App\Models\OrderSession;
use App\Models\Table;
use App\Models\User;
use App\Services\Customer\CustomerSessionService;
use App\Services\Customer\OrderPlacementService;
use Illuminate\Auth\Access\AuthorizationException;

class ManualOrderService
{
    public function __construct(private readonly OrderPlacementService $placement)
    {
    }

    /**
     * A waiter keying in an order for a walk-in (or adding to a table that already has
     * a session). Same server-side pricing as a customer-placed order — nothing about
     * the price comes from the request.
     *
     * @param  array<int, array{menu_item_id: int, quantity?: int, note?: ?string}>  $lines
     */
    public function place(User $waiter, Table $table, array $lines): Order
    {
        if ($table->hotel_id !== $waiter->hotel_id) {
            throw new AuthorizationException('That table belongs to another hotel.');
        }

        $session = OrderSession::where('table_id', $table->id)
            ->whereIn('status', [SessionStatus::Active, SessionStatus::BillRequested])
            ->latest('id')
            ->first();

        $stale = $session && $session->updated_at->lt(now()->subHours(CustomerSessionService::SESSION_TTL_HOURS));
        if ($stale && $session->orders()->doesntExist()) {
            $session->update(['status' => SessionStatus::Expired]);
            $session = null;
        }

        $session ??= OrderSession::create([
            'hotel_id' => $table->hotel_id,
            'table_id' => $table->id,
            'member_count' => 0,
            'status' => SessionStatus::Active,
        ]);

        // Waiter-entered items sit under Member 1 (created if the table had nobody yet).
        if (! $session->members()->where('member_no', 1)->exists()) {
            $session->members()->create(['member_no' => 1]);
            $session->increment('member_count');
        }

        $order = $this->placement->place($session, 1, $lines);

        $session->touch();
        $table->update(['status' => TableStatus::Active]);

        return $order;
    }
}
