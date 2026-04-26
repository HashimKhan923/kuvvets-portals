<x-emails.layout>
    <div class="status-banner red">
        <span class="status-icon">❌</span>
        <div class="status-title">Leave Request Rejected</div>
        <div class="status-sub">Your leave request could not be approved at this time.</div>
    </div>

    <p class="greeting">Hi <strong>{{ $leaveRequest->employee->first_name }}</strong>,</p>

    <p class="body-text">
        We regret to inform you that your leave request has been <strong style="color:#DC2626;">rejected</strong>. Please see the details and reason below.
    </p>

    <div class="detail-box">
        <div class="detail-box-title">Rejected Leave Details</div>
        <div class="detail-row">
            <span class="detail-label">Request #</span>
            <span class="detail-value" style="color:#C2531B;font-family:'Courier New',monospace;">{{ $leaveRequest->request_number }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Leave Type</span>
            <span class="detail-value">{{ $leaveRequest->leaveType->name }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Period</span>
            <span class="detail-value">{{ $leaveRequest->from_date->format('M j') }} – {{ $leaveRequest->to_date->format('M j, Y') }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Reviewed By</span>
            <span class="detail-value">{{ $leaveRequest->reviewer?->name ?? 'HR Team' }}</span>
        </div>
    </div>

    @if($leaveRequest->rejection_reason)
    <div class="callout danger">
        <span class="callout-icon">📝</span>
        <div>
            <strong>Rejection Reason:</strong><br>
            {{ $leaveRequest->rejection_reason }}
        </div>
    </div>
    @endif

    <p class="body-text">
        If you believe this decision was made in error or wish to discuss it further, please contact your HR department directly.
    </p>

    <div class="cta-wrap">
        <a href="{{ url('/employee/leaves') }}" class="cta-btn">View My Leaves →</a>
    </div>
</x-emails.layout>