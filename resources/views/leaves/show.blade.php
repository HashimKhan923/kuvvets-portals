@extends('layouts.app')
@section('title', $leaveRequest->request_number)
@section('page-title', 'Leave Request — ' . $leaveRequest->request_number)
@section('breadcrumb', 'Leaves · ' . $leaveRequest->request_number)

@section('content')
<div style="max-width:680px;">
<div class="card">

    {{-- Employee Header --}}
    <div style="display:flex;align-items:flex-start;justify-content:space-between;
                margin-bottom:24px;padding-bottom:20px;border-bottom:1px solid var(--border);">
        <div style="display:flex;align-items:center;gap:14px;">
            <img src="{{ $leaveRequest->employee->avatar_url }}"
                 class="avatar avatar-lg">
            <div>
                <div style="font-size:16px;font-weight:700;color:var(--text-primary);">
                    {{ $leaveRequest->employee->full_name }}
                </div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">
                    {{ $leaveRequest->employee->designation?->title ?? '—' }} ·
                    {{ $leaveRequest->employee->department?->name ?? '—' }}
                </div>
                <div style="font-size:11px;color:var(--accent);margin-top:2px;">
                    {{ $leaveRequest->employee->employee_id }}
                </div>
            </div>
        </div>
        @php $badge = $leaveRequest->status_badge; @endphp
        <span class="badge" style="background:{{ $badge['bg'] }};color:{{ $badge['color'] }};border:1px solid {{ $badge['border'] }};font-size:12px;padding:5px 14px;">
            {{ ucfirst($leaveRequest->status) }}
        </span>
    </div>

    {{-- Details Grid --}}
    <div class="grid-2" style="margin-bottom:20px;">
        @foreach([
            ['Request No.',  $leaveRequest->request_number],
            ['Leave Type',   $leaveRequest->leaveType->name . ' (' . ($leaveRequest->leaveType->is_paid ? 'Paid' : 'Unpaid') . ')'],
            ['From Date',    $leaveRequest->from_date->format('l, d M Y')],
            ['To Date',      $leaveRequest->to_date->format('l, d M Y')],
            ['Duration',     $leaveRequest->duration_text],
            ['Applied On',   $leaveRequest->created_at->format('d M Y · h:i A')],
        ] as [$l, $v])
        <div class="detail-block">
            <div class="detail-block-label">{{ $l }}</div>
            <div class="detail-block-value">{{ $v }}</div>
        </div>
        @endforeach
    </div>

    {{-- Reason --}}
    <div class="note-block" style="margin-bottom:18px;">
        <div class="note-block-label">REASON</div>
        <div class="note-block-text">{{ $leaveRequest->reason }}</div>
    </div>

    {{-- Contact --}}
    @if($leaveRequest->contact_during_leave)
    <div style="font-size:12px;color:var(--text-secondary);margin-bottom:16px;">
        <i class="fa-solid fa-phone" style="color:var(--accent);margin-right:6px;"></i>
        Contact during leave: {{ $leaveRequest->contact_during_leave }}
    </div>
    @endif

    {{-- Document --}}
    @if($leaveRequest->document_path)
    <div style="margin-bottom:18px;">
        <a href="{{ asset('storage/' . $leaveRequest->document_path) }}"
           target="_blank" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-file-lines"></i> View Supporting Document
        </a>
    </div>
    @endif

    {{-- Review Info --}}
    @if($leaveRequest->reviewed_by)
    <div class="detail-block" style="margin-bottom:18px;">
        <div class="detail-block-label" style="margin-bottom:8px;">REVIEW INFO</div>
        <div style="font-size:12px;color:var(--text-secondary);">
            <i class="fa-solid fa-user-check" style="color:var(--accent);margin-right:6px;"></i>
            Reviewed by <strong>{{ $leaveRequest->reviewer->name }}</strong>
            on {{ $leaveRequest->reviewed_at->format('d M Y · h:i A') }}
        </div>
        @if($leaveRequest->rejection_reason)
        <div class="flash flash-error" style="margin-top:10px;margin-bottom:0;">
            <i class="fa-solid fa-circle-info"></i>
            {{ $leaveRequest->rejection_reason }}
        </div>
        @endif
    </div>
    @endif

    {{-- Approve / Reject Actions --}}
    @if($leaveRequest->status === 'pending')
    <div style="display:flex;gap:10px;padding-top:16px;border-top:1px solid var(--border);">
        <form method="POST" action="{{ route('leaves.approve', $leaveRequest) }}" style="flex:1;">
            @csrf
            <button type="submit" class="btn btn-success" style="width:100%;justify-content:center;">
                <i class="fa-solid fa-check"></i> Approve
            </button>
        </form>
        <button onclick="document.getElementById('rejectInline').style.display='block'"
                class="btn btn-danger" style="flex:1;justify-content:center;">
            <i class="fa-solid fa-xmark"></i> Reject
        </button>
    </div>
    <div id="rejectInline" style="display:none;margin-top:12px;">
        <form method="POST" action="{{ route('leaves.reject', $leaveRequest) }}">
            @csrf
            <textarea name="rejection_reason" required rows="2"
                      placeholder="Reason for rejection (required)…"
                      class="form-textarea" style="margin-bottom:8px;"></textarea>
            <button type="submit" class="btn btn-danger" style="width:100%;justify-content:center;">
                Confirm Rejection
            </button>
        </form>
    </div>
    @endif

</div>
</div>
@endsection