@extends('layouts.app')

@section('title', 'Staff Login — Aries Restro')

@section('content')
<form class="loginwrap" method="POST" action="{{ route('login.store') }}">
    @csrf
    <div class="lg">A</div>
    <h2>Staff login</h2>
    <p>Sign in with your employee ID or email to reach your dashboard.</p>

    @if (session('status'))
        <div class="card" style="border-color:var(--green); background:var(--green-tint); color:var(--green); margin-bottom:14px; font-size:12.5px;">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="card" style="border-color:var(--red); background:var(--red-tint); color:var(--red); margin-bottom:14px; font-size:12.5px;">
            {{ $errors->first() }}
        </div>
    @endif

    <label class="flabel">Employee ID / email</label>
    <input type="text" name="identifier" value="{{ old('identifier') }}" autofocus>
    <label class="flabel">Password</label>
    <input type="password" name="password">
    <button type="submit" class="btn btn-primary" style="margin-top:22px;">Log in</button>
    <a href="{{ route('password.request') }}" class="smallmute" style="display:block;">Forgot password?</a>
</form>
@endsection
