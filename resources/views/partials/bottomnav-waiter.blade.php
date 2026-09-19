<div class="bottomnav">
    <a href="{{ route('waiter.tables') }}" class="{{ request()->routeIs('waiter.tables*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
        Tables
    </a>
    <a href="{{ route('waiter.manual-order') }}" class="{{ request()->routeIs('waiter.manual-order') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
        New order
    </a>
    <a href="{{ route('waiter.calls') }}" class="{{ request()->routeIs('waiter.calls') ? 'active' : '' }}" style="position:relative">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9"/></svg>
        Calls
        <span class="navbadge" id="waiter-calls-badge" style="display:{{ ($pendingCallCount ?? 0) > 0 ? 'flex' : 'none' }}">{{ $pendingCallCount ?? 0 }}</span>
    </a>
    <a href="{{ route('waiter.profile') }}" class="{{ request()->routeIs('waiter.profile') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c1.8-4 5-6 8-6s6.2 2 8 6"/></svg>
        Profile
    </a>
</div>
