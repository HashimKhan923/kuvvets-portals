@extends('layouts.app')
@section('title', 'Shift Assignment')
@section('page-title', 'Shift Assignment')
@section('breadcrumb', 'Attendance · Shift Assignment')

@section('content')

{{-- Stats --}}
<div class="stats-grid-4">
    @foreach([
        ['Total Employees', $stats['total'],       'fa-users',       'blue'],
        ['Assigned',        $stats['assigned'],    'fa-circle-check','green'],
        ['Unassigned',      $stats['unassigned'],  'fa-circle-xmark','red'],
        ['Total Shifts',    $stats['total_shifts'],'fa-clock',       'accent'],
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

{{-- Tabs --}}
<div class="tab-nav">
    <button type="button" class="tab-btn" id="satab-roster"  onclick="switchSATab('roster')">
        <i class="fa-solid fa-table-cells"></i> Shift Roster
    </button>
    <button type="button" class="tab-btn" id="satab-assign"  onclick="switchSATab('assign')">
        <i class="fa-solid fa-user-check"></i> Assign Shifts
    </button>
    <button type="button" class="tab-btn" id="satab-bulk"    onclick="switchSATab('bulk')">
        <i class="fa-solid fa-users-gear"></i> Bulk Assign
    </button>
    <button type="button" class="tab-btn" id="satab-unassigned" onclick="switchSATab('unassigned')">
        <i class="fa-solid fa-circle-exclamation"></i>
        Unassigned
        @if($stats['unassigned'] > 0)
        <span style="background:var(--red);color:#fff;border-radius:20px;padding:1px 6px;font-size:10px;margin-left:4px;">
            {{ $stats['unassigned'] }}
        </span>
        @endif
    </button>
</div>

{{-- ═══ ROSTER TAB ════════════════════════════════════════════════════════ --}}
<div id="sapane-roster">
    <div class="card card-sm" style="margin-bottom:16px;">
        <div class="toolbar">
            <form method="GET" action="{{ route('attendance.shift-assignment') }}" class="toolbar" style="flex:1;">
                <select name="shift_filter" class="form-select" style="min-width:180px;">
                    <option value="">All Shifts</option>
                    @foreach($shifts as $shift)
                    <option value="{{ $shift->id }}" {{ request('shift_filter') == $shift->id ? 'selected' : '' }}>
                        {{ $shift->name }}
                    </option>
                    @endforeach
                </select>
                <select name="dept_filter" class="form-select" style="min-width:160px;">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('dept_filter') == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }}
                    </option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
            </form>
        </div>
    </div>

    <div class="card card-flush">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Current Shift</th>
                    <th>Timing</th>
                    <th>Working Days</th>
                    <th>Effective From</th>
                    <th class="center">Change</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                @php $shift = $employee->shift; @endphp
                <tr>
                    <td>
                        <div class="td-employee">
                            <img src="{{ $employee->avatar_url }}" class="avatar avatar-sm">
                            <div>
                                <div class="td-employee name">{{ $employee->full_name }}</div>
                                <div class="td-employee id">{{ $employee->employee_id }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="muted">{{ $employee->department?->name ?? '—' }}</td>
                    <td>
                        @if($shift)
                        <div style="display:flex;align-items:center;gap:7px;">
                            <div style="width:8px;height:8px;border-radius:50%;background:var(--green);flex-shrink:0;"></div>
                            <span style="font-size:13px;font-weight:600;color:var(--text-primary);">
                                {{ $shift->name }}
                            </span>
                        </div>
                        @else
                        <div style="display:flex;align-items:center;gap:7px;">
                            <div style="width:8px;height:8px;border-radius:50%;background:var(--red);flex-shrink:0;"></div>
                            <span style="font-size:12px;color:var(--text-muted);">Not Assigned</span>
                        </div>
                        @endif
                    </td>
                    <td class="muted" style="font-size:12px;">
                        {{ $shift?->timing ?? '—' }}
                    </td>
                    <td>
                        @if($shift && $shift->working_days)
                        <div style="display:flex;gap:3px;flex-wrap:wrap;">
                            @foreach($shift->working_days as $day)
                            <span style="font-size:9px;background:var(--accent-bg);color:var(--accent);
                                         border:1px solid var(--accent-border);border-radius:3px;padding:1px 5px;">
                                {{ $day }}
                            </span>
                            @endforeach
                        </div>
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="muted" style="font-size:12px;">
                        {{ $employee->shift_effective_from?->format('d M Y') ?? '—' }}
                    </td>
                    <td class="center">
                        <button onclick="openAssignModal(
                                    {{ $employee->id }},
                                    '{{ addslashes($employee->full_name) }}',
                                    '{{ $employee->employee_id }}',
                                    {{ $employee->shift_id ?? 'null' }}
                                )"
                                class="btn btn-secondary btn-xs">
                            <i class="fa-solid fa-pen"></i>
                            {{ $shift ? 'Change' : 'Assign' }}
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <i class="fa-solid fa-users"></i>
                            No employees found.
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($employees->hasPages())
        <div class="pagination">
            <span class="pagination-info">
                Showing {{ $employees->firstItem() }}–{{ $employees->lastItem() }} of {{ $employees->total() }}
            </span>
            <div class="pagination-btns">
                @if($employees->onFirstPage())
                    <span class="page-btn disabled">← Prev</span>
                @else
                    <a href="{{ $employees->previousPageUrl() }}" class="page-btn">← Prev</a>
                @endif
                @if($employees->hasMorePages())
                    <a href="{{ $employees->nextPageUrl() }}" class="page-btn active">Next →</a>
                @else
                    <span class="page-btn disabled">Next →</span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

