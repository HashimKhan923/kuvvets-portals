<x-emails.layout>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
        <tr>
            <td style="background-color:#F5F3FF;border:1.5px solid #DDD6FE;border-radius:14px;padding:20px 24px;text-align:center;">
                <div style="font-size:36px;line-height:1;margin-bottom:10px;">🚀</div>
                <div style="font-family:Arial,sans-serif;font-size:20px;font-weight:800;color:#7C3AED;margin-bottom:6px;">Portal Access Granted</div>
                <div style="font-family:Arial,sans-serif;font-size:13px;color:#6B5347;line-height:1.5;">You now have access to the KUVVET Employee Portal.</div>
            </td>
        </tr>
    </table>

    <p style="font-family:Arial,sans-serif;font-size:15px;color:#6B5347;margin:0 0 18px;">Hi <strong style="color:#2D1F14;">{{ $employee->first_name }}</strong>,</p>

    <p style="font-family:Arial,sans-serif;font-size:14px;color:#4A3728;line-height:1.7;margin:0 0 24px;">
        Your access to the KUVVET Employee Portal has been activated by HR. You can now log in to manage your attendance, view payslips, apply for leaves, and more.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#2D1F14;border-radius:12px;margin-bottom:24px;">
        <tr>
            <td style="padding:20px 24px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td style="font-family:'Courier New',monospace;font-size:13px;color:rgba(255,255,255,0.55);padding:6px 0;border-bottom:1px solid rgba(255,255,255,0.08);width:40%;">Portal URL</td>
                        <td style="font-family:'Courier New',monospace;font-size:13px;color:#E87A45;font-weight:700;padding:6px 0;border-bottom:1px solid rgba(255,255,255,0.08);text-align:right;">{{ url('/employee/login') }}</td>
                    </tr>
                    <tr>
                        <td style="font-family:'Courier New',monospace;font-size:13px;color:rgba(255,255,255,0.55);padding:6px 0;border-bottom:1px solid rgba(255,255,255,0.08);">Username</td>
                        <td style="font-family:'Courier New',monospace;font-size:13px;color:#E87A45;font-weight:700;padding:6px 0;border-bottom:1px solid rgba(255,255,255,0.08);text-align:right;">{{ $username }}</td>
                    </tr>
                    <tr>
                        <td style="font-family:'Courier New',monospace;font-size:13px;color:rgba(255,255,255,0.55);padding:6px 0;">Temp. Password</td>
                        <td style="font-family:'Courier New',monospace;font-size:13px;color:#E87A45;font-weight:700;padding:6px 0;text-align:right;">{{ $password }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
        <tr>
            <td style="background-color:#FFFBEB;border-left:4px solid #F59E0B;border-radius:0 10px 10px 0;padding:14px 18px;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td style="font-size:16px;vertical-align:top;padding-right:12px;padding-top:1px;">⚠️</td>
                        <td style="font-family:Arial,sans-serif;font-size:12.5px;color:#92400E;line-height:1.6;">Change your password immediately after first login. Go to <strong>Profile &rarr; Password</strong>.</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td style="text-align:center;padding:4px 0 8px;">
                <a href="{{ url('/employee/login') }}" style="display:inline-block;background-color:#C2531B;color:#ffffff;text-decoration:none;padding:14px 36px;border-radius:12px;font-family:Arial,sans-serif;font-size:14px;font-weight:700;">Login Now &rarr;</a>
            </td>
        </tr>
    </table>

</x-emails.layout>