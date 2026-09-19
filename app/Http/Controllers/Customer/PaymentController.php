<?php

namespace App\Http\Controllers\Customer;

use App\Enums\BillStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Customer\Concerns\ResolvesCustomerSession;
use App\Models\Bill;
use App\Services\Customer\BillingService;

class PaymentController extends Controller
{
    use ResolvesCustomerSession;

    public function __construct(private readonly BillingService $billing)
    {
    }

    public function show()
    {
        return view('customer.pay', [
            'table' => $this->currentTable(),
            'memberNo' => $this->currentMemberNo(),
            'bill' => $this->currentOrPendingBill(),
        ]);
    }

    public function confirm()
    {
        // No real payment gateway wired up yet (Razorpay integration is a documented
        // future phase) — this just freezes the bill the same way a real "payment
        // succeeded" webhook would, so nothing downstream can recompute it anymore.
        $this->billing->markPaid($this->currentOrPendingBill());

        return redirect()->route('customer.success');
    }

    public function success()
    {
        $bill = Bill::where('session_id', $this->currentSession()->id)->where('status', BillStatus::Paid)->latest('id')->first()
            ?? $this->currentOrPendingBill();

        return view('customer.success', [
            'table' => $this->currentTable(),
            'memberNo' => $this->currentMemberNo(),
            'bill' => $bill,
        ]);
    }

    private function currentOrPendingBill(): Bill
    {
        $session = $this->currentSession();
        $existing = Bill::where('session_id', $session->id)->where('status', BillStatus::Pending)->first();

        return $this->billing->generateOrUpdate($session, $existing?->service_charge_consent ?? false);
    }
}
