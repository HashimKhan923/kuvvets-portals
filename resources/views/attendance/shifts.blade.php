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

            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:14px;">
                <div>
                    <div style="font-size:14px;font-weight:700;color:var(--text-primary);">
                        {{ $shift->name }}
                    </div>
                    <div style="font-size:11px;color:var(--accent);margin-top:2px;">
                        {{ $shift->code }}
                    </div>
                </div>
                <span class="badge {{ $shift->is_active ? 'badge-green' : 'badge-red' }}">
                    {{ $shift->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>

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

@push('scripts')
<script>
document.querySelectorAll('.day-chk').forEach(function(chk) {
    chk.addEventListener('change', function() {
        var btn = this.closest('label').querySelector('.day-btn');
        if (this.checked) {
            btn.style.background   = 'var(--accent-bg)';
            btn.style.color        = 'var(--accent)';
            btn.style.borderColor  = 'var(--accent-border)';
        } else {
            btn.style.background   = 'var(--bg-muted)';
            btn.style.color        = 'var(--text-muted)';
            btn.style.borderColor  = 'var(--border)';
        }
    });
});
</script>
@endpush

@endsection