@extends('layouts.app')
@section('title', 'Add Leave Request')
@section('page-title', 'Add Leave Request')
@section('breadcrumb', 'Leaves · New Request')

@section('content')
<div style="max-width:720px;">

@if($errors->any())
<div class="error-box" style="margin-bottom:18px;">
    <div class="error-box-title">
        <i class="fa-solid fa-triangle-exclamation"></i> Please fix these errors:
    </div>
    <ul style="padding-left:16px;">
        @foreach($errors->all() as $e)
            <li>{{ $e }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('leaves.store') }}" enctype="multipart/form-data">
@csrf

<div class="card" style="margin-bottom:16px;">
    <div class="form-section">
        <i class="fa-solid fa-calendar-plus"></i> Leave Details
    </div>
    <div style="display:flex;flex-direction:column;gap:14px;">

        {{-- Employee --}}
        <div>
            <label class="form-label">Employee <span style="color:var(--red);">*</span></label>
            <select name="employee_id" required class="form-select">
                <option value="">Select Employee</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                        {{ $emp->full_name }} — {{ $emp->employee_id }}
                        ({{ $emp->department?->name ?? 'No dept' }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Leave Type + Day Type --}}
        <div class="grid-2" style="gap:14px;">
            <div>
                <label class="form-label">Leave Type <span style="color:var(--red);">*</span></label>
                <select name="leave_type_id" required class="form-select">
                    <option value="">Select Leave Type</option>
                    @foreach($leaveTypes as $lt)
                        <option value="{{ $lt->id }}" {{ old('leave_type_id') == $lt->id ? 'selected' : '' }}>
                            {{ $lt->name }} ({{ $lt->days_per_year }} days/yr · {{ $lt->is_paid ? 'Paid' : 'Unpaid' }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Day Type</label>
                <select name="day_type" class="form-select">
                    <option value="full_day">Full Day</option>
                    <option value="half_day_morning">Half Day (Morning)</option>
                    <option value="half_day_afternoon">Half Day (Afternoon)</option>
                </select>
            </div>
        </div>

        {{-- Dates --}}
        <div class="grid-2" style="gap:14px;">
            <div>
                <label class="form-label">From Date <span style="color:var(--red);">*</span></label>
                <input type="date" name="from_date" required value="{{ old('from_date') }}"
                       class="form-input" onchange="calcDays()">
            </div>
            <div>
                <label class="form-label">To Date <span style="color:var(--red);">*</span></label>
                <input type="date" name="to_date" required value="{{ old('to_date') }}"
                       class="form-input" onchange="calcDays()">
            </div>
        </div>

        {{-- Day counter --}}
        <div id="dayCountBox"
             style="display:none;background:var(--accent-bg);border:1px solid var(--accent-border);
                    border-radius:8px;padding:10px 14px;">
            <span style="font-size:12px;color:var(--text-muted);">Estimated working days: </span>
            <span id="dayCount" style="font-size:16px;font-weight:700;color:var(--accent);"></span>
        </div>

        {{-- Reason --}}
        <div>
            <label class="form-label">Reason <span style="color:var(--red);">*</span></label>
            <textarea name="reason" required rows="3"
                      placeholder="Reason for taking leave…"
                      class="form-textarea">{{ old('reason') }}</textarea>
        </div>

        {{-- Contact + Document --}}
        <div class="grid-2" style="gap:14px;">
            <div>
                <label class="form-label">Contact During Leave</label>
                <input type="text" name="contact_during_leave"
                       value="{{ old('contact_during_leave') }}"
                       placeholder="+92-300-0000000" class="form-input">
            </div>
            <div>
                <label class="form-label">Supporting Document</label>
                <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png"
                       class="form-input">
                <div style="font-size:10px;color:var(--text-muted);margin-top:4px;">
                    PDF, JPG or PNG · Max 5MB
                </div>
            </div>
        </div>

        {{-- Emergency checkbox --}}
        <label style="display:flex;align-items:center;gap:8px;font-size:13px;
                       color:var(--text-secondary);cursor:pointer;">
            <input type="checkbox" name="is_emergency" value="1"
                   {{ old('is_emergency') ? 'checked' : '' }}
                   style="accent-color:var(--red);width:14px;height:14px;">
            <span>Mark as Emergency Leave</span>
            <span style="font-size:11px;color:var(--text-muted);">
                (Bypass minimum notice requirement)
            </span>
        </label>

    </div>
</div>

{{-- Buttons --}}
<div style="display:flex;align-items:center;justify-content:space-between;">
    <a href="{{ route('leaves.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Cancel
    </a>
    <button type="submit" class="btn btn-primary">
        <i class="fa-solid fa-paper-plane"></i> Submit Leave Request
    </button>
</div>

</form>
</div>

@push('scripts')
<script>
function calcDays() {
    var from = document.querySelector('[name=from_date]').value;
    var to   = document.querySelector('[name=to_date]').value;
    if (!from || !to) return;

    var f = new Date(from), t = new Date(to);
    if (t < f) return;

    var days = 0, cur = new Date(f);
    while (cur <= t) {
        var day = cur.getDay();
        if (day !== 0 && day !== 6) days++;
        cur.setDate(cur.getDate() + 1);
    }

    document.getElementById('dayCount').textContent    = days + (days === 1 ? ' day' : ' days');
    document.getElementById('dayCountBox').style.display = 'block';
}
</script>
@endpush

@endsection