@extends('layouts.app_mobile')

@section('content')
<div class="bg-white rounded-xl p-4 shadow-sm border">
    <div class="text-sm text-gray-500">Halo</div>
    <div class="text-lg font-semibold">{{ auth()->user()->name }}</div>
    <div class="text-sm text-gray-500 mt-1">
        Role: <span class="font-medium">{{ auth()->user()->role_user }}</span>
    </div>
</div>
@endsection