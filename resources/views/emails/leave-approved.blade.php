<x-emails.layout>
    <div class="status-banner green">
        <span class="status-icon">✅</span>
        <div class="status-title">Leave Approved!</div>
        <div class="status-sub">Your leave request has been approved by HR.</div>
    </div>

    <p class="greeting">Hi <strong>{{ $leaveRequest->employee->first_name }}</strong>,</p>

    <p class="body-text">
        Great news! Your leave request has been <strong style="color:#16A34A;">approved</strong>. Please make sure to hand over your responsibilities before your leave starts.
    </p>

    <div class="detail-box">
        <div class="detail-box-title">Approved Leave Details</div>
        <div class="detail-row">
            <span class="detail-label">Request #</span>
            <span class="detail-value" style="color:#C2531B;font-family:'Courier New',monospace;">{{ $leaveRequest->request_number }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Leave Type</span>
            <span class="detail-value">{{ $leaveRequest->leaveType->name }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">From</span>
            <span class="detail-value">{{ $leaveRequest->from_date->format('l, F j, Y') }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">To</span>
            <span class="detail-value">{{ $leaveRequest->to_date->format('l, F j, Y') }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Duration</span>
            <span class="detail-value">{{ $leaveRequest->total_days }} working day(s)</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Approved By</span>
            <span class="detail-value">{{ $leaveRequest->reviewer?->name ?? 'HR Team' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Approved On</span>
            <span class="detail-value">{{ $leaveRequest->reviewed_at?->format('M j, Y h:i A') }}</span>
        </div>
        @if($leaveRequest->hr_notes)
        <div class="detail-row">
            <span class="detail-label">HR Notes</span>
            <span class="detail-value">{{ $leaveRequest->hr_notes }}</span>
        </div>
        @endif
    </div>

    <div class="callout success">
        <span class="callout-icon">💡</span>
        <span>Please ensure proper handover before your leave. Contact HR if you need to modify or cancel this leave.</span>
    </div>

    <div class="cta-wrap">
        <a href="{{ url('/employee/leaves') }}" class="cta-btn">View My Leaves →</a>
    </div>
</x-emails.layout>