{{-- ═══ ASSIGN TAB ════════════════════════════════════════════════════════ --}}
<div id="sapane-assign" style="display:none;">
    <div style="max-width:600px;">
        <div class="card">
            <div class="form-section">
                <i class="fa-solid fa-user-check"></i> Assign Shift to Employee
            </div>
            <form method="POST" action="{{ route('attendance.shift-assignment.store') }}">
                @csrf
                <div style="display:flex;flex-direction:column;gap:14px;">

                    <div>
                        <label class="form-label">Employee <span style="color:var(--red);">*</span></label>
                        <select name="employee_id" required class="form-select" id="single-emp-select">
                            <option value="">Select Employee</option>
                            @foreach($allEmployees as $emp)
                            <option value="{{ $emp->id }}"
                                    data-shift="{{ $emp->shift_id }}"
                                    data-shift-name="{{ $emp->shift?->name ?? 'Not Assigned' }}">
                                {{ $emp->full_name }}
                                ({{ $emp->department?->name ?? 'No dept' }})
                                — {{ $emp->shift?->name ?? 'No shift' }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Current shift info --}}
                    <div id="current-shift-info" style="display:none;background:var(--bg-muted);
                         border:1px solid var(--border);border-radius:8px;padding:12px 14px;">
                        <div style="font-size:11px;color:var(--text-muted);margin-bottom:3px;">Current Shift</div>
                        <div id="current-shift-name" style="font-size:14px;font-weight:600;color:var(--text-primary);"></div>
                    </div>

                    <div>
                        <label class="form-label">New Shift <span style="color:var(--red);">*</span></label>
                        <select name="shift_id" required class="form-select">
                            <option value="">Select Shift</option>
                            @foreach($shifts as $shift)
                            <option value="{{ $shift->id }}">
                                {{ $shift->name }} — {{ $shift->timing }}
                                ({{ implode(', ', $shift->working_days ?? []) }})
                            </option>
                            @endforeach
                            <option value="none">— Remove Shift Assignment —</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Effective From <span style="color:var(--red);">*</span></label>
                        <input type="date" name="effective_from" required
                               value="{{ today()->format('Y-m-d') }}" class="form-input">
                    </div>

                    <div>
                        <label class="form-label">Reason / Notes</label>
                        <input type="text" name="notes"
                               placeholder="e.g. Transferred to night operations"
                               class="form-input">
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Save Shift Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ═══ BULK ASSIGN TAB ═══════════════════════════════════════════════════ --}}
<div id="sapane-bulk" style="display:none;">
    <div class="card">
        <div class="form-section">
            <i class="fa-solid fa-users-gear"></i> Bulk Shift Assignment
        </div>
        <form method="POST" action="{{ route('attendance.shift-assignment.bulk') }}">
            @csrf

            {{-- Step 1: Choose criteria --}}
            <div style="background:var(--bg-muted);border-radius:10px;padding:16px;margin-bottom:16px;">
                <div class="section-label" style="margin-bottom:12px;">Step 1 — Filter Employees</div>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
                    <div>
                        <label class="form-label">Department</label>
                        <select name="bulk_department_id" id="bulk-dept" class="form-select"
                                onchange="filterBulkEmployees()">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Currently On Shift</label>
                        <select name="bulk_current_shift" id="bulk-current-shift" class="form-select"
                                onchange="filterBulkEmployees()">
                            <option value="">Any Shift</option>
                            <option value="none">No Shift (Unassigned)</option>
                            @foreach($shifts as $shift)
                            <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Employment Type</label>
                        <select name="bulk_employment_type" id="bulk-emp-type" class="form-select"
                                onchange="filterBulkEmployees()">
                            <option value="">All Types</option>
                            @foreach(['permanent'=>'Permanent','contract'=>'Contract','probationary'=>'Probationary','part_time'=>'Part Time'] as $v => $l)
                            <option value="{{ $v }}">{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Step 2: Pick employees --}}
            <div style="background:var(--bg-muted);border-radius:10px;padding:16px;margin-bottom:16px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                    <div class="section-label" style="margin-bottom:0;">Step 2 — Select Employees</div>
                    <div style="display:flex;gap:8px;">
                        <button type="button" class="btn btn-success btn-sm"
                                onclick="selectAllBulk(true)">
                            <i class="fa-solid fa-check-double"></i> Select All
                        </button>
                        <button type="button" class="btn btn-danger btn-sm"
                                onclick="selectAllBulk(false)">
                            <i class="fa-solid fa-xmark"></i> Deselect All
                        </button>
                        <span id="bulk-selected-count"
                              style="font-size:12px;color:var(--text-muted);padding:5px 0;"></span>
                    </div>
                </div>

                <div id="bulk-employee-list"
                     style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));
                            gap:8px;max-height:360px;overflow-y:auto;">
                    @foreach($allEmployees as $emp)
                    <label class="bulk-emp-row"
                           data-dept="{{ $emp->department_id }}"
                           data-shift="{{ $emp->shift_id ?? 'none' }}"
                           data-type="{{ $emp->employment_type }}"
                           style="display:flex;align-items:center;gap:10px;cursor:pointer;
                                  padding:10px 12px;background:var(--bg-card);
                                  border:1px solid var(--border);border-radius:8px;
                                  transition:border-color .15s;"
                           onmouseover="this.style.borderColor='var(--accent-border)'"
                           onmouseout="this.style.borderColor=this.querySelector('input').checked?'var(--accent)':'var(--border)'">
                        <input type="checkbox" name="employee_ids[]" value="{{ $emp->id }}"
                               class="bulk-chk"
                               style="accent-color:var(--accent);width:15px;height:15px;flex-shrink:0;"
                               onchange="updateBulkCount();this.closest('label').style.borderColor=this.checked?'var(--accent)':'var(--border)'">
                        <img src="{{ $emp->avatar_url }}"
                             class="avatar avatar-sm" style="flex-shrink:0;">
                        <div style="min-width:0;">
                            <div style="font-size:12px;font-weight:600;color:var(--text-primary);
                                        overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                {{ $emp->full_name }}
                            </div>
                            <div style="font-size:10px;color:var(--text-muted);">
                                {{ $emp->department?->name ?? 'No dept' }}
                                @if($emp->shift)
                                · <span style="color:var(--green);">{{ $emp->shift->name }}</span>
                                @else
                                · <span style="color:var(--red);">No shift</span>
                                @endif
                            </div>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Step 3: Choose shift --}}
            <div style="background:var(--bg-muted);border-radius:10px;padding:16px;margin-bottom:16px;">
                <div class="section-label" style="margin-bottom:12px;">Step 3 — Choose Shift & Date</div>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;">
                    <div>
                        <label class="form-label">Assign Shift <span style="color:var(--red);">*</span></label>
                        <select name="shift_id" required class="form-select">
                            <option value="">Select Shift</option>
                            @foreach($shifts as $shift)
                            <option value="{{ $shift->id }}">
                                {{ $shift->name }} — {{ $shift->timing }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Effective From <span style="color:var(--red);">*</span></label>
                        <input type="date" name="effective_from" required
                               value="{{ today()->format('Y-m-d') }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Notes</label>
                        <input type="text" name="notes"
                               placeholder="e.g. Ramadan schedule" class="form-input">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary"
                    onclick="return confirmBulkAssign()">
                <i class="fa-solid fa-users-gear"></i> Apply Bulk Assignment
            </button>

        </form>
    </div>
</div>

{{-- ═══ UNASSIGNED TAB ════════════════════════════════════════════════════ --}}
<div id="sapane-unassigned" style="display:none;">
    @if($unassignedEmployees->count())
    <div class="flash flash-warning" style="margin-bottom:16px;">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <strong>{{ $unassignedEmployees->count() }} employee(s)</strong> have no shift assigned.
        Attendance tracking may be inaccurate for these employees.
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px;">
        @foreach($unassignedEmployees as $emp)
        <div class="card" style="border-left:3px solid var(--red);">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                <img src="{{ $emp->avatar_url }}"
                     class="avatar" style="width:44px;height:44px;border-radius:50%;object-fit:cover;
                                           border:2px solid var(--red);flex-shrink:0;">
                <div>
                    <div style="font-size:13px;font-weight:700;color:var(--text-primary);">
                        {{ $emp->full_name }}
                    </div>
                    <div style="font-size:11px;color:var(--text-muted);">
                        {{ $emp->employee_id }} · {{ $emp->department?->name ?? '—' }}
                    </div>
                    <div style="font-size:11px;color:var(--text-muted);">
                        Joined: {{ $emp->joining_date?->format('d M Y') ?? '—' }}
                    </div>
                </div>
            </div>
            <button onclick="openAssignModal(
                        {{ $emp->id }},
                        '{{ addslashes($emp->full_name) }}',
                        '{{ $emp->employee_id }}',
                        null
                    )"
                    class="btn btn-primary" style="width:100%;justify-content:center;">
                <i class="fa-solid fa-clock"></i> Assign Shift Now
            </button>
        </div>
        @endforeach
    </div>
    @else
    <div class="card">
        <div class="empty-state" style="padding:48px;">
            <i class="fa-solid fa-circle-check" style="color:var(--green);"></i>
            All employees have shifts assigned.
        </div>
    </div>
    @endif
