<x-emails.layout>
    <div class="status-banner blue">
        <span class="status-icon">📋</span>
        <div class="status-title">Leave Request Submitted</div>
        <div class="status-sub">Your request has been sent to HR for review.</div>
    </div>

    <p class="greeting">Hi <strong>{{ $leaveRequest->employee->first_name }}</strong>,</p>

    <p class="body-text">
        Your leave request has been successfully submitted and is now pending HR approval. You will receive an email once it is reviewed.
    </p>

    <div class="detail-box">
        <div class="detail-box-title">Leave Request Details</div>
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
            <span class="detail-label">Reason</span>
            <span class="detail-value">{{ $leaveRequest->reason }}</span>
        </div>
        @if($leaveRequest->is_emergency)
        <div class="detail-row">
            <span class="detail-label">Type</span>
            <span class="detail-value" style="color:#DC2626;">🚨 Emergency Leave</span>
        </div>
        @endif
    </div>

    <div class="callout info">
        <span class="callout-icon">ℹ️</span>
        <span>Leave requests are typically reviewed within 1 business day. You can view your request status in the Employee Portal.</span>
    </div>

    <div class="cta-wrap">
        <a href="{{ url('/employee/leaves') }}" class="cta-btn">View My Leaves →</a>
    </div>
</x-emails.layout>