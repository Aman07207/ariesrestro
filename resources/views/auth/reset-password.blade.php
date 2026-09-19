@extends('layouts.app')

@section('title', 'Reset Password — Aries Restro')

@section('content')
<form class="loginwrap" method="POST" action="{{ route('password.update') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <div class="lg">A</div>
    <h2>Reset password</h2>
    <p>Choose a new password for your account.</p>

    @if ($errors->any())
        <div class="card" style="border-color:var(--red); background:var(--red-tint); color:var(--red); margin-bottom:14px; font-size:12.5px;">
            {{ $errors->first() }}
        </div>
    @endif

    <label class="flabel">Email</label>
    <input type="text" name="email" value="{{ old('email', $email) }}">
    <label class="flabel">New password</label>
    <input type="password" name="password">
    <label class="flabel">Confirm new password</label>
    <input type="password" name="password_confirmation">
    <button type="submit" class="btn btn-primary" style="margin-top:22px;">Reset password</button>
</form>
@endsection
