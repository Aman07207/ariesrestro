@extends('layouts.admin')

@section('title', 'Edit Payment Settings — Super Admin')
@section('page-title', 'Edit payment settings')

@section('content')
<div class="panel">
    <div class="panel-body">
        @if($errors->any())
            <div class="card" style="border-color:var(--red); background:var(--red-tint); color:var(--red); margin-bottom:14px; font-size:12.5px;">
                <ul style="margin:0; padding-left:16px;">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        <form method="POST" action="{{ route('superadmin.payment-settings.update', $setting) }}">
            @csrf
            @method('PUT')
            <label class="flabel">Hotel</label>
            <select name="hotel_id">
                @foreach($hotels as $h)
                    <option value="{{ $h->id }}" @selected($setting->hotel_id === $h->id)>{{ $h->name }}</option>
                @endforeach
            </select>
            <label class="flabel">Razorpay linked account ID</label>
            <input type="text" name="razorpay_linked_account_id" value="{{ old('razorpay_linked_account_id', $setting->razorpay_linked_account_id) }}">
            <label class="flabel">Razorpay key ID</label>
            <input type="text" name="razorpay_key_id" placeholder="•••••• leave blank to keep unchanged">
            <label class="flabel">Razorpay key secret</label>
            <input type="text" name="razorpay_key_secret" placeholder="•••••• leave blank to keep unchanged">
            <label class="flabel">Commission (%)</label>
            <input type="number" step="0.01" name="commission_percent" value="{{ old('commission_percent', $setting->commission_percent) }}">
            <button type="submit" class="btn btn-primary" style="margin-top:18px;">Save changes</button>
        </form>
    </div>
</div>
@endsection
