@extends('layouts.app')
@section('title', $employee->full_name . ' — Documents')
@section('page-title', $employee->full_name . ' — Documents')
@section('breadcrumb', 'Documents · Employee · ' . $employee->full_name)

@section('content')

{{-- Employee Header --}}
<div class="card" style="margin-bottom:20px;">
    <div style="display:flex;align-items:center;gap:16px;">
        <img src="{{ $employee->avatar_url }}"
             class="avatar"
             style="width:60px;height:60px;border-radius:50%;object-fit:cover;
                    border:3px solid var(--accent-border);flex-shrink:0;">
        <div style="flex:1;">
            <div style="font-size:18px;font-weight:700;color:var(--text-primary);">
                {{ $employee->full_name }}
            </div>
            <div style="font-size:13px;color:var(--text-muted);margin-top:2px;">
                {{ $employee->employee_id }} ·
                {{ $employee->designation?->title ?? '—' }} ·
                {{ $employee->department?->name ?? '—' }}
            </div>
            <div style="font-size:12px;color:var(--accent);margin-top:2px;font-weight:600;">
                {{ $documents->count() }} document(s) on file
            </div>
        </div>
        <button onclick="document.getElementById('uploadModal').classList.add('open')"
                class="btn btn-primary">
            <i class="fa-solid fa-upload"></i> Upload Document
        </button>
    </div>
</div>

{{-- Documents grouped by category --}}
@php $grouped = $documents->groupBy(fn($d) => $d->category?->name ?? 'Uncategorised'); @endphp

@forelse($grouped as $catName => $docs)
<div class="card card-flush" style="margin-bottom:16px;">
    <div style="padding:12px 18px;background:var(--bg-muted);border-bottom:1px solid var(--border);
                display:flex;align-items:center;gap:10px;">
        <i class="fa-solid fa-folder-open" style="color:var(--accent);"></i>
        <span style="font-size:13px;font-weight:700;color:var(--text-primary);">{{ $catName }}</span>
        <span style="font-size:11px;color:var(--text-muted);">({{ $docs->count() }})</span>
    </div>
    @include('documents._document_table', ['documents' => $docs])
</div>
@empty
<div class="card">
    <div class="empty-state" style="padding:48px;">
        <i class="fa-solid fa-file-lines"></i>
        No documents on file for {{ $employee->full_name }}.
        <button onclick="document.getElementById('uploadModal').classList.add('open')"
                class="btn btn-primary btn-sm" style="margin-top:14px;">
            <i class="fa-solid fa-upload"></i> Upload First Document
        </button>
    </div>
</div>
@endforelse

{{-- Upload Modal with employee pre-selected --}}
@include('documents._upload_modal')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var sel = document.querySelector('select[name="employee_id"]');
    if (sel) sel.value = '{{ $employee->id }}';
});
</script>
@endpush

@endsection