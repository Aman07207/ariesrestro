@extends('layouts.marketing')

@section('title', 'Aries Restro — Contactless Table Ordering for Restaurants, Cafés & Hotels')

@section('content')
<nav class="mkt-nav">
    <div class="mkt-brand"><div class="logo-dot">A</div>Aries <span>Restro</span></div>
    <div class="mkt-nav-links">
        <a href="#how-it-works">How it works</a>
        <a href="#features">Features</a>
        <a href="#request-demo">Request a demo</a>
        <a href="{{ route('login') }}" class="btn btn-outline">Staff login</a>
    </div>
</nav>

<section class="mkt-hero">
    <div>
        <div class="mkt-eyebrow">QR table ordering, zero friction</div>
        <h1>Turn every table into a <span>contactless</span> ordering counter</h1>
        <p>Customers scan the QR code on their table, order from their own phone, and pay one combined bill — no app to download, no login, no waiting to flag down a waiter. The order reaches your kitchen and waiter instantly.</p>
        <div class="mkt-hero-ctas">
            <a href="#request-demo" class="btn btn-primary">Request a demo</a>
            <a href="#how-it-works" class="btn btn-outline">See how it works</a>
        </div>
    </div>
    <div class="mkt-hero-art">
        <div class="mock-row"><div class="mock-dot">📷</div><div class="mock-text"><b>Scan</b><span>Table 5 QR code</span></div></div>
        <div class="mock-row"><div class="mock-dot">🍽️</div><div class="mock-text"><b>Order</b><span>Paneer Tikka × 2, Masala Chai × 2</span></div></div>
        <div class="mock-row"><div class="mock-dot">👨‍🍳</div><div class="mock-text"><b>Kitchen notified</b><span>Live in the queue — no relay needed</span></div></div>
        <div class="mock-row"><div class="mock-dot">💳</div><div class="mock-text"><b>Pay</b><span>One combined bill via Razorpay</span></div></div>
    </div>
</section>

<section class="mkt-section" id="how-it-works">
    <div class="mkt-container">
        <div class="mkt-eyebrow">How it works</div>
        <h2>From scan to served, in four steps</h2>
        <p class="lead">No hardware to install, no app for customers to download — just a QR code printed on each table.</p>
        <div class="mkt-steps">
            <div class="mkt-step">
                <div class="num">1</div>
                <h3>Scan</h3>
                <p>Customer points their phone camera at the table's QR code — straight to your live menu, no login.</p>
            </div>
            <div class="mkt-step">
                <div class="num">2</div>
                <h3>Order</h3>
                <p>Each person at the table can order from their own phone — it's all tracked as one shared table session.</p>
            </div>
            <div class="mkt-step">
                <div class="num">3</div>
                <h3>Serve</h3>
                <p>Orders land in your Kitchen queue and Waiter app instantly — no shouting orders across the floor.</p>
            </div>
            <div class="mkt-step">
                <div class="num">4</div>
                <h3>Pay</h3>
                <p>When they're done, the whole table pays one combined bill via Razorpay — split evenly or by item.</p>
            </div>
        </div>
    </div>
</section>

<section class="mkt-section tight" id="features" style="background:var(--bg);">
    <div class="mkt-container">
        <div class="mkt-eyebrow">Built for restaurant owners</div>
        <h2>Everything your floor and kitchen need, in one place</h2>
        <div class="mkt-features">
            <div class="mkt-feature">
                <div class="ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg></div>
                <h3>Live kitchen &amp; waiter apps</h3>
                <p>Orders appear the instant they're placed — the kitchen queue and waiter's table grid update without anyone relaying anything by hand.</p>
            </div>
            <div class="mkt-feature">
                <div class="ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg></div>
                <h3>One combined bill, Razorpay-ready</h3>
                <p>The whole table settles together — no splitting arguments, no separate checks, no cash handling required.</p>
            </div>
            <div class="mkt-feature">
                <div class="ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg></div>
                <h3>Your own admin dashboard</h3>
                <p>Manage your menu, tables, and staff yourself — add items, mark things out of stock, onboard new waiters, all without calling support.</p>
            </div>
            <div class="mkt-feature">
                <div class="ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2"/><path d="M4 10h16M10 4v16"/></svg></div>
                <h3>Print-and-place QR codes</h3>
                <p>Generate a QR code for every table in seconds — print it, place it, done. No hardware, no per-table setup.</p>
            </div>
            <div class="mkt-feature">
                <div class="ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l8 4v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-4z"/></svg></div>
                <h3>Multi-tenant &amp; secure</h3>
                <p>Every hotel's data is fully isolated, staff accounts are role-based, and payment details are encrypted at rest.</p>
            </div>
            <div class="mkt-feature">
                <div class="ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 01-3.4 0"/></svg></div>
                <h3>"Call Waiter," instantly</h3>
                <p>Customers can flag a waiter with one tap — right down to a sound alert on the waiter's own screen.</p>
            </div>
        </div>
    </div>
</section>

<section class="mkt-section" id="request-demo">
    <div class="mkt-container">
        <div class="mkt-demo">
            <div>
                <div class="mkt-eyebrow">Get started</div>
                <h2>See it running on your own menu</h2>
                <p class="lead">Tell us a bit about your restaurant, café, or hotel and we'll set up a live walkthrough — no commitment, no cost.</p>
            </div>
            <div class="mkt-form">
                @if(session('status'))
                    <div class="card" style="border-color:var(--green); background:var(--green-tint); color:var(--green); margin-bottom:14px; font-size:12.5px;">
                        {{ session('status') }}
                    </div>
                @endif
                @if($errors->any())
                    <div class="card" style="border-color:var(--red); background:var(--red-tint); color:var(--red); margin-bottom:14px; font-size:12.5px;">
                        <ul style="margin:0; padding-left:16px;">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('demo-requests.store') }}">
                    @csrf
                    <div class="field">
                        <label>Your name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required>
                    </div>
                    <div class="field">
                        <label>Restaurant / café / hotel name</label>
                        <input type="text" name="hotel_name" value="{{ old('hotel_name') }}" required>
                    </div>
                    <div class="field">
                        <label>Phone</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" required>
                    </div>
                    <div class="field">
                        <label>Email</label>
                        <input type="text" name="email" value="{{ old('email') }}" required>
                    </div>
                    <div class="field">
                        <label>Anything we should know? (optional)</label>
                        <textarea name="message">{{ old('message') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Request a demo</button>
                </form>
            </div>
        </div>
    </div>
</section>

<footer class="mkt-footer">
    <div class="mkt-brand">Aries <span style="color:var(--orange);">Restro</span> — by Aries Innovation</div>
    <div>Contact: <a href="mailto:tech.ariesinnovation@gmail.com">tech.ariesinnovation@gmail.com</a></div>
    <div>&copy; {{ date('Y') }} Aries Innovation. All rights reserved.</div>
</footer>
@endsection
