<div class="form-grid">
    <div>
        <label class="flabel">Hotel name</label>
        <input type="text" name="name" value="{{ old('name', $hotel->name ?? '') }}">
    </div>
    <div>
        <label class="flabel">Slug</label>
        <input type="text" name="slug" value="{{ old('slug', $hotel->slug ?? '') }}">
    </div>
</div>
<div class="form-grid">
    <div>
        <label class="flabel">GSTIN</label>
        <input type="text" name="gstin" maxlength="15" style="text-transform:uppercase;" placeholder="22AAAAA0000A1Z5" value="{{ old('gstin', $hotel->gstin ?? '') }}">
    </div>
    <div>
        <label class="flabel">Google review link</label>
        <input type="text" name="google_review_link" placeholder="https://g.page/r/..." value="{{ old('google_review_link', $hotel->google_review_link ?? '') }}">
    </div>
</div>
<label class="flabel">Hotel logo {{ isset($hotel) ? '(leave blank to keep current)' : '' }}</label>
<input type="file" name="logo" accept="image/png,image/jpeg,image/webp">
@isset($hotel)
    @if($hotel->logo)
        <p class="smallmute" style="text-align:left; margin-top:6px;">
            <img src="{{ $hotel->logoUrl() }}" alt="Current logo" style="height:40px; border-radius:8px; vertical-align:middle; margin-right:8px;">
            Current: {{ basename($hotel->logo) }}
        </p>
    @endif
@endisset
<div class="form-grid" style="margin-top:12px;">
    <div>
        <label class="flabel">Owner name</label>
        <input type="text" name="owner_name" value="{{ old('owner_name', $hotel->owner_name ?? '') }}">
    </div>
    <div>
        <label class="flabel">Owner email</label>
        <input type="text" name="owner_email" value="{{ old('owner_email', $hotel->owner_email ?? '') }}">
    </div>
</div>
<div class="form-grid">
    <div>
        <label class="flabel">Owner phone</label>
        <input type="text" name="owner_phone" value="{{ old('owner_phone', $hotel->owner_phone ?? '') }}">
    </div>
    <div>
        <label class="flabel">Subscription plan</label>
        <select name="subscription_plan">
            @foreach(['standard', 'premium', 'enterprise'] as $plan)
                <option value="{{ $plan }}" @selected(old('subscription_plan', $hotel->subscription_plan ?? 'standard') === $plan)>{{ ucfirst($plan) }}</option>
            @endforeach
        </select>
    </div>
</div>
<label class="flabel">Address</label>
<textarea name="address">{{ old('address', $hotel->address ?? '') }}</textarea>

<label class="flabel" style="margin-top:16px;">Tax &amp; charges</label>
<div class="card" style="padding:14px;">
    <label style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; margin-bottom:12px;">
        <input type="checkbox" name="is_luxury_hotel" value="1" style="width:auto;" @checked(old('is_luxury_hotel', $hotel->is_luxury_hotel ?? false))>
        Luxury hotel (room tariffs over ₹7,500/night)
    </label>
    <div class="form-grid">
        <div>
            <label class="flabel">GST rate (%)</label>
            <input type="number" step="0.01" name="gst_rate" value="{{ old('gst_rate', $hotel->gst_rate ?? 5) }}">
        </div>
        <div>
            <label class="flabel">State VAT on alcohol (%)</label>
            <input type="number" step="0.01" name="vat_rate" value="{{ old('vat_rate', $hotel->vat_rate ?? '') }}" placeholder="blank if no alcohol">
        </div>
    </div>
    <label class="flabel">Default service charge (%)</label>
    <input type="number" step="0.01" name="default_service_charge_percent" value="{{ old('default_service_charge_percent', $hotel->default_service_charge_percent ?? '') }}" placeholder="e.g. 10 — customer opts in on their bill, never automatic">
</div>

@isset($hotel)
    <label class="flabel">Status</label>
    <select name="status">
        @foreach(\App\Enums\HotelStatus::cases() as $status)
            <option value="{{ $status->value }}" @selected(old('status', $hotel->status->value) === $status->value)>{{ ucfirst($status->value) }}</option>
        @endforeach
    </select>
@endisset
