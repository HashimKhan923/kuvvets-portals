@extends('layouts.app')
@section('title', 'Leave Balances')
@section('page-title', 'Leave Balances')
@section('breadcrumb', 'Leaves · Balances · ' . $year)

@section('content')

{{-- Controls --}}
<div class="card card-sm" style="margin-bottom:18px;">
    <div class="toolbar">
        <form method="GET" action="{{ route('leaves.balances') }}" class="toolbar" style="flex:1;">
            <select name="year" class="form-select" style="width:auto;">
                @for($y = now()->year + 1; $y >= 2022; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <select name="department" class="form-select" style="min-width:150px;">
                <option value="">All Departments</option>
                @foreach($departments as $d)
                    <option value="{{ $d->id }}" {{ request('department') == $d->id ? 'selected' : '' }}>
                        {{ $d->name }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-filter"></i> Filter
            </button>
        </form>
        <form method="POST" action="{{ route('leaves.allocate') }}">
            @csrf
            <input type="hidden" name="year" value="{{ $year }}">
            <button type="submit" class="btn btn-blue btn-sm"
                    onclick="return confirm('Allocate leave balances for {{ $year }}? This will create missing records only.')">
                <i class="fa-solid fa-calendar-plus"></i> Allocate {{ $year }} Balances
            </button>
        </form>
    </div>
</div>

{{-- Balance Table --}}
<div class="card card-flush" style="overflow-x:auto;">
    <table class="data-table" style="min-width:800px;">
        <thead>
            <tr>
                <th style="min-width:200px;">Employee</th>
                @foreach($leaveTypes as $lt)
                <th class="center" style="min-width:100px;">
                    <div style="display:flex;align-items:center;justify-content:center;gap:4px;">
                        <div style="width:7px;height:7px;border-radius:50%;background:{{ $lt->color }};flex-shrink:0;"></div>
                        {{ $lt->code }}
                    </div>
                    <div style="font-size:9px;color:var(--text-muted);font-weight:400;margin-top:1px;">
                        {{ $lt->days_per_year }}d/yr
                    </div>
                </th>
                @endforeach
                <th class="center">Grant Extra</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employees as $emp)
            <tr>
                <td>
                    <div class="td-employee">
                        <img src="{{ $emp->avatar_url }}" class="avatar avatar-sm">
                        <div>
                            <div style="font-size:12px;font-weight:600;color:var(--text-primary);">
                                {{ $emp->full_name }}
                            </div>
                            <div style="font-size:10px;color:var(--accent);">
                                {{ $emp->employee_id }}
                            </div>
                        </div>
                    </div>
                </td>
                @foreach($leaveTypes as $lt)
                @php
                    $bal   = $emp->leaveBalances->firstWhere('leave_type_id', $lt->id);
                    $avail = $bal ? $bal->available    : 0;
                    $used  = $bal ? $bal->used_days    : 0;
                    $total = $bal ? $bal->total_allocated : 0;
                    $pct   = $bal ? $bal->usage_percent  : 0;
                @endphp
                <td class="center">
                    @if($bal)
                    <div style="font-size:14px;font-weight:700;
                                color:{{ $avail > 0 ? 'var(--green)' : 'var(--red)' }};">
                        {{ $avail }}
                    </div>
                    <div style="font-size:10px;color:var(--text-muted);margin-bottom:4px;">
                        {{ $used }}/{{ $total }} used
                    </div>
                    <div class="progress-track" style="width:60px;margin:0 auto;">
                        <div class="progress-fill"
                             style="width:{{ $pct }}%;
                                    background:{{ $pct > 80 ? 'var(--red)' : ($pct > 50 ? 'var(--yellow)' : 'var(--green)') }};">
                        </div>
                    </div>
                    @else
                    <span style="font-size:11px;color:var(--border-strong);">—</span>
                    @endif
                </td>
                @endforeach
                <td class="center">
                    <button onclick="openGrantModal({{ $emp->id }}, '{{ addslashes($emp->full_name) }}')"
                            class="btn btn-secondary btn-xs">
                        <i class="fa-solid fa-plus"></i> Grant
                    </button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ $leaveTypes->count() + 2 }}">
                    <div class="empty-state">No employees found.</div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Grant Extra Days Modal --}}
<div id="grantModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-title">
            <i class="fa-solid fa-calendar-plus"></i> Grant Extra Leave Days
        </div>
        <div id="grantEmpName" style="font-size:12px;color:var(--accent);margin-bottom:14px;font-weight:600;"></div>
        <form method="POST" action="{{ route('leaves.grant-extra') }}">
            @csrf
            <input type="hidden" name="employee_id" id="grantEmpId">
            <input type="hidden" name="year" value="{{ $year }}">
            <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:4px;">
                <div>
                    <label class="form-label">Leave Type</label>
                    <select name="leave_type_id" required class="form-select">
                        @foreach($leaveTypes as $lt)
                            <option value="{{ $lt->id }}">{{ $lt->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Days to Grant</label>
                    <input type="number" name="days" required
                           min="0.5" max="30" step="0.5" value="1"
                           class="form-input">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('grantModal').classList.remove('open')">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">Grant Days</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openGrantModal(id, name) {
    document.getElementById('grantEmpId').value        = id;
    document.getElementById('grantEmpName').textContent = name;
    document.getElementById('grantModal').classList.add('open');
}
document.getElementById('grantModal').addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('open');
});
</script>
@endpush

@endsection