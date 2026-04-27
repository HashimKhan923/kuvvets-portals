<x-emails.layout>
    <div class="status-banner green">
        <span class="status-icon">🎉</span>
        <div class="status-title">Welcome to KUVVET!</div>
        <div class="status-sub">Your employee account has been created.</div>
    </div>

    <p class="greeting">Hi <strong>{{ $employee->first_name }}</strong>,</p>

    <p class="body-text">
        Welcome aboard! Your account on the KUVVET Employee Portal has been set up by HR.
        You can log in to manage your attendance, view payslips, apply for leaves, and more.
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