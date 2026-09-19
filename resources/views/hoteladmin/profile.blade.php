@extends('layouts.admin')

@section('title', 'My Profile — Hotel Admin')
@section('page-title', 'My profile')

@section('content')
<div class="panel">
    <div class="panel-body">
        @if($errors->any())
            <div class="card" style="border-color:var(--red); background:var(--red-tint); color:var(--red); margin-bottom:14px; font-size:12.5px;">
                <ul style="margin:0; padding-left:16px;">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        <form method="POST" action="{{ route('hoteladmin.profile.update') }}">
            @csrf
            @method('PUT')
            <div class="form-grid">
                <div>
                    <label class="flabel">Name</label>
                    <input type="text" name="name" value="{{ old('name', $admin->name) }}">
                </div>
                <div>
                    <label class="flabel">Email</label>
                    <input type="text" name="email" value="{{ old('email', $admin->email) }}">
                </div>
            </div>
            <label class="flabel">Phone</label>
            <input type="text" name="phone" value="{{ old('phone', $admin->phone) }}">
            <label class="flabel">New password (leave blank to keep unchanged)</label>
            <input type="password" name="password">
            <p class="smallmute" style="text-align:left;">Hotel: {{ $admin->hotel->name }}</p>
            <button type="submit" class="btn btn-primary" style="margin-top:18px;">Save changes</button>
        </form>
    </div>
</div>
@endsection
