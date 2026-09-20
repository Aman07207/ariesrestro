<?php

namespace App\Services\Waiter;

use App\Enums\BillStatus;
use App\Enums\SessionStatus;
use App\Models\Bill;
use App\Models\OrderSession;
use App\Models\Table;
use App\Models\User;
use App\Services\Customer\BillingService;
use Illuminate\Auth\Access\AuthorizationException;
use InvalidArgumentException;

class TableCloseService
{
    public function __construct(private readonly BillingService $billing)
    {
    }

    /** Waiter takes payment at the table (cash / card / UPI) — bill is frozen, table freed. */
    public function settle(Table $table, User $waiter, string $method): Bill
    {
        if (! in_array($method, ['cash', 'card', 'upi', 'razorpay'], true)) {
            throw new InvalidArgumentException('Choose a valid payment mode.');
        }

        $session = $this->openSession($table, $waiter);

        // Keep whatever service-charge choice the customer already made on their bill screen.
        $existing = Bill::where('session_id', $session->id)->where('status', BillStatus::Pending)->first();
        $bill = $this->billing->generateOrUpdate($session, $existing?->service_charge_consent ?? false);

        $this->billing->markPaid($bill, $method);

        return $bill->fresh();
    }

    /** Guests left without paying online / walk-out — ends the session, leaves the bill unpaid. */
    public function closeUnpaid(Table $table, User $waiter): void
    {
        $session = $this->openSession($table, $waiter);

        $this->billing->endSession($session->id, SessionStatus::Closed);
    }

    private function openSession(Table $table, User $waiter): OrderSession
    {
        if ($table->hotel_id !== $waiter->hotel_id) {
            throw new AuthorizationException('That table belongs to another hotel.');
        }

        $session = $table->orderSessions()
            ->whereIn('status', [SessionStatus::Active, SessionStatus::BillRequested])
            ->latest('id')
            ->first();

        if (! $session) {
            throw new InvalidArgumentException('This table has no open session.');
        }

        return $session;
    }
}
