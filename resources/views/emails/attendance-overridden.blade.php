<x-emails.layout>
    <div class="status-banner yellow">
        <span class="status-icon">✏️</span>
        <div class="status-title">Attendance Record Updated</div>
        <div class="status-sub">HR has made a change to your attendance record.</div>
    </div>

    <p class="greeting">Hi <strong>{{ $attendance->employee->first_name }}</strong>,</p>

    <p class="body-text">
        Your attendance record for <strong>{{ $attendance->date->format('l, F j, Y') }}</strong> has been manually updated by HR. Please review the changes below.
    </p>

    <div class="detail-box">
        <div class="detail-box-title">Updated Attendance Record</div>
        <div class="detail-row">
            <span class="detail-label">Date</span>
            <span class="detail-value">{{ $attendance->date->format('l, F j, Y') }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Status</span>
            <span class="detail-value">{{ ucwords(str_replace('_',' ',$attendance->status)) }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Check-in</span>
            <span class="detail-value">{{ $attendance->check_in?->format('h:i A') ?? '—' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Check-out</span>
            <span class="detail-value">{{ $attendance->check_out?->format('h:i A') ?? '—' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Working Hours</span>
            <span class="detail-value">{{ $attendance->working_hours }}</span>
        </div>
        @if($attendance->notes)
        <div class="detail-row">
            <span class="detail-label">HR Notes</span>
            <span class="detail-value">{{ $attendance->notes }}</span>
        </div>
        @endif
    </div>

    <div class="callout info">
        <span class="callout-icon">ℹ️</span>
        <span>If you have questions about this change, contact your HR department.</span>
    </div>

    <div class="cta-wrap">
        <a href="{{ url('/employee/attendance') }}" class="cta-btn">View Attendance History →</a>
    </div>
</x-emails.layout>