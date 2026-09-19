@extends('layouts.admin')

@section('title', 'Tax & Charges — Hotel Admin')
@section('page-title', 'Tax & charges')

@section('content')
<div class="panel" style="max-width:480px;">
    <div class="panel-body">
        <p style="color:var(--navy-soft); font-size:12.5px; margin-bottom:16px;">Food/non-alcoholic items are taxed under GST (split into CGST+SGST on the bill); alcoholic items are taxed separately under state VAT — the two are never combined.</p>
        @if($errors->any())
            <div class="card" style="border-color:var(--red); background:var(--red-tint); color:var(--red); margin-bottom:14px; font-size:12.5px;">
                <ul style="margin:0; padding-left:16px;">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        <form method="POST" action="{{ route('hoteladmin.tax-settings.update') }}">
            @csrf
            @method('PUT')

            <label style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; margin-bottom:14px;">
                <input type="checkbox" name="is_luxury_hotel" value="1" style="width:auto;" @checked($hotel->is_luxury_hotel)>
                This is a luxury hotel with room tariffs over ₹7,500/night
            </label>

            <label class="flabel">GST rate (%)</label>
            <input type="number" step="0.01" name="gst_rate" value="{{ old('gst_rate', $hotel->gst_rate) }}">
            <p class="smallmute" style="text-align:left; margin:4px 0 0;">5% for a standalone restaurant, 18% if the box above is checked. This value is what's actually used to calculate every bill — the checkbox is guidance, not a live override.</p>

            <label class="flabel">State VAT rate on alcohol (%)</label>
            <input type="number" step="0.01" name="vat_rate" value="{{ old('vat_rate', $hotel->vat_rate) }}" placeholder="e.g. 20 — leave blank if you don't serve alcohol">

            <label class="flabel">Default service charge (%)</label>
            <input type="number" step="0.01" name="default_service_charge_percent" value="{{ old('default_service_charge_percent', $hotel->default_service_charge_percent) }}" placeholder="e.g. 10">
            <p class="smallmute" style="text-align:left; margin:4px 0 0;">Shown to customers as a suggestion on their bill screen — by law it's their choice, never added automatically.</p>

            <hr class="dash">

            <label class="flabel">Menu greeting theme</label>
            <select name="weather_theme_mode">
                @foreach($modeIcons as $mode => $icon)
                    <option value="{{ $mode }}" @selected(old('weather_theme_mode', $hotel->weather_theme_mode->value) === $mode)>
                        {{ $icon }} {{ ucfirst($mode) }}{{ $mode === 'auto' ? ' (follow platform default)' : ' — always show this season here' }}
                    </option>
                @endforeach
            </select>
            <p class="smallmute" style="text-align:left; margin:4px 0 0;">Overrides the platform-wide default just for this hotel's customer menu greeting card. Leave on "Auto" unless you specifically want this venue to look different from the rest.</p>

            <button type="submit" class="btn btn-primary" style="margin-top:18px;">Save changes</button>
        </form>
    </div>
</div>
@endsection
