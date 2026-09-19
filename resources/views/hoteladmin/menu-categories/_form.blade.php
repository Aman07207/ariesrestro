<div class="form-grid">
    <div>
        <label class="flabel">Name</label>
        <input type="text" name="name" value="{{ old('name', $category->name ?? '') }}">
    </div>
    <div>
        <label class="flabel">Type</label>
        <select name="type">
            <option value="food" @selected(old('type', $category->type ?? 'food') === 'food')>Food</option>
            <option value="beverage" @selected(old('type', $category->type ?? '') === 'beverage')>Beverage</option>
        </select>
    </div>
</div>
<label class="flabel">Display order</label>
<input type="number" name="display_order" min="0" value="{{ old('display_order', $category->display_order ?? 0) }}">
