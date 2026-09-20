<div wire:poll.10s="detect">
@if($orderItems->isEmpty())
        <div class="emptystate">
            <div class="em-ic"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg></div>
            <h4>No orders yet</h4>
            <p>Once you place an order it'll show up here with live status.</p>
        </div>
    @else
        <div class="steprow">
            @foreach($stepKeys as $i => $key)
                @php $done = $i <= $overallIdx; @endphp
                <div class="stepcol"><div class="stepdot {{ $done ? 'done' : '' }}">{{ $i + 1 }}</div><div class="steplabel">{{ $steps[$key] }}</div></div>
                @if(!$loop->last)
                    <div class="stepline {{ $i < $overallIdx ? 'done' : '' }}"></div>
                @endif
            @endforeach
        </div>
        <hr class="dash">

        @foreach($orderItems->groupBy('order.member_no') as $memberNumber => $items)
            <div class="memberblock">
                <div class="memberhead">👤 Member {{ $memberNumber }}{{ $memberNumber == $memberNo ? ' (You)' : '' }}</div>
                @foreach($items as $item)
                    <div class="orderitem">
                        <div>
                            <div class="oi-name">{{ $item->name }}</div>
                            <div class="oi-qty">Qty {{ $item->quantity }}{{ $item->note ? ' · '.$item->note : '' }}</div>
                        </div>
                        <span class="badge badge-{{ $item->status->value }}">{{ $item->status->value }}</span>
                    </div>
                @endforeach
            </div>
        @endforeach

        <div class="smallmute">Status updates here live as the kitchen works on your order.</div>
    @endif
</div>