</div>

{{-- ═══ ASSIGN MODAL ═══════════════════════════════════════════════════════ --}}
<div id="assignModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-title">
            <i class="fa-solid fa-clock"></i> Assign Shift
        </div>

        {{-- Employee Info --}}
        <div style="background:var(--bg-muted);border-radius:8px;padding:12px 14px;margin-bottom:18px;">
            <div id="modal-emp-name"
                 style="font-size:14px;font-weight:700;color:var(--text-primary);"></div>
            <div id="modal-emp-id"
                 style="font-size:11px;color:var(--accent);margin-top:2px;"></div>
            <div style="margin-top:8px;font-size:11px;color:var(--text-muted);">
                Current shift:
                <span id="modal-current-shift"
                      style="font-weight:600;color:var(--text-secondary);"></span>
            </div>
        </div>

        <form id="assignModalForm" method="POST">
            @csrf
            <input type="hidden" name="employee_id" id="modal-employee-id">

            <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:4px;">
                <div>
                    <label class="form-label">Shift <span style="color:var(--red);">*</span></label>
                    <select name="shift_id" id="modal-shift-id" required class="form-select">
                        <option value="">Select Shift</option>
                        @foreach($shifts as $shift)
                        <option value="{{ $shift->id }}">
                            {{ $shift->name }} — {{ $shift->timing }}
                            ({{ implode(', ', $shift->working_days ?? []) }})
                        </option>
                        @endforeach
                        <option value="none" style="color:var(--red);">
                            — Remove Shift Assignment —
                        </option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Effective From <span style="color:var(--red);">*</span></label>
                    <input type="date" name="effective_from" id="modal-effective-from"
                           required class="form-input">
                </div>
                <div>
                    <label class="form-label">Notes</label>
                    <input type="text" name="notes"
                           placeholder="Reason for shift change (optional)"
                           class="form-input">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeAssignModal()">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Save Assignment
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
var shiftNames = @json($shifts->pluck('name', 'id'));

