<div class="bottomnav">
    <a href="{{ route('chef.queue') }}" class="{{ request()->routeIs('chef.queue') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
        Queue
    </a>
    <a href="{{ route('chef.stock') }}" class="{{ request()->routeIs('chef.stock') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 9v4M12 17h.01"/></svg>
        Stock
    </a>
    <a href="{{ route('chef.profile') }}" class="{{ request()->routeIs('chef.profile') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c1.8-4 5-6 8-6s6.2 2 8 6"/></svg>
        Profile
    </a>
</div>
