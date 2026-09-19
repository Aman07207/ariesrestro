<?php

namespace App\Livewire\Waiter;

use App\Enums\WaiterCallStatus;
use App\Models\Table;
use App\Models\WaiterCall;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class CallsIndex extends Component
{
    public int $hotelId;

    public function mount(): void
    {
        $this->hotelId = Auth::user()->hotel_id;
    }

    public function render()
    {
        return view('livewire.waiter.calls-index', [
            'calls' => WaiterCall::whereHas('table', fn ($q) => $q->where('hotel_id', $this->hotelId))
                ->with('table')
                ->latest('id')
                ->get(),
        ]);
    }

    #[On('echo-private:waiter-calls.{hotelId},.waiter-call.created')]
    public function refreshOnNewCall(): void
    {
        $this->dispatch('waiter-call-received');
    }

    public function attend(WaiterCall $call): void
    {
        // Table carries a BelongsToHotel global scope, which would silently hide the
        // relation (not just restrict it) for a waiter from a different hotel — that's
        // right for normal queries, but here it would turn a 403 into a crash. Bypass
        // it deliberately, just to read the raw hotel_id for the comparison below.
        $tableHotelId = Table::withoutGlobalScopes()->where('id', $call->table_id)->value('hotel_id');

        abort_unless($tableHotelId === $this->hotelId, 403);

        $call->update(['status' => WaiterCallStatus::Attended, 'attended_by' => Auth::id()]);

        $this->dispatch('waiter-call-attended');
    }
}
