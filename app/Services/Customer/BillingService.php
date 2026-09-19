<?php

namespace App\Services\Customer;

use App\Enums\BillStatus;
use App\Enums\OrderItemStatus;
use App\Enums\TaxTrack;
use App\Models\Bill;
use App\Models\OrderItem;
use App\Models\OrderSession;
use Illuminate\Auth\Access\AuthorizationException;

/**
 * The billing engine. Every figure here is legally load-bearing — see the
 * calculation order comment on preview() before touching anything.
 */
class BillingService
{
    /**
     * @return array{food_base_amount: float, alcohol_base_amount: float, service_charge_percent: float,
     *     service_charge_amount: float, service_charge_consent: bool, gst_rate: float, cgst_amount: float,
     *     sgst_amount: float, vat_rate: ?float, vat_amount: float, grand_total: float}
     *
     * Calculation order (do not reorder):
     * 1. Split the base total by tax track — food_base (GST) vs alcohol_base (VAT).
     * 2. Service charge — on food_base + alcohol_base combined, ONLY if consented, else 0.
     * 3. Food GST — food_base x hotel.gst_rate only. Never (food_base + service_charge) x
     *    gst_rate — GST must never be calculated on top of the service charge.
     * 4. Alcohol VAT — alcohol_base x hotel.vat_rate only.
     * 5. Grand total = food_base + alcohol_base + service_charge + (cgst+sgst) + vat.
     */
    public function preview(OrderSession $session, bool $serviceChargeConsent): array
    {
        $hotel = $session->hotel;

        [$foodBase, $alcoholBase] = $this->baseAmounts($session);

        $subtotal = $foodBase + $alcoholBase;

        $serviceChargePercent = $serviceChargeConsent ? (float) ($hotel->default_service_charge_percent ?? 0) : 0.0;
        $serviceChargeAmount = $serviceChargeConsent ? round($subtotal * $serviceChargePercent / 100, 2) : 0.0;

        $gstRate = (float) $hotel->gst_rate;
        $foodGst = round($foodBase * $gstRate / 100, 2);
        $cgst = round($foodGst / 2, 2);
        $sgst = round($foodGst - $cgst, 2); // avoids a rounding-halves mismatch, still "exactly half" for any sane input

        $vatRate = $hotel->vat_rate !== null ? (float) $hotel->vat_rate : null;
        $vatAmount = $vatRate !== null ? round($alcoholBase * $vatRate / 100, 2) : 0.0;

        $grandTotal = $foodBase + $alcoholBase + $serviceChargeAmount + $cgst + $sgst + $vatAmount;

        return [
            'food_base_amount' => round($foodBase, 2),
            'alcohol_base_amount' => round($alcoholBase, 2),
            'service_charge_percent' => $serviceChargePercent,
            'service_charge_amount' => $serviceChargeAmount,
            'service_charge_consent' => $serviceChargeConsent,
            'gst_rate' => $gstRate,
            'cgst_amount' => $cgst,
            'sgst_amount' => $sgst,
            'vat_rate' => $vatRate,
            'vat_amount' => $vatAmount,
            'grand_total' => round($grandTotal, 2),
        ];
    }

    /**
     * Creates or updates the session's pending Bill snapshot. Re-editable (e.g. the
     * customer toggling the service-charge checkbox) right up until it's marked paid —
     * only a paid bill is frozen against further recomputation.
     */
    public function generateOrUpdate(OrderSession $session, bool $serviceChargeConsent): Bill
    {
        $existing = Bill::where('session_id', $session->id)->where('status', BillStatus::Pending)->first();

        if ($existing === null) {
            $paid = Bill::where('session_id', $session->id)->where('status', BillStatus::Paid)->exists();
            if ($paid) {
                throw new AuthorizationException('This table has already been billed and paid.');
            }
        }

        $figures = $this->preview($session, $serviceChargeConsent);

        return Bill::updateOrCreate(
            ['session_id' => $session->id, 'status' => BillStatus::Pending],
            array_merge($figures, ['hotel_id' => $session->hotel_id])
        );
    }

    public function markPaid(Bill $bill): void
    {
        if ($bill->status === BillStatus::Paid) {
            return;
        }

        $bill->update(['status' => BillStatus::Paid]);
    }

    /** @return array{0: float, 1: float} [foodBase, alcoholBase] */
    private function baseAmounts(OrderSession $session): array
    {
        $items = OrderItem::whereHas('order', fn ($q) => $q->where('session_id', $session->id))
            ->where('status', '!=', OrderItemStatus::Cancelled)
            ->with('menuItem')
            ->get();

        $foodBase = 0;
        $alcoholBase = 0;

        foreach ($items as $item) {
            $lineTotal = (float) $item->price * $item->quantity;
            if ($item->menuItem?->tax_track === TaxTrack::Vat) {
                $alcoholBase += $lineTotal;
            } else {
                $foodBase += $lineTotal;
            }
        }

        return [$foodBase, $alcoholBase];
    }
}
