<x-emails.layout>
    <div class="status-banner purple">
        <span class="status-icon">🚀</span>
        <div class="status-title">Portal Access Granted</div>
        <div class="status-sub">You now have access to the KUVVET Employee Portal.</div>
    </div>

    <p class="greeting">Hi <strong>{{ $employee->first_name }}</strong>,</p>

    <p class="body-text">
        Your access to the KUVVET Employee Portal has been activated by HR. You can now log in to manage your attendance, view payslips, apply for leaves, and more.
    </p>

    <div class="creds-box">
        <div class="creds-row">
            <span class="creds-key">Portal URL</span>
            <span class="creds-value">{{ url('/employee/login') }}</span>
        </div>
        <div class="creds-row">
            <span class="creds-key">Username</span>
            <span class="creds-value">{{ $username }}</span>
        </div>
        <div class="creds-row">
            <span class="creds-key">Temp. Password</span>
            <span class="creds-value">{{ $password }}</span>
        </div>
    </div>

    <div class="callout warning">
        <span class="callout-icon">⚠️</span>
        <span>Change your password immediately after first login. Go to <strong>Profile → Password</strong>.</span>
    </div>

    <div class="cta-wrap">
        <a href="{{ url('/employee/login') }}" class="cta-btn">Login Now →</a>
    </div>
</x-emails.layout>