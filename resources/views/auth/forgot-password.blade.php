@extends('layouts.app')

@section('title', 'Forgot Password — Aries Restro')

@section('content')
<form class="loginwrap" method="POST" action="{{ route('password.email') }}">
    @csrf
    <div class="lg">A</div>
    <h2>Forgot password</h2>
    <p>Enter your employee ID or email. Super Admin and Hotel Admin accounts get a reset link — Waiter/Chef/Manager passwords are reset by your Hotel Admin instead.</p>

    @if (session('status'))
        <div class="card" style="border-color:var(--green); background:var(--green-tint); color:var(--green); margin-bottom:14px; font-size:12.5px;">
            {{ session('status') }}
        </div>
    @endif

    <label class="flabel">Employee ID / email</label>
    <input type="text" name="identifier" value="{{ old('identifier') }}" autofocus>
    <button type="submit" class="btn btn-primary" style="margin-top:22px;">Send reset link</button>
    <a href="{{ route('login') }}" class="smallmute" style="display:block;">Back to login</a>
</form>
@endsection
