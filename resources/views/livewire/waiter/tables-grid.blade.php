<div class="tablegrid" wire:poll.10s="watchKitchen">
    @foreach($tables as $t)
        @php
            $cls = $t['colorClass'];
            $label = $cls === 'bill' ? 'Bill requested' : $cls;
        @endphp
        @if($cls === 'available')
            <div class="tabletile available" wire:key="t{{ $t['table']->id }}">
                <div class="tno">Table {{ $t['table']->table_number }}</div>
                <div><div class="tstatus">available</div></div>
            </div>
        @else
            <a href="{{ route('waiter.tables.show', $t['table']) }}" class="tabletile {{ $cls }}" wire:key="t{{ $t['table']->id }}">
                <div class="tno">Table {{ $t['table']->table_number }}</div>
                <div>
                    <div class="tstatus">{{ $label }}</div>
                    <div class="tmembers">{{ $t['memberCount'] }} member{{ $t['memberCount'] != 1 ? 's' : '' }} · {{ $t['itemCount'] }} items</div>
                </div>
            </a>
        @endif
    @endforeach
</div>
