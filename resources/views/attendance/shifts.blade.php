@extends('layouts.app')
@section('title', 'Shift Management')
@section('page-title', 'Shift Management')
@section('breadcrumb', 'Attendance · Shifts')

@section('content')

<div style="display:grid;grid-template-columns:1fr 360px;gap:20px;align-items:start;">

    {{-- Shift Cards --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;align-content:start;">
        @forelse($shifts as $shift)
        <div class="card" style="transition:border-color .2s;"
             onmouseover="this.style.borderColor='var(--accent-border)'"
             onmouseout="this.style.borderColor='var(--border)'">

            {{-- Card Header --}}
            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:14px;">
                <div>
                    <div style="font-size:14px;font-weight:700;color:var(--text-primary);">
                        {{ $shift->name }}
                    </div>
                    <div style="font-size:11px;color:var(--accent);margin-top:2px;">
                        {{ $shift->code }}
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:6px;">
                    <span class="badge {{ $shift->is_active ? 'badge-green' : 'badge-red' }}">
                        {{ $shift->is_active ? 'Active' : 'Inactive' }}
                    </span>
                    {{-- Edit Button --}}
                    <button onclick="openEditShift(
                                {{ $shift->id }},
                                '{{ addslashes($shift->name) }}',
                                '{{ $shift->start_time }}',
                                '{{ $shift->end_time }}',
                                {{ $shift->working_hours }},
                                {{ $shift->grace_minutes }},
                                {{ $shift->break_minutes }},
                                {{ $shift->is_night_shift ? 'true' : 'false' }},
                                {{ $shift->is_active ? 'true' : 'false' }},
                                {{ json_encode($shift->working_days ?? []) }}
                            )"
                            class="action-btn" title="Edit Shift">
                        <i class="fa-solid fa-pen"></i>
                    </button>
                    {{-- Delete Button --}}
                    <form method="POST" action="{{ route('attendance.shifts.destroy', $shift) }}"
                          onsubmit="return confirm('Delete shift \'{{ addslashes($shift->name) }}\'?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="action-btn danger" title="Delete Shift">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Shift Info Grid --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:14px;">
                @foreach([
                    ['fa-clock',     'Timing',       $shift->timing],
                    ['fa-hourglass', 'Working Hrs',  $shift->working_hours . 'h'],
                    ['fa-coffee',    'Break',        $shift->break_minutes . ' min'],
                    ['fa-stopwatch', 'Grace Period', $shift->grace_minutes . ' min'],
                ] as [$icon, $label, $value])
                <div class="detail-block">
                    <div class="detail-block-label">
                        <i class="fa-solid {{ $icon }}" style="font-size:9px;color:var(--accent);margin-right:4px;"></i>
                        {{ $label }}
                    </div>
                    <div class="detail-block-value" style="font-size:12px;">{{ $value }}</div>
                </div>
                @endforeach
            </div>

            {{-- Working Days --}}
            @if($shift->working_days)
            <div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:8px;">
                @foreach($shift->working_days as $day)
                <span class="badge badge-accent" style="font-size:10px;padding:2px 7px;">
                    {{ $day }}
                </span>
                @endforeach
            </div>
            @endif

            @if($shift->is_night_shift)
            <div style="font-size:10px;color:var(--purple);display:flex;align-items:center;gap:5px;margin-top:4px;">
                <i class="fa-solid fa-moon"></i> Night Shift
            </div>
            @endif

        </div>
        @empty
        <div class="card" style="grid-column:1/-1;">
            <div class="empty-state">
                <i class="fa-solid fa-clock"></i>
                No shifts defined yet.
            </div>
        </div>
        @endforelse
    </div>

    {{-- Create Shift Form --}}
    <div class="card" style="position:sticky;top:20px;">
        <div class="form-section">
            <i class="fa-solid fa-plus-circle"></i> New Shift
        </div>
        <form method="POST" action="{{ route('attendance.shifts.store') }}">
            @csrf
            <div style="display:flex;flex-direction:column;gap:12px;">

                <div>
                    <label class="form-label">Shift Name <span style="color:var(--red);">*</span></label>
                    <input type="text" name="name" required
                           placeholder="e.g. Morning Shift" class="form-input">
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div>
                        <label class="form-label">Start Time <span style="color:var(--red);">*</span></label>
                        <input type="time" name="start_time" required class="form-input">
                    </div>
                    <div>
                        <label class="form-label">End Time <span style="color:var(--red);">*</span></label>
                        <input type="time" name="end_time" required class="form-input">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;">
                    <div>
                        <label class="form-label">Hours</label>
                        <input type="number" name="working_hours" value="8" min="0" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Grace (min)</label>
                        <input type="number" name="grace_minutes" value="10" min="0" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Break (min)</label>
                        <input type="number" name="break_minutes" value="60" min="0" class="form-input">
                    </div>
                </div>

                <div>
                    <label class="form-label" style="margin-bottom:8px;">Working Days</label>
                    <div style="display:flex;gap:6px;flex-wrap:wrap;">
                        @foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $day)
                        @php $defaultChecked = in_array($day, ['Mon','Tue','Wed','Thu','Fri']); @endphp
                        <label style="cursor:pointer;">
                            <input type="checkbox" name="working_days[]" value="{{ $day }}"
                                   class="day-chk" style="display:none;"
                                   {{ $defaultChecked ? 'checked' : '' }}>
                            <span class="day-btn"
                                  style="display:inline-block;padding:5px 10px;border-radius:5px;
                                         font-size:11px;font-weight:500;cursor:pointer;transition:all .15s;
                                         border:1px solid {{ $defaultChecked ? 'var(--accent-border)' : 'var(--border)' }};
                                         background:{{ $defaultChecked ? 'var(--accent-bg)' : 'var(--bg-muted)' }};
                                         color:{{ $defaultChecked ? 'var(--accent)' : 'var(--text-muted)' }};">
                                {{ $day }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <label style="display:flex;align-items:center;gap:8px;font-size:12px;color:var(--text-secondary);cursor:pointer;">
                    <input type="checkbox" name="is_night_shift" value="1"
                           style="accent-color:var(--accent);">
                    Night Shift
                </label>

                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i> Create Shift
                </button>

            </div>
        </form>
    </div>

</div>

{{-- ═══ EDIT SHIFT MODAL ══════════════════════════════════════════════════ --}}
<div id="editShiftModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-title">
            <i class="fa-solid fa-pen"></i> Edit Shift
        </div>
        <form id="editShiftForm" method="POST">
            @csrf @method('PUT')
            <div style="display:flex;flex-direction:column;gap:12px;">

                <div>
                    <label class="form-label">Shift Name <span style="color:var(--red);">*</span></label>
                    <input type="text" name="name" id="edit_name" required class="form-input">
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div>
                        <label class="form-label">Start Time</label>
                        <input type="time" name="start_time" id="edit_start_time" required class="form-input">
                    </div>
                    <div>
                        <label class="form-label">End Time</label>
                        <input type="time" name="end_time" id="edit_end_time" required class="form-input">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;">
                    <div>
                        <label class="form-label">Hours</label>
                        <input type="number" name="working_hours" id="edit_working_hours" min="0" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Grace (min)</label>
                        <input type="number" name="grace_minutes" id="edit_grace_minutes" min="0" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Break (min)</label>
                        <input type="number" name="break_minutes" id="edit_break_minutes" min="0" class="form-input">
                    </div>
                </div>

                <div>
                    <label class="form-label" style="margin-bottom:8px;">Working Days</label>
                    <div style="display:flex;gap:6px;flex-wrap:wrap;">
                        @foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $day)
                        <label style="cursor:pointer;">
                            <input type="checkbox" name="working_days[]" value="{{ $day }}"
                                   class="edit-day-chk" style="display:none;">
                            <span class="edit-day-btn"
                                  style="display:inline-block;padding:5px 10px;border-radius:5px;
                                         font-size:11px;font-weight:500;cursor:pointer;transition:all .15s;
                                         border:1px solid var(--border);background:var(--bg-muted);
                                         color:var(--text-muted);">
                                {{ $day }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div style="display:flex;gap:20px;">
                    <label style="display:flex;align-items:center;gap:8px;font-size:12px;
                                   color:var(--text-secondary);cursor:pointer;">
                        <input type="checkbox" name="is_night_shift" value="1"
                               id="edit_is_night_shift" style="accent-color:var(--accent);">
                        Night Shift
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;font-size:12px;
                                   color:var(--text-secondary);cursor:pointer;">
                        <input type="checkbox" name="is_active" value="1"
                               id="edit_is_active" style="accent-color:var(--accent);">
                        Active
                    </label>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeEditShift()">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
{{-- Create form: day pill toggles --}}
document.querySelectorAll('.day-chk').forEach(function(chk) {
    chk.addEventListener('change', function() {
        setDayStyle(this.closest('label').querySelector('.day-btn'), this.checked);
    });
});

{{-- Edit form: day pill toggles --}}
document.querySelectorAll('.edit-day-chk').forEach(function(chk) {
    chk.addEventListener('change', function() {
        setDayStyle(this.closest('label').querySelector('.edit-day-btn'), this.checked);
    });
});

function setDayStyle(btn, active) {
    btn.style.background  = active ? 'var(--accent-bg)'     : 'var(--bg-muted)';
    btn.style.color       = active ? 'var(--accent)'        : 'var(--text-muted)';
    btn.style.borderColor = active ? 'var(--accent-border)' : 'var(--border)';
}

function openEditShift(id, name, startTime, endTime, hours, grace, breakMin, isNight, isActive, workingDays) {
    document.getElementById('editShiftForm').action = '/admin/attendance/shifts/' + id;
    document.getElementById('edit_name').value           = name;
    document.getElementById('edit_start_time').value     = startTime;
    document.getElementById('edit_end_time').value       = endTime;
    document.getElementById('edit_working_hours').value  = hours;
    document.getElementById('edit_grace_minutes').value  = grace;
    document.getElementById('edit_break_minutes').value  = breakMin;
    document.getElementById('edit_is_night_shift').checked = isNight;
    document.getElementById('edit_is_active').checked      = isActive;

    document.querySelectorAll('.edit-day-chk').forEach(function(chk) {
        var checked = workingDays.indexOf(chk.value) !== -1;
        chk.checked = checked;
        setDayStyle(chk.closest('label').querySelector('.edit-day-btn'), checked);
    });

    document.getElementById('editShiftModal').classList.add('open');
}

function closeEditShift() {
    document.getElementById('editShiftModal').classList.remove('open');
}

document.getElementById('editShiftModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditShift();
});
</script>
@endpush

@endsection