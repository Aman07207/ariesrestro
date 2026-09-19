@extends('layouts.admin')

@section('title', 'Platform Settings — Super Admin')
@section('page-title', 'Platform settings')

@section('content')
<div class="panel" style="max-width:480px;">
    <div class="panel-body">
        <p style="color:var(--navy-soft); font-size:12.5px; margin-bottom:16px;">
            Controls the customer menu's greeting-card theme for every hotel that hasn't set its own override
            (Hotel Admin &rarr; Tax &amp; charges). "Auto" reads real local weather per hotel, refreshed every
            few hours, falling back to the calendar-month season if a hotel has no location set or the weather
            API is unavailable.
        </p>
        @if($errors->any())
            <div class="card" style="border-color:var(--red); background:var(--red-tint); color:var(--red); margin-bottom:14px; font-size:12.5px;">
                <ul style="margin:0; padding-left:16px;">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        <form method="POST" action="{{ route('superadmin.platform-settings.update') }}">
            @csrf
            @method('PUT')

            <label class="flabel">Platform default menu theme</label>
            <select name="weather_theme_mode">
                @foreach($modeIcons as $mode => $icon)
                    <option value="{{ $mode }}" @selected($weatherThemeMode === $mode)>
                        {{ $icon }} {{ ucfirst($mode) }}{{ $mode === 'auto' ? ' (real weather)' : ' — force this season everywhere' }}
                    </option>
                @endforeach
            </select>
            <p class="smallmute" style="text-align:left; margin:4px 0 0;">This is the master switch — it applies platform-wide unless a hotel overrides it for itself.</p>

            <button type="submit" class="btn btn-primary" style="margin-top:18px;">Save changes</button>
        </form>
    </div>
</div>
@endsection
