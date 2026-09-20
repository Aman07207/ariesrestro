<div style="display:contents" wire:poll.10s="watchKitchen">
<div class="topbar">
    <div class="topbar-left">
        <a href="{{ route('waiter.tables') }}" class="iconbtn"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 18l-6-6 6-6"/></svg></a>
        <div><h2>Table {{ $table->table_number }}</h2><div class="sub">{{ $memberCount }} member{{ $memberCount != 1 ? 's' : '' }} · {{ $sessionStatusLabel }}</div></div>
    </div>
    <button type="button" class="iconbtn" onclick="openTransferModal()"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 16V4M7 4L3 8M7 4l4 4"/><path d="M17 8v12M17 20l4-4M17 20l-4-4"/></svg></button>
</div>

<div class="content">
    @foreach($orderItems->groupBy('order.member_no') as $memberNumber => $items)
        @php $sub = $items->filter(fn($i) => $i->status !== \App\Enums\OrderItemStatus::Cancelled)->sum(fn($i) => $i->price * $i->quantity); @endphp
        <div class="memberblock">
            <div class="memberhead">Member {{ $memberNumber }} · ₹{{ $sub }}</div>
            @foreach($items as $item)
                <div class="orderitem">
                    <div><div class="oi-name">{{ $item->name }}</div><div class="oi-qty">Qty {{ $item->quantity }}{{ $item->note ? ' · '.$item->note : '' }}</div></div>
                    <div style="display:flex; align-items:center; gap:6px;">
                        <span class="badge badge-{{ $item->status->value }}">{{ $item->status->value }}</span>
                        @if($item->status === \App\Enums\OrderItemStatus::Pending)
                            <button type="button" class="iconbtn" style="width:26px; height:26px;" onclick="openCancelModal({{ $item->id }})"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"/></svg></button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach

    <hr class="dash">
    <div class="billrow"><span>Food subtotal</span><span>₹{{ number_format($billPreview['food_base_amount'], 2) }}</span></div>
    @if($billPreview['alcohol_base_amount'] > 0)
        <div class="billrow"><span>Alcohol subtotal</span><span>₹{{ number_format($billPreview['alcohol_base_amount'], 2) }}</span></div>
    @endif
    <div class="billrow"><span>CGST</span><span>₹{{ number_format($billPreview['cgst_amount'], 2) }}</span></div>
    <div class="billrow"><span>SGST</span><span>₹{{ number_format($billPreview['sgst_amount'], 2) }}</span></div>
    @if($billPreview['alcohol_base_amount'] > 0)
        <div class="billrow"><span>VAT</span><span>₹{{ number_format($billPreview['vat_amount'], 2) }}</span></div>
    @endif
    <div class="smallmute" style="text-align:left; margin:4px 0;">Service charge not included — the customer opts in on their own bill screen.</div>
    <div class="billrow total"><span>Table total (before service charge)</span><span>₹{{ number_format($billPreview['grand_total'], 2) }}</span></div>
    <div wire:ignore>
    <label class="flabel">Payment mode</label>
    <select id="settle-method"><option value="cash">Cash</option><option value="upi">UPI</option><option value="card">Card</option></select>
    <button type="button" class="btn btn-dark" style="margin-top:16px;" onclick="confirmBill()">Confirm bill &amp; mark paid</button>
    <button type="button" class="btn btn-outline" style="margin-top:9px;" onclick="closeTableUnpaid()">Close table without payment</button>
    </div>
</div>
</div>
