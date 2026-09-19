<div class="form-grid">
    <div>
        <label class="flabel">Hotel</label>
        <select name="hotel_id">
            @foreach($hotels as $h)
                <option value="{{ $h->id }}" @selected(old('hotel_id', $subscription->hotel_id ?? '') == $h->id)>{{ $h->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="flabel">Plan</label>
        <select name="plan">
            @foreach(['standard', 'premium', 'enterprise'] as $plan)
                <option value="{{ $plan }}" @selected(old('plan', $subscription->plan ?? 'standard') === $plan)>{{ ucfirst($plan) }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-grid">
    <div>
        <label class="flabel">Billing cycle</label>
        <select name="billing_cycle">
            <option value="monthly" @selected(old('billing_cycle', $subscription->billing_cycle ?? '') === 'monthly')>Monthly</option>
            <option value="yearly" @selected(old('billing_cycle', $subscription->billing_cycle ?? '') === 'yearly')>Yearly</option>
        </select>
    </div>
    <div>
        <label class="flabel">Price (₹)</label>
        <input type="number" step="0.01" name="price" value="{{ old('price', $subscription->price ?? '') }}">
    </div>
</div>
<div class="form-grid">
    <div>
        <label class="flabel">Start date</label>
        <input type="date" name="start_date" value="{{ old('start_date', optional($subscription->start_date ?? null)->format('Y-m-d')) }}">
    </div>
    <div>
        <label class="flabel">End date</label>
        <input type="date" name="end_date" value="{{ old('end_date', optional($subscription->end_date ?? null)->format('Y-m-d')) }}">
    </div>
</div>
<label class="flabel">Status</label>
<select name="status">
    @foreach(\App\Enums\SubscriptionStatus::cases() as $status)
        <option value="{{ $status->value }}" @selected(old('status', $subscription->status->value ?? 'active') === $status->value)>{{ ucfirst($status->value) }}</option>
    @endforeach
</select>
