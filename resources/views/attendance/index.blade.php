@extends('layouts.app')
@section('title', 'Attendance')
@section('page-title', 'Daily Attendance Board')
@section('breadcrumb', 'Attendance · ' . $date->format('l, d M Y'))

@section('content')

{{-- Summary Stats --}}
<div class="stats-grid-6">
    @foreach([
        ['Present',  $summary['present'],  'fa-user-check',     'green'],
        ['Absent',   $summary['absent'],   'fa-user-xmark',     'red'],
        ['Late',     $summary['late'],     'fa-user-clock',     'yellow'],
        ['On Leave', $summary['on_leave'], 'fa-umbrella-beach', 'blue'],
        ['Half Day', $summary['half_day'], 'fa-hourglass-half', 'purple'],
        ['Total',    $summary['total'],    'fa-users',          'accent'],
    ] as [$label, $val, $icon, $color])
    <div class="stat-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
            <div class="stat-label">{{ $label }}</div>
            <div class="stat-icon stat-icon-{{ $color }}"><i class="fa-solid {{ $icon }}"></i></div>
        </div>
        <div class="stat-num">{{ $val }}</div>
    </div>
    @endforeach
</div>

{{-- Filter Toolbar --}}
<div class="card card-sm" style="margin-bottom:16px;">
    <div class="toolbar">
        <form method="GET" action="{{ route('attendance.index') }}" class="toolbar" style="flex:1;">
            <input type="date" name="date" value="{{ $date->format('Y-m-d') }}"
                   class="form-input" style="width:auto;">
            <select name="department" class="form-select" style="min-width:150px;">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department') == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }}
                    </option>
                @endforeach
            </select>
            <select name="status" class="form-select">
                <option value="">All Status</option>
                @foreach(['completed'=>'Present','absent'=>'Absent','short_day'=>'Short Day','half_day'=>'Half Day','three_quarter_day'=>'3/4 Day','on_leave'=>'On Leave','work_from_home'=>'WFH'] as $v => $l)
                    <option value="{{ $v }}" {{ request('status') == $v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-filter"></i> Filter
            </button>
        </form>
        @can('attendance.manage')
        <button class="btn btn-secondary"
                onclick="document.getElementById('manualModal').classList.add('open')">
            <i class="fa-solid fa-pen-to-square"></i> Manual Entry
        </button>
        @endcan
    </div>
</div>

{{-- Attendance Records Table --}}
<div class="card card-flush" style="margin-bottom:16px;">
    <div style="padding:12px 20px;border-bottom:1px solid var(--border);">
        <span style="font-size:12px;font-weight:600;color:var(--green);">
            <i class="fa-solid fa-circle-check"></i>
            Present & Recorded — {{ $records->count() }} records
        </span>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Employee</th>
                <th>Department</th>
                <th>Shift</th>
                <th>Check In</th>
                <th>Check Out</th>
                <th>Hours</th>
                <th>OT</th>
                <th>Status</th>
                <th>Source</th>
                @can('attendance.manage')<th></th>@endcan
            </tr>
        </thead>
        <tbody>
            @forelse($records as $att)
            @php $badge = $att->status_badge; @endphp
            <tr>
                <td>
                    <div class="td-employee">
                        <img src="{{ $att->employee->avatar_url }}" class="avatar avatar-sm">
                        <div>
                            <div style="font-size:12px;font-weight:600;color:var(--text-primary);">
                                {{ $att->employee->full_name }}
                            </div>
                            <div style="font-size:10px;color:var(--accent);">
                                {{ $att->employee->employee_id }}
                            </div>
                        </div>
                    </div>
                </td>
                <td class="muted">{{ $att->employee->department?->name ?? '—' }}</td>
                <td class="muted">{{ $att->shift?->name ?? '—' }}</td>
                <td>
                    @if($att->check_in)
                        <span style="font-size:12px;font-weight:600;color:var(--green);">
                            {{ $att->check_in->format('h:i A') }}
                        </span>
                        @if($att->late_minutes > 0)
                        <div style="font-size:10px;color:var(--yellow);">
                            +{{ $att->late_minutes }}m late
                        </div>
                        @endif
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td>
                    @if($att->check_out)
                        <span style="font-size:12px;color:var(--text-secondary);">
                            {{ $att->check_out->format('h:i A') }}
                        </span>
                    @else
                        <span style="font-size:11px;color:var(--accent);">
                            <span class="live-dot"></span> Active
                        </span>
                    @endif
                </td>
                <td class="muted" style="font-size:12px;">{{ $att->working_hours }}</td>
                <td>
                    @if($att->overtime_minutes > 0)
                        <span style="font-size:12px;font-weight:600;color:var(--yellow);">
                            {{ $att->overtime_hours }}
                        </span>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td>
                    <span class="badge" style="background:{{ $badge['bg'] }};color:{{ $badge['color'] }};border:1px solid {{ $badge['border'] }};">
                        {{ ucfirst(str_replace('_', ' ', $att->status)) }}
                    </span>
                    <!-- @if($att->override)
                        <i class="fa-solid fa-pen-to-square"
                           style="font-size:9px;color:var(--text-muted);margin-left:4px;"
                           title="Manually overridden"></i>
                    @endif -->
                </td>
                <td>
                    <span style="font-size:10px;color:var(--text-muted);text-transform:uppercase;">
                        {{ $att->source }}
                    </span>
                </td>
                @can('attendance.manage')
                <td style="text-align:center;">
                    <button type="button"
                            onclick="openEditModal({{ $att->id }}, {{ $att->employee_id }}, '{{ $att->date->format('Y-m-d') }}', '{{ $att->check_in?->format('H:i') }}', '{{ $att->check_out?->format('H:i') }}', '{{ $att->status }}', '{{ addslashes($att->notes ?? '') }}')"
                            style="background:none;border:1px solid var(--border);border-radius:6px;padding:4px 8px;cursor:pointer;color:var(--accent);"
                            title="Edit attendance">
                        <i class="fa-solid fa-pen-to-square" style="font-size:11px;"></i>
                    </button>
                </td>
                @endcan
            </tr>
            @empty
            <tr>
                <td colspan="10">
                    <div class="empty-state">No attendance records for this date.</div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Absentees --}}
