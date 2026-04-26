<x-emails.layout>
    <div class="status-banner green">
        <span class="status-icon">💰</span>
        <div class="status-title">Your Payslip is Ready</div>
        <div class="status-sub">{{ $payslip->period->month_name }} salary has been processed.</div>
    </div>

    <p class="greeting">Hi <strong>{{ $payslip->employee->first_name }}</strong>,</p>

    <p class="body-text">
        Your payslip for <strong>{{ $payslip->period->month_name }}</strong> is now available in the Employee Portal. Your salary has been processed and will be credited to your registered bank account.
    </p>

    <div class="detail-box">
        <div class="detail-box-title">Payslip Summary</div>
        <div class="detail-row">
            <span class="detail-label">Payslip #</span>
            <span class="detail-value" style="font-family:'Courier New',monospace;color:#C2531B;">{{ $payslip->payslip_number }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Period</span>
            <span class="detail-value">{{ $payslip->period->month_name }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Gross Salary</span>
            <span class="detail-value">PKR {{ number_format((float)$payslip->gross_salary, 2) }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Deductions</span>
            <span class="detail-value" style="color:#DC2626;">– PKR {{ number_format((float)$payslip->total_deductions, 2) }}</span>
        </div>
        <div class="detail-row" style="background:#F0FDF4;">
            <span class="detail-label" style="color:#16A34A;font-weight:700;">Net Salary</span>
            <span class="detail-value" style="color:#16A34A;font-size:15px;font-weight:800;">PKR {{ number_format((float)$payslip->net_salary, 2) }}</span>
        </div>
        @if($payslip->period->payment_date)
        <div class="detail-row">
            <span class="detail-label">Payment Date</span>
            <span class="detail-value">{{ $payslip->period->payment_date->format('F j, Y') }}</span>
        </div>
        @endif
    </div>

    <div class="callout info">
        <span class="callout-icon">ℹ️</span>
        <span>Log in to the Employee Portal to view your full payslip breakdown and download a PDF copy.</span>
    </div>

    <div class="cta-wrap">
        <a href="{{ url('/employee/payslips') }}" class="cta-btn">View & Download Payslip →</a>
    </div>
</x-emails.layout>