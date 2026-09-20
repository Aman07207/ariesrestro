<label class="flabel">Hotel</label>
<select name="hotel_id">
    @foreach($hotels as $h)
        <option value="{{ $h->id }}" @selected(old('hotel_id', $staffMember->hotel_id ?? '') == $h->id)>{{ $h->name }}</option>
    @endforeach
</select>
<div class="form-grid">
    <div>
        <label class="flabel">Full name</label>
        <input type="text" name="name" value="{{ old('name', $staffMember->name ?? '') }}">
    </div>
    <div>
        <label class="flabel">Employee ID</label>
        <input type="text" name="employee_id" value="{{ old('employee_id', $staffMember->employee_id ?? '') }}">
    </div>
</div>
<div class="form-grid">
    <div>
        <label class="flabel">Email</label>
        <input type="text" name="email" value="{{ old('email', $staffMember->email ?? '') }}">
    </div>
    <div>
        <label class="flabel">Phone</label>
        <input type="text" name="phone" value="{{ old('phone', $staffMember->phone ?? '') }}">
    </div>
</div>
<div class="form-grid">
    <div>
        <label class="flabel">Role</label>
        <select name="role">
            @foreach(['hotel_admin', 'manager', 'waiter', 'chef'] as $roleOption)
                <option value="{{ $roleOption }}" @selected(old('role', $staffMember->role->value ?? 'waiter') === $roleOption)>{{ ucwords(str_replace('_', ' ', $roleOption)) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="flabel">Shift</label>
        <input type="text" name="shift" placeholder="e.g. 10 AM-8 PM" value="{{ old('shift', $staffMember->shift ?? '') }}">
    </div>
</div>
<label class="flabel">Section / station</label>
<input type="text" name="section" placeholder="e.g. Ground floor · Tables 1-8" value="{{ old('section', $staffMember->section ?? '') }}">
<label class="flabel">Password {{ isset($staffMember) ? '(leave blank to keep unchanged)' : '' }}</label>
<input type="password" name="password">
