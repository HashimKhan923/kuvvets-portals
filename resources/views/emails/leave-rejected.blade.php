<x-emails.layout>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
        <tr>
            <td style="background-color:#FEF2F2;border:1.5px solid #FECACA;border-radius:14px;padding:20px 24px;text-align:center;">
                <div style="font-size:36px;line-height:1;margin-bottom:10px;">❌</div>
                <div style="font-family:Arial,sans-serif;font-size:20px;font-weight:800;color:#DC2626;margin-bottom:6px;">Leave Request Rejected</div>
                <div style="font-family:Arial,sans-serif;font-size:13px;color:#6B5347;line-height:1.5;">Your leave request could not be approved at this time.</div>
            </td>
        </tr>
    </table>

    <p style="font-family:Arial,sans-serif;font-size:15px;color:#6B5347;margin:0 0 18px;">Hi <strong style="color:#2D1F14;">{{ $leaveRequest->employee->first_name }}</strong>,</p>
    <p style="font-family:Arial,sans-serif;font-size:14px;color:#4A3728;line-height:1.7;margin:0 0 24px;">We regret to inform you that your leave request has been <strong style="color:#DC2626;">rejected</strong>. Please see the details and reason below.</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-radius:12px;overflow:hidden;margin-bottom:24px;">
        <tr><td style="background-color:#F0EAE2;padding:12px 18px;font-family:Arial,sans-serif;font-size:11px;font-weight:700;color:#6B5347;text-transform:uppercase;letter-spacing:0.8px;">Rejected Leave Details</td></tr>
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
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#A89080;font-weight:600;padding:12px 0;border-bottom:1px solid #F0EAE2;">Period</td>
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#2D1F14;font-weight:600;padding:12px 0;border-bottom:1px solid #F0EAE2;">{{ $leaveRequest->from_date->format('M j') }} – {{ $leaveRequest->to_date->format('M j, Y') }}</td>
                </tr>
                <tr>
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#A89080;font-weight:600;padding:12px 0;">Reviewed By</td>
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#2D1F14;font-weight:600;padding:12px 0;">{{ $leaveRequest->reviewer?->name ?? 'HR Team' }}</td>
                </tr>
            </table>
        </td></tr>
    </table>

    @if($leaveRequest->rejection_reason)
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
        <tr>
            <td style="background-color:#FEF2F2;border-left:4px solid #EF4444;border-radius:0 10px 10px 0;padding:14px 18px;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td style="font-size:16px;vertical-align:top;padding-right:12px;padding-top:1px;">📝</td>
                        <td style="font-family:Arial,sans-serif;font-size:12.5px;color:#7F1D1D;line-height:1.6;">
                            <strong>Rejection Reason:</strong><br>{{ $leaveRequest->rejection_reason }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    @endif

    <p style="font-family:Arial,sans-serif;font-size:14px;color:#4A3728;line-height:1.7;margin:0 0 24px;">If you believe this decision was made in error or wish to discuss it further, please contact your HR department directly.</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr><td style="text-align:center;padding:4px 0 8px;">
            <a href="{{ url('/employee/leaves') }}" style="display:inline-block;background-color:#C2531B;color:#ffffff;text-decoration:none;padding:14px 36px;border-radius:12px;font-family:Arial,sans-serif;font-size:14px;font-weight:700;">View My Leaves &rarr;</a>
        </td></tr>
    </table>

</x-emails.layout>