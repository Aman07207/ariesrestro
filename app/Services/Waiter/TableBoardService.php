<?php

namespace App\Services\Waiter;

use App\Enums\OrderItemStatus;
use App\Enums\SessionStatus;
use App\Models\Table;
use Illuminate\Support\Collection;

class TableBoardService
{
    /** @return Collection<int, array{table: Table, colorClass: string, memberCount: int, itemCount: int}> */
    public function forHotel(int $hotelId): Collection
    {
        return Table::where('hotel_id', $hotelId)
            ->orderBy('table_number')
            ->with(['orderSessions' => fn ($q) => $q->whereIn('status', [SessionStatus::Active, SessionStatus::BillRequested])
                ->with('orders.orderItems')])
            ->get()
            ->map(function (Table $table) {
                $session = $table->orderSessions->first();
                $items = $session
                    ? $session->orders->flatMap->orderItems->filter(fn ($i) => $i->status !== OrderItemStatus::Cancelled)
                    : collect();

                return [
                    'table' => $table,
                    'colorClass' => ! $session ? 'available' : ($session->status === SessionStatus::BillRequested ? 'bill' : 'active'),
                    'memberCount' => $session ? $session->orders->pluck('member_no')->unique()->count() : 0,
                    'itemCount' => $items->count(),
                ];
            });
    }
}
