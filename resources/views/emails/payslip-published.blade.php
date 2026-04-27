<x-emails.layout>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
        <tr>
            <td style="background-color:#F0FDF4;border:1.5px solid #BBF7D0;border-radius:14px;padding:20px 24px;text-align:center;">
                <div style="font-size:36px;line-height:1;margin-bottom:10px;">💰</div>
                <div style="font-family:Arial,sans-serif;font-size:20px;font-weight:800;color:#16A34A;margin-bottom:6px;">Your Payslip is Ready</div>
                <div style="font-family:Arial,sans-serif;font-size:13px;color:#6B5347;line-height:1.5;">{{ $payslip->period->month_name }} salary has been processed.</div>
            </td>
        </tr>
    </table>

    <p style="font-family:Arial,sans-serif;font-size:15px;color:#6B5347;margin:0 0 18px;">Hi <strong style="color:#2D1F14;">{{ $payslip->employee->first_name }}</strong>,</p>
    <p style="font-family:Arial,sans-serif;font-size:14px;color:#4A3728;line-height:1.7;margin:0 0 24px;">Your payslip for <strong>{{ $payslip->period->month_name }}</strong> is now available in the Employee Portal. Your salary has been processed and will be credited to your registered bank account.</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-radius:12px;overflow:hidden;margin-bottom:24px;">
        <tr><td style="background-color:#F0EAE2;padding:12px 18px;font-family:Arial,sans-serif;font-size:11px;font-weight:700;color:#6B5347;text-transform:uppercase;letter-spacing:0.8px;">Payslip Summary</td></tr>
        <tr><td style="background-color:#F7F3EF;padding:0 18px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#A89080;font-weight:600;padding:12px 0;border-bottom:1px solid #F0EAE2;width:160px;">Payslip #</td>
                    <td style="font-family:'Courier New',monospace;font-size:13px;color:#C2531B;font-weight:600;padding:12px 0;border-bottom:1px solid #F0EAE2;">{{ $payslip->payslip_number }}</td>
                </tr>
                <tr>
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#A89080;font-weight:600;padding:12px 0;border-bottom:1px solid #F0EAE2;">Period</td>
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#2D1F14;font-weight:600;padding:12px 0;border-bottom:1px solid #F0EAE2;">{{ $payslip->period->month_name }}</td>
                </tr>
                <tr>
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#A89080;font-weight:600;padding:12px 0;border-bottom:1px solid #F0EAE2;">Gross Salary</td>
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#2D1F14;font-weight:600;padding:12px 0;border-bottom:1px solid #F0EAE2;">PKR {{ number_format((float)$payslip->gross_salary, 2) }}</td>
                </tr>
                <tr>
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#A89080;font-weight:600;padding:12px 0;border-bottom:1px solid #F0EAE2;">Deductions</td>
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#DC2626;font-weight:600;padding:12px 0;border-bottom:1px solid #F0EAE2;">– PKR {{ number_format((float)$payslip->total_deductions, 2) }}</td>
                </tr>
                <tr style="background-color:#F0FDF4;">
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#16A34A;font-weight:700;padding:12px 0;{{ $payslip->period->payment_date ? 'border-bottom:1px solid #F0EAE2;' : '' }}">Net Salary</td>
                    <td style="font-family:Arial,sans-serif;font-size:15px;color:#16A34A;font-weight:800;padding:12px 0;{{ $payslip->period->payment_date ? 'border-bottom:1px solid #F0EAE2;' : '' }}">PKR {{ number_format((float)$payslip->net_salary, 2) }}</td>
                </tr>
                @if($payslip->period->payment_date)
                <tr>
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#A89080;font-weight:600;padding:12px 0;">Payment Date</td>
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#2D1F14;font-weight:600;padding:12px 0;">{{ $payslip->period->payment_date->format('F j, Y') }}</td>
                </tr>
                @endif
            </table>
        </td></tr>
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
        <tr>
            <td style="background-color:#EFF6FF;border-left:4px solid #3B82F6;border-radius:0 10px 10px 0;padding:14px 18px;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td style="font-size:16px;vertical-align:top;padding-right:12px;padding-top:1px;">ℹ️</td>
                        <td style="font-family:Arial,sans-serif;font-size:12.5px;color:#1E40AF;line-height:1.6;">Log in to the Employee Portal to view your full payslip breakdown and download a PDF copy.</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr><td style="text-align:center;padding:4px 0 8px;">
            <a href="{{ url('/employee/payslips') }}" style="display:inline-block;background-color:#C2531B;color:#ffffff;text-decoration:none;padding:14px 36px;border-radius:12px;font-family:Arial,sans-serif;font-size:14px;font-weight:700;">View &amp; Download Payslip &rarr;</a>
        </td></tr>
    </table>

</x-emails.layout>