{{-- Tab switching --}}
function switchSATab(active) {
    ['roster','assign','bulk','unassigned'].forEach(function(t) {
        document.getElementById('sapane-' + t).style.display = t === active ? 'block' : 'none';
        document.getElementById('satab-' + t).classList.toggle('active', t === active);
    });
}
switchSATab('roster');

{{-- Modal open/close --}}
function openAssignModal(empId, empName, empCode, currentShiftId) {
    document.getElementById('modal-employee-id').value     = empId;
    document.getElementById('modal-emp-name').textContent  = empName;
    document.getElementById('modal-emp-id').textContent    = empCode;
    document.getElementById('modal-effective-from').value  = new Date().toISOString().split('T')[0];
    document.getElementById('assignModalForm').action      =
        '{{ route("attendance.shift-assignment.store") }}';

    var currentName = currentShiftId && shiftNames[currentShiftId]
        ? shiftNames[currentShiftId]
        : 'Not Assigned';
    document.getElementById('modal-current-shift').textContent = currentName;

    if (currentShiftId) {
        document.getElementById('modal-shift-id').value = currentShiftId;
    } else {
        document.getElementById('modal-shift-id').value = '';
    }

    document.getElementById('assignModal').classList.add('open');
}

function closeAssignModal() {
    document.getElementById('assignModal').classList.remove('open');
}

