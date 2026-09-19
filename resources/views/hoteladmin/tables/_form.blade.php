<div class="form-grid">
    <div>
        <label class="flabel">Table number</label>
        <input type="number" name="table_number" min="1" value="{{ old('table_number', $table->table_number ?? '') }}">
    </div>
    <div>
        <label class="flabel">Seating capacity</label>
        <input type="number" name="seating_capacity" min="1" max="20" value="{{ old('seating_capacity', $table->seating_capacity ?? 4) }}">
    </div>
</div>
<label class="flabel">Status</label>
<select name="status">
    @foreach(\App\Enums\TableStatus::cases() as $status)
        <option value="{{ $status->value }}" @selected(old('status', $table->status->value ?? 'available') === $status->value)>{{ ucfirst(str_replace('_', ' ', $status->value)) }}</option>
    @endforeach
</select>
