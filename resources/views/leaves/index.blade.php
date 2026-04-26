@extends('layouts.app')
@section('title', 'Leave Management')
@section('page-title', 'Leave Management')
@section('breadcrumb', 'Leaves · All Requests')

@section('content')

{{-- Stats --}}
<div class="stats-grid-4">
    @foreach([
        ['Pending Approval',    $stats['pending'],        'fa-clock',          'yellow'],
        ['Approved This Month', $stats['approved_month'], 'fa-circle-check',   'green'],
        ['On Leave Today',      $stats['on_leave_today'], 'fa-umbrella-beach', 'blue'],
        ['Rejected This Month', $stats['rejected_month'], 'fa-circle-xmark',   'red'],
    ] as [$label, $val, $icon, $color])
    <div class="stat-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <div class="stat-label">{{ $label }}</div>
            <div class="stat-icon stat-icon-{{ $color }}"><i class="fa-solid {{ $icon }}"></i></div>
        </div>
        <div class="stat-num">{{ $val }}</div>
    </div>
    @endforeach
</div>

{{-- Quick Links --}}
<div class="quick-links">
    <a href="{{ route('leaves.create') }}"   class="quick-link ql-accent"><i class="fa-solid fa-plus"></i> Add Leave Request</a>
    <a href="{{ route('leaves.calendar') }}" class="quick-link ql-blue"><i class="fa-solid fa-calendar-days"></i> Leave Calendar</a>
    <a href="{{ route('leaves.balances') }}" class="quick-link ql-green"><i class="fa-solid fa-chart-bar"></i> Balances</a>
    <a href="{{ route('leaves.report') }}"   class="quick-link ql-purple"><i class="fa-solid fa-file-lines"></i> Report</a>
    <a href="{{ route('leaves.types') }}"    class="quick-link ql-yellow"><i class="fa-solid fa-tags"></i> Leave Types</a>
    <a href="{{ route('leaves.holidays') }}" class="quick-link ql-pink"><i class="fa-solid fa-star"></i> Holidays</a>
</div>

