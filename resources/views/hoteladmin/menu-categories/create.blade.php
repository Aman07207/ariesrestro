@extends('layouts.admin')

@section('title', 'Add Category — Hotel Admin')
@section('page-title', 'Add menu category')

@section('content')
<div class="panel">
    <div class="panel-body">
        @if($errors->any())
            <div class="card" style="border-color:var(--red); background:var(--red-tint); color:var(--red); margin-bottom:14px; font-size:12.5px;">
                <ul style="margin:0; padding-left:16px;">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        <form method="POST" action="{{ route('hoteladmin.menu-categories.store') }}">
            @csrf
            @include('hoteladmin.menu-categories._form')
            <button type="submit" class="btn btn-primary" style="margin-top:18px;">Add category</button>
        </form>
    </div>
</div>
@endsection
