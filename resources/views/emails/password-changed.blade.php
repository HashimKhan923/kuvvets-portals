<x-emails.layout>
    <div class="status-banner blue">
        <span class="status-icon">🔒</span>
        <div class="status-title">Password Changed</div>
        <div class="status-sub">Your account password was recently updated.</div>
    </div>

    <p class="greeting">Hi <strong>{{ $user->name }}</strong>,</p>

    <p class="body-text">
        Your KUVVET portal password was successfully changed on <strong>{{ now()->format('F j, Y') }} at {{ now()->format('h:i A') }}</strong>.
    </p>

    <div class="detail-box">
        <div class="detail-box-title">Security Information</div>
        <div class="detail-row">
            <span class="detail-label">Account</span>
            <span class="detail-value">{{ $user->email }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Changed At</span>
            <span class="detail-value">{{ now()->format('M j, Y h:i A') }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">IP Address</span>
            <span class="detail-value">{{ request()->ip() }}</span>
        </div>
    </div>

    <div class="callout danger">
        <span class="callout-icon">🚨</span>
        <div>
            <strong>Didn't change your password?</strong><br>
            If you did not make this change, your account may be compromised. Contact HR or your system administrator immediately.
        </div>
    </div>

    <div class="cta-wrap">
        <a href="{{ url('/employee/login') }}" class="cta-btn">Login to Your Portal →</a>
    </div>
</x-emails.layout>