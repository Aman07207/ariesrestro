<div class="brand">
    <div class="logo-dot">A</div>
    <div><div class="btitle">Aries Restro</div><div class="bsub">Super Admin</div></div>
</div>
<nav class="admin-nav">
    <a href="{{ route('superadmin.dashboard') }}" class="{{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
        Dashboard
    </a>
    <div class="navgroup-label">Platform</div>
    <a href="{{ route('superadmin.hotels.index') }}" class="{{ request()->routeIs('superadmin.hotels.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 9h1M9 13h1M14 9h1M14 13h1M9 21v-4h6v4"/></svg>
        Hotels
    </a>
    <a href="{{ route('superadmin.staff.index') }}" class="{{ request()->routeIs('superadmin.staff.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="8" r="4"/><path d="M2 21c1.5-4 4.5-6 7-6s5.5 2 7 6"/><circle cx="18" cy="8" r="3"/><path d="M17 12c2 .3 3.7 1.8 5 5"/></svg>
        Staff (all hotels)
    </a>
    <a href="{{ route('superadmin.subscriptions.index') }}" class="{{ request()->routeIs('superadmin.subscriptions.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18"/></svg>
        Subscriptions
    </a>
    <a href="{{ route('superadmin.payment-settings.index') }}" class="{{ request()->routeIs('superadmin.payment-settings.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
        Payment settings
    </a>
    <a href="{{ route('superadmin.platform-settings') }}" class="{{ request()->routeIs('superadmin.platform-settings') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
        Platform settings
    </a>
    <div class="navgroup-label">All hotels</div>
    <a href="{{ route('superadmin.tables.index') }}" class="{{ request()->routeIs('superadmin.tables.*') || request()->routeIs('superadmin.hotels.tables') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2"/><path d="M4 10h16M10 4v16"/></svg>
        Tables &amp; QR codes
    </a>
    <a href="{{ route('superadmin.sales.index') }}" class="{{ request()->routeIs('superadmin.sales.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-6"/></svg>
        Sales
    </a>
    <a href="{{ route('superadmin.customers.index') }}" class="{{ request()->routeIs('superadmin.customers.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="8" r="4"/><path d="M2 21c1.5-4 4.5-6 7-6s5.5 2 7 6"/><circle cx="18" cy="8" r="3"/><path d="M17 12c2 .3 3.7 1.8 5 5"/></svg>
        Active customers
    </a>
    <div class="navgroup-label">Audit</div>
    <a href="{{ route('superadmin.activity.index') }}" class="{{ request()->routeIs('superadmin.activity.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="9"/></svg>
        Activity log
    </a>
    <a href="{{ route('superadmin.demo-requests.index') }}" class="{{ request()->routeIs('superadmin.demo-requests.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16v16H4z"/><path d="M4 4l8 8 8-8"/></svg>
        Demo requests
    </a>
</nav>
<div class="sidebar-foot">Aries Innovation</div>
