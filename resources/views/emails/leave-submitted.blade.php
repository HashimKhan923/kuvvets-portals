<x-emails.layout>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
        <tr>
            <td style="background-color:#EFF6FF;border:1.5px solid #BFDBFE;border-radius:14px;padding:20px 24px;text-align:center;">
                <div style="font-size:36px;line-height:1;margin-bottom:10px;">📋</div>
                <div style="font-family:Arial,sans-serif;font-size:20px;font-weight:800;color:#2563EB;margin-bottom:6px;">Leave Request Submitted</div>
                <div style="font-family:Arial,sans-serif;font-size:13px;color:#6B5347;line-height:1.5;">Your request has been sent to HR for review.</div>
            </td>
        </tr>
    </table>

    <p style="font-family:Arial,sans-serif;font-size:15px;color:#6B5347;margin:0 0 18px;">Hi <strong style="color:#2D1F14;">{{ $leaveRequest->employee->first_name }}</strong>,</p>
    <p style="font-family:Arial,sans-serif;font-size:14px;color:#4A3728;line-height:1.7;margin:0 0 24px;">Your leave request has been successfully submitted and is now pending HR approval. You will receive an email once it is reviewed.</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-radius:12px;overflow:hidden;margin-bottom:24px;">
        <tr><td style="background-color:#F0EAE2;padding:12px 18px;font-family:Arial,sans-serif;font-size:11px;font-weight:700;color:#6B5347;text-transform:uppercase;letter-spacing:0.8px;">Leave Request Details</td></tr>
        <tr><td style="background-color:#F7F3EF;padding:0 18px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#A89080;font-weight:600;padding:12px 0;border-bottom:1px solid #F0EAE2;width:160px;">Request #</td>
                    <td style="font-family:'Courier New',monospace;font-size:13px;color:#C2531B;font-weight:600;padding:12px 0;border-bottom:1px solid #F0EAE2;">{{ $leaveRequest->request_number }}</td>
                </tr>
                <tr>
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#A89080;font-weight:600;padding:12px 0;border-bottom:1px solid #F0EAE2;">Leave Type</td>
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#2D1F14;font-weight:600;padding:12px 0;border-bottom:1px solid #F0EAE2;">{{ $leaveRequest->leaveType->name }}</td>
                </tr>
                <tr>
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#A89080;font-weight:600;padding:12px 0;border-bottom:1px solid #F0EAE2;">From</td>
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#2D1F14;font-weight:600;padding:12px 0;border-bottom:1px solid #F0EAE2;">{{ $leaveRequest->from_date->format('l, F j, Y') }}</td>
                </tr>
                <tr>
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#A89080;font-weight:600;padding:12px 0;border-bottom:1px solid #F0EAE2;">To</td>
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#2D1F14;font-weight:600;padding:12px 0;border-bottom:1px solid #F0EAE2;">{{ $leaveRequest->to_date->format('l, F j, Y') }}</td>
                </tr>
                <tr>
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#A89080;font-weight:600;padding:12px 0;border-bottom:1px solid #F0EAE2;">Duration</td>
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#2D1F14;font-weight:600;padding:12px 0;border-bottom:1px solid #F0EAE2;">{{ $leaveRequest->total_days }} working day(s)</td>
                </tr>
                <tr>
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#A89080;font-weight:600;padding:12px 0;{{ $leaveRequest->is_emergency ? 'border-bottom:1px solid #F0EAE2;' : '' }}">Reason</td>
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#2D1F14;font-weight:600;padding:12px 0;{{ $leaveRequest->is_emergency ? 'border-bottom:1px solid #F0EAE2;' : '' }}">{{ $leaveRequest->reason }}</td>
                </tr>
                @if($leaveRequest->is_emergency)
                <tr>
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#A89080;font-weight:600;padding:12px 0;">Type</td>
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#DC2626;font-weight:600;padding:12px 0;">🚨 Emergency Leave</td>
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
                        <td style="font-family:Arial,sans-serif;font-size:12.5px;color:#1E40AF;line-height:1.6;">Leave requests are typically reviewed within 1 business day. You can view your request status in the Employee Portal.</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr><td style="text-align:center;padding:4px 0 8px;">
            <a href="{{ url('/employee/leaves') }}" style="display:inline-block;background-color:#C2531B;color:#ffffff;text-decoration:none;padding:14px 36px;border-radius:12px;font-family:Arial,sans-serif;font-size:14px;font-weight:700;">View My Leaves &rarr;</a>
        </td></tr>
    </table>

</x-emails.layout>