<?php

namespace App\Livewire\Waiter;

use App\Enums\WaiterCallStatus;
use App\Models\WaiterCall;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Small always-mounted badge shown on every waiter screen (via
 * partials/bottomnav-waiter and the tables page). Refreshes itself the
 * moment a new call is broadcast, or a call is attended elsewhere on the
 * page, instead of the old ~8s poll.
 */
class CallsBadge extends Component
{
    public int $hotelId;

    public string $extraStyle = '';

    public function mount(string $extraStyle = ''): void
    {
        $this->hotelId = Auth::user()->hotel_id;
        $this->extraStyle = $extraStyle;
    }

    public function render()
    {
        return view('livewire.waiter.calls-badge', [
            'count' => WaiterCall::whereHas('table', fn ($q) => $q->where('hotel_id', $this->hotelId))
                ->where('status', WaiterCallStatus::Pending)
                ->count(),
        ]);
    }

    #[On('echo-private:waiter-calls.{hotelId},.waiter-call.created')]
    public function refreshOnNewCall(): void
    {
        $this->dispatch('waiter-call-received');
    }

    #[On('waiter-call-attended')]
    public function refreshOnAttended(): void
    {
        //
    }
}
