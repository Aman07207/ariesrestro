<?php

namespace App\Livewire\Waiter;

use App\Livewire\Concerns\WatchesKitchen;
use App\Services\Waiter\TableBoardService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class TablesGrid extends Component
{
    use WatchesKitchen;

    public int $hotelId;

    public function mount(): void
    {
        $this->hotelId = Auth::user()->hotel_id;
        $this->primeKitchenWatch();
    }

    #[On('echo-private:hotel.{hotelId}.orders,.order.placed')]
    #[On('echo-private:hotel.{hotelId}.orders,.order-item.status-changed')]
    public function onKitchenEvent(): void
    {
        $this->watchKitchen();
    }

    public function render(TableBoardService $board)
    {
        return view('livewire.waiter.tables-grid', ['tables' => $board->forHotel($this->hotelId)]);
    }
}
