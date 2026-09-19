<div class="bottomnav">
    <a href="{{ route('customer.menu') }}" class="{{ request()->routeIs('customer.menu') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
        Menu
    </a>
    <a href="{{ route('customer.cart') }}" class="{{ request()->routeIs('customer.cart') ? 'active' : '' }}" style="position:relative">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6h15l-1.5 9h-12z"/><path d="M6 6L4 3H2"/><circle cx="9" cy="20" r="1.4"/><circle cx="17" cy="20" r="1.4"/></svg>
        Cart
        <span class="navbadge" id="cart-badge" style="display:none">0</span>
    </a>
    <a href="{{ route('customer.track') }}" class="{{ request()->routeIs('customer.track') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
        My order
    </a>
    <button type="button" onclick="openWaiterCallModal()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9"/></svg>
        Waiter
    </button>
</div>
