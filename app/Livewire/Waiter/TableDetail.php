<?php

namespace App\Livewire\Waiter;

use App\Enums\SessionStatus;
use App\Livewire\Concerns\WatchesKitchen;
use App\Models\Table;
use App\Services\Customer\BillingService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class TableDetail extends Component
{
    use WatchesKitchen;

    #[Locked]
    public int $tableId;

    public int $hotelId;

    public function mount(Table $table): void
    {
        $this->tableId = $table->id;
        $this->hotelId = Auth::user()->hotel_id;
        $this->primeKitchenWatch();
    }

    #[On('echo-private:hotel.{hotelId}.orders,.order.placed')]
    #[On('echo-private:hotel.{hotelId}.orders,.order-item.status-changed')]
    public function onKitchenEvent(): void
    {
        $this->watchKitchen();
    }

    public function render(BillingService $billing)
    {
        // Table's hotel scope makes another hotel's id a 404 for this logged-in waiter.
        $table = Table::findOrFail($this->tableId);

        $session = $table->orderSessions()
            ->whereIn('status', [SessionStatus::Active, SessionStatus::BillRequested])
            ->latest('id')
            ->with('orders.orderItems')
            ->first();

        if (! $session) {
            // Paid / freed while this screen was open.
            $this->redirectRoute('waiter.tables', navigate: false);

            return view('livewire.waiter.table-detail-empty');
        }

        // Attach each order directly (see TableController history): flatMap over item
        // collections is a plain Support\Collection, so ->load() isn't available here.
        $orderItems = $session->orders->flatMap(
            fn ($order) => $order->orderItems->each(fn ($item) => $item->setRelation('order', $order))
        );

        return view('livewire.waiter.table-detail', [
            'table' => $table,
            'orderItems' => $orderItems,
            // Read-only preview — no Bill row created here; assumes no service charge
            // until the customer opts in.
            'billPreview' => $billing->preview($session, false),
            'memberCount' => $session->orders->pluck('member_no')->unique()->count(),
            'sessionStatusLabel' => $session->status === SessionStatus::BillRequested ? 'Bill requested' : 'Active',
        ]);
    }
}
