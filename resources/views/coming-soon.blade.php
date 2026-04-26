
<div style="display:flex;align-items:center;justify-content:center;min-height:60vh;">
    <div style="text-align:center;max-width:480px;">
        <div style="width:80px;height:80px;background:var(--gold-faint);border:2px solid var(--gold-mid);
                    border-radius:20px;display:flex;align-items:center;justify-content:center;
                    margin:0 auto 24px;font-size:32px;">
            ⚙️
        </div>
        <h2 style="font-family:'Space Grotesk',sans-serif;font-size:22px;font-weight:700;
                   color:var(--text-primary);margin-bottom:10px;">
            {{ $module }}
        </h2>
        <p style="font-size:14px;color:var(--text-muted);line-height:1.7;margin-bottom:28px;">
            This module is currently under development and will be available soon.
            The KUVVET portal is being built step by step with full functionality.
        </p>
        <div style="display:flex;gap:10px;justify-content:center;">
            <a href="{{ route('dashboard') }}"
               style="padding:10px 22px;background:var(--navy);color:#F0D080;border:none;
                      border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;
                      display:inline-flex;align-items:center;gap:7px;">
                <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
        <div style="margin-top:32px;padding:16px;background:var(--gold-faint);
                    border:1px solid var(--gold-mid);border-radius:10px;">
            <div style="font-size:11px;color:var(--gold-dark);font-weight:600;
                        letter-spacing:.5px;margin-bottom:6px;">MODULES COMPLETED</div>
            <div style="display:flex;flex-wrap:wrap;gap:6px;justify-content:center;">
                @foreach(['Employees','Departments','Attendance','Recruitment','Leave Management','Payroll'] as $m)
                <span style="font-size:11px;background:var(--white);color:var(--success);
                             border:1px solid var(--success-border);border-radius:20px;
                             padding:2px 10px;">
                    <i class="fa-solid fa-check" style="font-size:9px;"></i> {{ $m }}
                </span>
                @endforeach
            </div>
        </div>
    </div>
</div>
