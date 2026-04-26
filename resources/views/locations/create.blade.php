@extends('layouts.app')
@section('title', 'New Location')

@section('content')
<div class="page-wrapper" style="max-width:880px;">
    <div class="page-head">
        <div>
            <a href="{{ route('locations.index') }}" style="font-size:12px;color:var(--muted);text-decoration:none;">
                <i class="fa-solid fa-arrow-left"></i> Back to Locations
            </a>
            <h1 class="page-title" style="margin-top:6px;">New Location</h1>
            <p class="page-sub">Create a check-in point for employee attendance</p>
        </div>
    </div>

    @include('locations._form')
</div>

<style>
    .page-wrapper { padding: 22px 28px; }
    .page-head { margin-bottom: 22px; }
    .page-title { font-family: 'Space Grotesk', sans-serif; font-size: 22px; font-weight: 700; color: var(--text); }
    .page-sub { font-size: 12.5px; color: var(--muted); margin-top: 3px; }
</style>
@endsection