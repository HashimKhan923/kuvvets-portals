<x-emails.layout>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
        <tr>
            <td style="background-color:#F0FDF4;border:1.5px solid #BBF7D0;border-radius:14px;padding:20px 24px;text-align:center;">
                <div style="font-size:36px;line-height:1;margin-bottom:10px;">✅</div>
                <div style="font-family:Arial,sans-serif;font-size:20px;font-weight:800;color:#16A34A;margin-bottom:6px;">Leave Approved!</div>
                <div style="font-family:Arial,sans-serif;font-size:13px;color:#6B5347;line-height:1.5;">Your leave request has been approved by HR.</div>
            </td>
        </tr>
    </table>

    <p style="font-family:Arial,sans-serif;font-size:15px;color:#6B5347;margin:0 0 18px;">Hi <strong style="color:#2D1F14;">{{ $leaveRequest->employee->first_name }}</strong>,</p>
    <p style="font-family:Arial,sans-serif;font-size:14px;color:#4A3728;line-height:1.7;margin:0 0 24px;">Great news! Your leave request has been <strong style="color:#16A34A;">approved</strong>. Please make sure to hand over your responsibilities before your leave starts.</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-radius:12px;overflow:hidden;margin-bottom:24px;">
        <tr><td style="background-color:#F0EAE2;padding:12px 18px;font-family:Arial,sans-serif;font-size:11px;font-weight:700;color:#6B5347;text-transform:uppercase;letter-spacing:0.8px;">Approved Leave Details</td></tr>
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
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#A89080;font-weight:600;padding:12px 0;border-bottom:1px solid #F0EAE2;">Approved By</td>
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#2D1F14;font-weight:600;padding:12px 0;border-bottom:1px solid #F0EAE2;">{{ $leaveRequest->reviewer?->name ?? 'HR Team' }}</td>
                </tr>
                <tr>
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#A89080;font-weight:600;padding:12px 0;{{ $leaveRequest->hr_notes ? 'border-bottom:1px solid #F0EAE2;' : '' }}">Approved On</td>
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#2D1F14;font-weight:600;padding:12px 0;{{ $leaveRequest->hr_notes ? 'border-bottom:1px solid #F0EAE2;' : '' }}">{{ $leaveRequest->reviewed_at?->format('M j, Y h:i A') }}</td>
                </tr>
                @if($leaveRequest->hr_notes)
                <tr>
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#A89080;font-weight:600;padding:12px 0;">HR Notes</td>
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#2D1F14;font-weight:600;padding:12px 0;">{{ $leaveRequest->hr_notes }}</td>
                </tr>
                @endif
            </table>
        </td></tr>
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
        <tr>
            <td style="background-color:#F0FDF4;border-left:4px solid #22C55E;border-radius:0 10px 10px 0;padding:14px 18px;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td style="font-size:16px;vertical-align:top;padding-right:12px;padding-top:1px;">💡</td>
                        <td style="font-family:Arial,sans-serif;font-size:12.5px;color:#14532D;line-height:1.6;">Please ensure proper handover before your leave. Contact HR if you need to modify or cancel this leave.</td>
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