document.getElementById('assignModal').addEventListener('click', function(e) {
    if (e.target === this) closeAssignModal();
});

{{-- Single assign: show current shift info --}}
document.getElementById('single-emp-select').addEventListener('change', function() {
    var selected = this.options[this.selectedIndex];
    var shiftName = selected.getAttribute('data-shift-name');
    var box  = document.getElementById('current-shift-info');
    var name = document.getElementById('current-shift-name');
    if (this.value && shiftName) {
        name.textContent  = shiftName;
        box.style.display = 'block';
    } else {
        box.style.display = 'none';
    }
});

{{-- Bulk: filter employees --}}
function filterBulkEmployees() {
    var dept  = document.getElementById('bulk-dept').value;
    var shift = document.getElementById('bulk-current-shift').value;
    var type  = document.getElementById('bulk-emp-type').value;

    document.querySelectorAll('.bulk-emp-row').forEach(function(row) {
        var deptMatch  = !dept  || row.dataset.dept  === dept;
        var shiftMatch = !shift || row.dataset.shift === shift;
        var typeMatch  = !type  || row.dataset.type  === type;
        row.style.display = (deptMatch && shiftMatch && typeMatch) ? 'flex' : 'none';
    });
    updateBulkCount();
}

function selectAllBulk(state) {
    document.querySelectorAll('.bulk-emp-row').forEach(function(row) {
        if (row.style.display !== 'none') {
            var chk = row.querySelector('.bulk-chk');
            chk.checked = state;
            row.style.borderColor = state ? 'var(--accent)' : 'var(--border)';
        }
    });
    updateBulkCount();
}

function updateBulkCount() {
    var count = document.querySelectorAll('.bulk-chk:checked').length;
    document.getElementById('bulk-selected-count').textContent =
        count > 0 ? count + ' employee(s) selected' : '';
}

function confirmBulkAssign() {
    var count = document.querySelectorAll('.bulk-chk:checked').length;
    if (count === 0) {
        alert('Please select at least one employee.');
        return false;
    }
    return confirm('Assign shift to ' + count + ' employee(s)?');
}
</script>
@endpush

@endsection