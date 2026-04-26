@extends('layouts.app')
@section('title', 'Schedule Session')
@section('page-title', 'Schedule Training Session')
@section('breadcrumb', 'Training · Sessions · New')

@section('content')
<div style="max-width:760px;">
<form method="POST" action="{{ route('training.sessions.store') }}">
@csrf

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

<div class="card" style="margin-bottom:16px;">
    <div class="form-section">
        <i class="fa-solid fa-calendar-plus"></i> Session Details
    </div>
    <div style="display:flex;flex-direction:column;gap:14px;">

        <div>
            <label class="form-label">Training Program <span style="color:var(--red);">*</span></label>
            <select name="training_program_id" required class="form-select">
                <option value="">Select Program</option>
                @foreach($programs as $p)
                <option value="{{ $p->id }}"
                        {{ (old('training_program_id') == $p->id || $selectedProgram?->id == $p->id) ? 'selected' : '' }}>
                    {{ $p->title }}
                    ({{ ucfirst(str_replace('_', ' ', $p->category)) }} · {{ $p->duration_hours }}h)
                </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label">Session Title <span style="color:var(--red);">*</span></label>
            <input type="text" name="title" required
                   value="{{ old('title', $selectedProgram ? $selectedProgram->title . ' — Batch ' . date('MY') : '') }}"
                   placeholder="e.g. Forklift Safety — Batch Jun2025"
                   class="form-input">
        </div>

        <div class="grid-2" style="gap:14px;">
            <div>
                <label class="form-label">Start Date <span style="color:var(--red);">*</span></label>
                <input type="date" name="start_date" required value="{{ old('start_date') }}" class="form-input">
            </div>
            <div>
                <label class="form-label">End Date <span style="color:var(--red);">*</span></label>
                <input type="date" name="end_date" required value="{{ old('end_date') }}" class="form-input">
            </div>
            <div>
                <label class="form-label">Start Time</label>
                <input type="time" name="start_time" value="{{ old('start_time', '09:00') }}" class="form-input">
            </div>
            <div>
                <label class="form-label">End Time</label>
                <input type="time" name="end_time" value="{{ old('end_time', '17:00') }}" class="form-input">
            </div>
            <div>
                <label class="form-label">Venue / Location</label>
                <input type="text" name="venue" value="{{ old('venue') }}"
                       placeholder="e.g. Training Room A / Online" class="form-input">
            </div>
            <div>
                <label class="form-label">Max Participants <span style="color:var(--red);">*</span></label>
                <input type="number" name="max_participants" required
                       value="{{ old('max_participants', 20) }}" min="1" class="form-input">
            </div>
            <div>
                <label class="form-label">Trainer Name</label>
                <input type="text" name="trainer_name" value="{{ old('trainer_name') }}"
                       placeholder="e.g. Ahmed Khan / External Trainer" class="form-input">
            </div>
            <div>
                <label class="form-label">Trainer Email</label>
                <input type="email" name="trainer_email" value="{{ old('trainer_email') }}"
                       placeholder="trainer@example.com" class="form-input">
            </div>
            <div>
                <label class="form-label">Actual Cost (PKR)</label>
                <input type="number" name="actual_cost" value="{{ old('actual_cost', 0) }}"
                       min="0" class="form-input">
            </div>
        </div>

        <div>
            <label class="form-label">Notes</label>
            <textarea name="notes" rows="2"
                      placeholder="Any additional notes or prerequisites…"
                      class="form-textarea">{{ old('notes') }}</textarea>
        </div>
    </div>
</div>

<div style="display:flex;align-items:center;justify-content:space-between;">
    <a href="{{ route('training.sessions') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Cancel
    </a>
    <button type="submit" class="btn btn-primary">
        <i class="fa-solid fa-calendar-check"></i> Schedule Session
    </button>
</div>
</form>
</div>
@endsection