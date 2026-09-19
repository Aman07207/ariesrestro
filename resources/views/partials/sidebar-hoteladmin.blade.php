<div class="brand">
    <div class="logo-dot">A</div>
    <div><div class="btitle">{{ auth()->user()->hotel->name ?? 'Aries Restro' }}</div><div class="bsub">Hotel Admin</div></div>
</div>
<nav class="admin-nav">
    <a href="{{ route('hoteladmin.dashboard') }}" class="{{ request()->routeIs('hoteladmin.dashboard') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
        Dashboard
    </a>
    <div class="navgroup-label">Manage</div>
    <a href="{{ route('hoteladmin.tables.index') }}" class="{{ request()->routeIs('hoteladmin.tables.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2"/><path d="M4 10h16M10 4v16"/></svg>
        Tables
    </a>
    <a href="{{ route('hoteladmin.menu-categories.index') }}" class="{{ request()->routeIs('hoteladmin.menu-categories.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
        Menu categories
    </a>
    <a href="{{ route('hoteladmin.menu-items.index') }}" class="{{ request()->routeIs('hoteladmin.menu-items.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18M3 8h4M3 13h4M21 3s-3 1-3 6 3 6 3 6M21 3v18"/></svg>
        Menu items
    </a>
    <a href="{{ route('hoteladmin.staff.index') }}" class="{{ request()->routeIs('hoteladmin.staff.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="8" r="4"/><path d="M2 21c1.5-4 4.5-6 7-6s5.5 2 7 6"/><circle cx="18" cy="8" r="3"/><path d="M17 12c2 .3 3.7 1.8 5 5"/></svg>
        Staff
    </a>
    <a href="{{ route('hoteladmin.tax-settings') }}" class="{{ request()->routeIs('hoteladmin.tax-settings') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
        Tax &amp; charges
    </a>
    <div class="navgroup-label">Account</div>
    <a href="{{ route('hoteladmin.profile') }}" class="{{ request()->routeIs('hoteladmin.profile') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c1.8-4 5-6 8-6s6.2 2 8 6"/></svg>
        Profile
    </a>
</nav>
<div class="sidebar-foot">{{ auth()->user()->hotel->name ?? '' }}</div>
