@extends('layouts.admin')

@section('title', 'Edit staff account — Super Admin')
@section('page-title', 'Edit staff account')

@section('content')
<div class="panel">
    <div class="panel-body">
        @if($errors->any())
            <div class="card" style="border-color:var(--red); background:var(--red-tint); color:var(--red); margin-bottom:14px; font-size:12.5px;">
                <ul style="margin:0; padding-left:16px;">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        <form method="POST" action="{{ route('superadmin.staff.update', $staffMember) }}">
            @csrf
            @method('PUT')
            @include('superadmin.staff._form')
            <button type="submit" class="btn btn-primary" style="margin-top:18px;">Save changes</button>
        </form>
    </div>
</div>
@endsection
