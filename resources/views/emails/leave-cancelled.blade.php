<x-emails.layout>
    <div class="status-banner yellow">
        <span class="status-icon">🚫</span>
        <div class="status-title">Leave Request Cancelled</div>
        <div class="status-sub">Request {{ $leaveRequest->request_number }} has been cancelled.</div>
    </div>

    <p class="greeting">Hi <strong>{{ $leaveRequest->employee->first_name }}</strong>,</p>

    <p class="body-text">
        Your leave request has been <strong>cancelled</strong>. If this was a mistake or you still need time off, please submit a new request through the portal.
    </p>

    <div class="detail-box">
        <div class="detail-box-title">Cancelled Leave Details</div>
        <div class="detail-row">
            <span class="detail-label">Request #</span>
            <span class="detail-value" style="font-family:'Courier New',monospace;">{{ $leaveRequest->request_number }}</span>
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
            <span class="detail-label">Days Restored</span>
            <span class="detail-value" style="color:#16A34A;">+{{ $leaveRequest->total_days }} day(s) back to your balance</span>
        </div>
    </div>

    <div class="cta-wrap">
        <a href="{{ url('/employee/leaves/apply') }}" class="cta-btn">Apply for New Leave →</a>
    </div>
</x-emails.layout>