@if($absentees->count())
<div class="card card-flush">
    <div style="padding:12px 20px;border-bottom:1px solid var(--border);">
        <span style="font-size:12px;font-weight:600;color:var(--red);">
            <i class="fa-solid fa-circle-xmark"></i>
            Not Checked In — {{ $absentees->count() }} employees
        </span>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1px;background:var(--border);">
        @foreach($absentees as $emp)
        <div style="background:var(--bg-card);padding:12px 16px;display:flex;align-items:center;gap:10px;">
            <img src="{{ $emp->avatar_url }}" class="avatar avatar-sm" style="flex-shrink:0;">
            <div>
                <div style="font-size:12px;font-weight:500;color:var(--text-secondary);">
                    {{ $emp->full_name }}
                </div>
                <div style="font-size:10px;color:var(--text-muted);">
                    {{ $emp->department?->name ?? '—' }}
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Edit Attendance Modal --}}
@can('attendance.manage')
<div id="editModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-title">
            <i class="fa-solid fa-pen-to-square"></i> Edit Attendance Record
        </div>
        <form method="POST" action="" id="editForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="employee_id" id="edit_employee_id">
            <div style="display:flex;flex-direction:column;gap:12px;">
                <div>
                    <label class="form-label">Employee</label>
                    <input type="text" id="edit_employee_name" class="form-input" disabled style="background:var(--cream-warm);">
                </div>
                <div class="grid-2" style="gap:12px;">
                    <div>
                        <label class="form-label">Date</label>
                        <input type="date" name="date" id="edit_date" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Status</label>
                        <select name="status" id="edit_status" required class="form-select">
                            @foreach(['completed'=>'Present','absent'=>'Absent','short_day'=>'Short Day','half_day'=>'Half Day','three_quarter_day'=>'3/4 Day','on_leave'=>'On Leave','work_from_home'=>'Work From Home'] as $v => $l)
                                <option value="{{ $v }}">{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Check In</label>
                        <input type="time" name="check_in" id="edit_check_in" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Check Out</label>
                        <input type="time" name="check_out" id="edit_check_out" class="form-input">
                    </div>
                </div>
                <div>
                    <label class="form-label">Notes</label>
                    <input type="text" name="notes" id="edit_notes" placeholder="Reason for edit…" class="form-input">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('editModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Record</button>
            </div>
        </form>
    </div>
</div>
@endcan

{{-- Manual Entry Modal --}}
@can('attendance.manage')
<div id="manualModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-title">
            <i class="fa-solid fa-pen-to-square"></i> Manual Attendance Entry
        </div>
        <form method="POST" action="{{ route('attendance.store') }}">
            @csrf
            <div style="display:flex;flex-direction:column;gap:12px;">
                <div>
                    <label class="form-label">Employee</label>
                    <select name="employee_id" required class="form-select">
                        <option value="">Select Employee</option>
                        @foreach(\App\Models\Employee::where('company_id', auth()->user()->company_id)->where('employment_status', 'active')->orderBy('first_name')->get() as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->employee_id }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid-2" style="gap:12px;">
                    <div>
                        <label class="form-label">Date</label>
                        <input type="date" name="date" value="{{ $date->format('Y-m-d') }}"
                               class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Status</label>
                        <select name="status" required class="form-select">
                            @foreach(['completed'=>'Present','absent'=>'Absent','short_day'=>'Short Day','half_day'=>'Half Day','three_quarter_day'=>'3/4 Day','on_leave'=>'On Leave','work_from_home'=>'Work From Home'] as $v => $l)
                                <option value="{{ $v }}">{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Check In</label>
                        <input type="time" name="check_in" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Check Out</label>
                        <input type="time" name="check_out" class="form-input">
                    </div>
                </div>
                <div>
                    <label class="form-label">Notes</label>
                    <input type="text" name="notes"
                           placeholder="Reason for manual entry…" class="form-input">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('manualModal').classList.remove('open')">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">Save Record</button>
            </div>
        </form>
    </div>
</div>
@endcan

@push('scripts')
<script>
var manualModal = document.getElementById('manualModal');
if (manualModal) {
    manualModal.addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('open');
    });
}

var editModal = document.getElementById('editModal');
if (editModal) {
    editModal.addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('open');
    });
}

function openEditModal(id, employeeId, date, checkIn, checkOut, status, notes) {
    var baseUrl = '{{ route("attendance.update", ["attendance" => "ATTENDANCE_ID"]) }}'.replace('ATTENDANCE_ID', id);
    document.getElementById('editForm').action = baseUrl;
    document.getElementById('edit_employee_id').value = employeeId;
    document.getElementById('edit_date').value = date;
    document.getElementById('edit_check_in').value = checkIn || '';
    document.getElementById('edit_check_out').value = checkOut || '';
    document.getElementById('edit_status').value = status;
    document.getElementById('edit_notes').value = notes || '';

    // Look up employee name from the row
    var btn = event.currentTarget;
    var row = btn.closest('tr');
    if (row) {
        var nameEl = row.querySelector('.td-employee div div:first-child');
        document.getElementById('edit_employee_name').value = nameEl ? nameEl.textContent.trim() : '';
    }

    document.getElementById('editModal').classList.add('open');
}
</script>
@endpush

@endsection