{{-- Filters --}}
<div class="card card-sm" style="margin-bottom:16px;">
    <form method="GET" action="{{ route('leaves.index') }}" class="toolbar">
        <select name="status" class="form-select">
            <option value="">All Status</option>
            @foreach(['pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected','cancelled'=>'Cancelled'] as $v => $l)
                <option value="{{ $v }}" {{ request('status') === $v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>
        <select name="department" class="form-select" style="min-width:150px;">
            <option value="">All Departments</option>
            @foreach($departments as $d)
                <option value="{{ $d->id }}" {{ request('department') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
            @endforeach
        </select>
        <select name="leave_type" class="form-select">
            <option value="">All Types</option>
            @foreach($leaveTypes as $lt)
                <option value="{{ $lt->id }}" {{ request('leave_type') == $lt->id ? 'selected' : '' }}>{{ $lt->name }}</option>
            @endforeach
        </select>
        <input type="month" name="month" value="{{ request('month') }}" class="form-input" style="width:auto;">
        <button type="submit" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-filter"></i> Filter
        </button>
        @if(request()->hasAny(['status','department','leave_type','month']))
            <a href="{{ route('leaves.index') }}" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-xmark"></i> Clear
            </a>
        @endif
    </form>
</div>

{{-- Requests Table --}}
<div class="card card-flush">
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Employee</th>
                <th>Leave Type</th>
                <th>Period</th>
                <th>Days</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Applied On</th>
                <th class="center">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $req)
            @php $badge = $req->status_badge; @endphp
            <tr>
                <td class="muted" style="font-size:11px;">
                    {{ $req->request_number }}
                    @if($req->is_emergency)
                        <div style="margin-top:3px;">
                            <span class="badge-emergency">EMERGENCY</span>
                        </div>
                    @endif
                </td>
                <td>
                    <div class="td-employee">
                        <img src="{{ $req->employee->avatar_url }}" class="avatar avatar-sm">
                        <div>
                            <a href="{{ route('employees.show', $req->employee) }}" class="td-employee name">
                                {{ $req->employee->full_name }}
                            </a>
                            <div style="font-size:10px;color:var(--text-muted);">
                                {{ $req->employee->department?->name ?? '—' }}
                            </div>
                        </div>
                    </div>
                </td>
                <td>
                    <div style="display:flex;align-items:center;gap:7px;">
                        <div style="width:8px;height:8px;border-radius:50%;background:{{ $req->leaveType->color }};flex-shrink:0;"></div>
                        <span style="font-size:12px;color:var(--text-secondary);">{{ $req->leaveType->name }}</span>
                    </div>
                    <div style="font-size:10px;color:var(--text-muted);margin-top:1px;">
                        {{ $req->leaveType->is_paid ? 'Paid' : 'Unpaid' }}
                    </div>
                </td>
                <td class="muted" style="font-size:12px;">
                    {{ $req->from_date->format('d M Y') }}
                    @if(!$req->from_date->eq($req->to_date))
                        <div style="font-size:10px;color:var(--text-muted);">to {{ $req->to_date->format('d M Y') }}</div>
                    @endif
                </td>
                <td>
                    <span style="font-size:13px;font-weight:700;color:var(--accent);">{{ $req->total_days }}</span>
                    <div style="font-size:10px;color:var(--text-muted);">{{ $req->duration_text }}</div>
                </td>
                <td class="muted" style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:12px;"
                    title="{{ $req->reason }}">
                    {{ $req->reason }}
                </td>
                <td>
                    <span class="badge" style="background:{{ $badge['bg'] }};color:{{ $badge['color'] }};border:1px solid {{ $badge['border'] }};">
                        {{ ucfirst($req->status) }}
                    </span>
                </td>
                <td class="muted" style="font-size:11px;">{{ $req->created_at->format('d M Y') }}</td>
                <td class="center">
                    <div style="display:flex;align-items:center;justify-content:center;gap:5px;">
                        <a href="{{ route('leaves.show', $req) }}" class="action-btn" title="View">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                        @if($req->status === 'pending')
                        <form method="POST" action="{{ route('leaves.approve', $req) }}">
                            @csrf
                            <button type="submit" class="action-btn success" title="Approve">
                                <i class="fa-solid fa-check"></i>
                            </button>
                        </form>
                        <button onclick="openRejectModal({{ $req->id }})"
                                class="action-btn danger" title="Reject">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                        @endif
                        @if(in_array($req->status, ['pending','approved']))
                        <form method="POST" action="{{ route('leaves.cancel', $req) }}"
                              onsubmit="return confirm('Cancel this leave request?')">
                            @csrf
                            <button type="submit" class="action-btn" title="Cancel">
                                <i class="fa-solid fa-ban"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9">
                    <div class="empty-state">
                        <i class="fa-solid fa-calendar-xmark"></i>
                        No leave requests found.
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($requests->hasPages())
    <div class="pagination">
        <span class="pagination-info">
            Showing {{ $requests->firstItem() }}–{{ $requests->lastItem() }} of {{ $requests->total() }}
        </span>
        <div class="pagination-btns">
            @if($requests->onFirstPage())
                <span class="page-btn disabled">← Prev</span>
            @else
                <a href="{{ $requests->previousPageUrl() }}" class="page-btn">← Prev</a>
            @endif
            @if($requests->hasMorePages())
                <a href="{{ $requests->nextPageUrl() }}" class="page-btn active">Next →</a>
            @else
                <span class="page-btn disabled">Next →</span>
            @endif
        </div>
    </div>
    @endif
</div>

{{-- Reject Modal --}}
<div id="rejectModal" class="modal-overlay">
    <div class="modal-box" style="border-color:var(--red-border);">
        <div class="modal-title">
            <i class="fa-solid fa-circle-xmark" style="color:var(--red);"></i>
            Reject Leave Request
        </div>
        <form id="rejectForm" method="POST">
            @csrf
            <div style="margin-bottom:16px;">
                <label class="form-label">
                    Reason for Rejection <span style="color:var(--red);">*</span>
                </label>
                <textarea name="rejection_reason" required rows="3"
                          placeholder="Explain why this leave is being rejected…"
                          class="form-textarea"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeRejectModal()">
                    Cancel
                </button>
                <button type="submit" class="btn btn-danger">
                    <i class="fa-solid fa-xmark"></i> Reject
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openRejectModal(id) {
    document.getElementById('rejectForm').action = '/admin/leaves/' + id + '/reject';
    document.getElementById('rejectModal').classList.add('open');
}
function closeRejectModal() {
    document.getElementById('rejectModal').classList.remove('open');
}
document.getElementById('rejectModal').addEventListener('click', function(e) {
    if (e.target === this) closeRejectModal();
});
</script>
@endpush

@endsection