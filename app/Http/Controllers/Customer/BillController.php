<?php

namespace App\Http\Controllers\Customer;

use App\Enums\BillStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Customer\Concerns\ResolvesCustomerSession;
use App\Models\Bill;
use App\Services\Customer\BillingService;
use Illuminate\Http\Request;

class BillController extends Controller
{
    use ResolvesCustomerSession;

    public function __construct(private readonly BillingService $billing)
    {
    }

    public function show()
    {
        $session = $this->currentSession();

        // Reuse whatever consent choice is already on the pending bill (e.g. after the
        // redirect from updateServiceCharge) — only a brand new bill defaults to false.
        // Still recomputed fresh each visit so it reflects any items added/cancelled
        // since it was last generated.
        $existing = Bill::where('session_id', $session->id)->where('status', BillStatus::Pending)->first();
        $consent = $existing?->service_charge_consent ?? false;

        $bill = $this->billing->generateOrUpdate($session, $consent);

        return view('customer.bill', [
            'table' => $this->currentTable(),
            'memberNo' => $this->currentMemberNo(),
            'bill' => $bill,
        ]);
    }

    public function updateServiceCharge(Request $request)
    {
        $this->billing->generateOrUpdate($this->currentSession(), $request->boolean('service_charge_consent'));

        return redirect()->route('customer.bill');
    }
}
