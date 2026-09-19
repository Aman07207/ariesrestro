<div class="form-grid">
    <div>
        <label class="flabel">Name</label>
        <input type="text" name="name" value="{{ old('name', $item->name ?? '') }}">
    </div>
    <div>
        <label class="flabel">Category</label>
        <select name="category_id">
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" @selected(old('category_id', $item->category_id ?? '') == $cat->id)>{{ $cat->name }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-grid">
    <div>
        <label class="flabel">Price (₹)</label>
        <input type="number" step="0.01" name="price" value="{{ old('price', $item->price ?? '') }}">
    </div>
    <div>
        <label class="flabel">Veg type</label>
        <select name="veg_type">
            <option value="veg" @selected(old('veg_type', $item->veg_type ?? 'veg') === 'veg')>Veg</option>
            <option value="non-veg" @selected(old('veg_type', $item->veg_type ?? '') === 'non-veg')>Non-veg</option>
        </select>
    </div>
</div>
<label class="flabel">Tax track</label>
<select name="tax_track">
    <option value="gst" @selected(old('tax_track', $item->tax_track?->value ?? 'gst') === 'gst')>GST — food / non-alcoholic (water, juice, tea, coffee, soft drinks...)</option>
    <option value="vat" @selected(old('tax_track', $item->tax_track?->value ?? '') === 'vat')>VAT — alcoholic (beer, wine, whiskey...)</option>
</select>
<label class="flabel">Photo {{ isset($item) ? '(leave blank to keep current)' : '' }}</label>
<input type="file" name="image" accept="image/png,image/jpeg,image/webp">
@isset($item)
    @if($item->image)
        <p class="smallmute" style="text-align:left; margin-top:6px;">Current: {{ basename($item->image) }}</p>
    @endif
@endisset

<div class="form-grid" style="margin-top:12px;">
    <label style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600;">
        <input type="checkbox" name="is_available" value="1" style="width:auto;" @checked(old('is_available', $item->is_available ?? true))>
        Available
    </label>
    <label style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600;">
        <input type="checkbox" name="is_popular" value="1" style="width:auto;" @checked(old('is_popular', $item->is_popular ?? false))>
        Mark as popular
    </label>
</div>
