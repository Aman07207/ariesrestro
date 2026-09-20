@php
    $columns = [
        ['status' => 'pending', 'label' => 'Pending', 'color' => '#B5B2AC', 'action' => 'preparing', 'actionLabel' => 'Start preparing'],
        ['status' => 'preparing', 'label' => 'Preparing', 'color' => 'var(--amber)', 'action' => 'ready', 'actionLabel' => 'Mark ready'],
        ['status' => 'ready', 'label' => 'Ready', 'color' => 'var(--green)', 'action' => 'served', 'actionLabel' => 'Mark served'],
    ];
@endphp

<div style="display:contents" wire:poll.10s="detect">
<div class="content" style="padding:14px 0 96px;">
    <div class="kanban">
        @foreach($columns as $col)
            @php $items = $itemsByStatus[$col['status']] ?? collect(); @endphp
            <div class="kancol">
                <h4><i style="background:{{ $col['color'] }}"></i>{{ $col['label'] }} ({{ $items->count() }})</h4>
                @if($items->isEmpty())
                    <div style="font-size:11.5px; color:var(--navy-soft); padding:10px 2px;">Nothing here</div>
                @else
                    @foreach($items as $item)
                        <div class="kancard" wire:key="item-{{ $item->id }}">
                            <div class="kt">Table {{ $item->order->table->table_number }} · Member {{ $item->order->member_no }}</div>
                            <div class="kn">{{ $item->name }}</div>
                            <div class="kq">Qty {{ $item->quantity }}</div>
                            @if($item->note)<div class="ki">"{{ $item->note }}"</div>@endif
                            <button type="button" class="btn btn-sm btn-dark" style="width:100%;" wire:click="advance({{ $item->id }})" wire:loading.attr="disabled" wire:target="advance({{ $item->id }})">{{ $col['actionLabel'] }}</button>
                        </div>
                    @endforeach
                @endif
            </div>
        @endforeach
    </div>
</div>
</div>
