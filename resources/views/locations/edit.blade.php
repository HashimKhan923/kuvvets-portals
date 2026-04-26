@extends('layouts.app')
@section('title', 'Edit ' . $location->name)

@section('content')
<div class="page-wrapper" style="max-width:880px;">
    <div class="page-head">
        <div>
            <a href="{{ route('locations.show', $location) }}" style="font-size:12px;color:var(--muted);text-decoration:none;">
                <i class="fa-solid fa-arrow-left"></i> Back to {{ $location->name }}
            </a>
            <h1 class="page-title" style="margin-top:6px;">Edit Location</h1>
            <p class="page-sub">{{ $location->code }} · {{ $location->name }}</p>
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