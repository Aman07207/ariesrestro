@extends('layouts.admin')

@section('title', 'Add Payment Settings — Super Admin')
@section('page-title', 'Add payment settings')

@section('content')
<div class="panel">
    <div class="panel-body">
        @if($errors->any())
            <div class="card" style="border-color:var(--red); background:var(--red-tint); color:var(--red); margin-bottom:14px; font-size:12.5px;">
                <ul style="margin:0; padding-left:16px;">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        <form method="POST" action="{{ route('superadmin.payment-settings.store') }}">
            @csrf
            <label class="flabel">Hotel</label>
            <select name="hotel_id">
                @forelse($hotels as $h)
                    <option value="{{ $h->id }}">{{ $h->name }}</option>
                @empty
                    <option value="">All hotels already have payment settings</option>
                @endforelse
            </select>
            <label class="flabel">Razorpay linked account ID</label>
            <input type="text" name="razorpay_linked_account_id" value="{{ old('razorpay_linked_account_id') }}">
            <label class="flabel">Razorpay key ID</label>
            <input type="text" name="razorpay_key_id" value="{{ old('razorpay_key_id') }}">
            <label class="flabel">Razorpay key secret</label>
            <input type="text" name="razorpay_key_secret" value="{{ old('razorpay_key_secret') }}">
            <label class="flabel">Commission (%)</label>
            <input type="number" step="0.01" name="commission_percent" value="{{ old('commission_percent', 0) }}">
            <button type="submit" class="btn btn-primary" style="margin-top:18px;">Save settings</button>
        </form>
    </div>
</div>
@endsection
