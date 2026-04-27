<x-emails.layout>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
        <tr>
            @if($document->isExpired())
            <td style="background-color:#FEF2F2;border:1.5px solid #FECACA;border-radius:14px;padding:20px 24px;text-align:center;">
                <div style="font-size:36px;line-height:1;margin-bottom:10px;">🚨</div>
                <div style="font-family:Arial,sans-serif;font-size:20px;font-weight:800;color:#DC2626;margin-bottom:6px;">Document Expired</div>
                <div style="font-family:Arial,sans-serif;font-size:13px;color:#6B5347;line-height:1.5;">Action required: update your document immediately.</div>
            </td>
            @else
            <td style="background-color:#FFFBEB;border:1.5px solid #FDE68A;border-radius:14px;padding:20px 24px;text-align:center;">
                <div style="font-size:36px;line-height:1;margin-bottom:10px;">⏰</div>
                <div style="font-family:Arial,sans-serif;font-size:20px;font-weight:800;color:#D97706;margin-bottom:6px;">Document Expiring Soon</div>
                <div style="font-family:Arial,sans-serif;font-size:13px;color:#6B5347;line-height:1.5;">Please renew your document before it expires.</div>
            </td>
            @endif
        </tr>
    </table>

    <p style="font-family:Arial,sans-serif;font-size:15px;color:#6B5347;margin:0 0 18px;">Hi <strong style="color:#2D1F14;">{{ $document->employee->first_name }}</strong>,</p>
    <p style="font-family:Arial,sans-serif;font-size:14px;color:#4A3728;line-height:1.7;margin:0 0 24px;">
        @if($document->isExpired())
            Your document <strong>{{ $document->title }}</strong> has <strong style="color:#DC2626;">expired</strong>. Please upload a renewed copy as soon as possible.
        @else
            Your document <strong>{{ $document->title }}</strong> will expire on <strong>{{ $document->expiry_date->format('F j, Y') }}</strong> — that's <strong>{{ $document->expiry_date->diffInDays(now()) }} days away</strong>. Please arrange for renewal.
        @endif
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-radius:12px;overflow:hidden;margin-bottom:24px;">
        <tr><td style="background-color:#F0EAE2;padding:12px 18px;font-family:Arial,sans-serif;font-size:11px;font-weight:700;color:#6B5347;text-transform:uppercase;letter-spacing:0.8px;">Document Details</td></tr>
        <tr><td style="background-color:#F7F3EF;padding:0 18px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#A89080;font-weight:600;padding:12px 0;border-bottom:1px solid #F0EAE2;width:160px;">Document</td>
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#2D1F14;font-weight:600;padding:12px 0;border-bottom:1px solid #F0EAE2;">{{ $document->title }}</td>
                </tr>
                <tr>
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#A89080;font-weight:600;padding:12px 0;border-bottom:1px solid #F0EAE2;">Type</td>
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#2D1F14;font-weight:600;padding:12px 0;border-bottom:1px solid #F0EAE2;">{{ ucfirst(str_replace('_',' ',$document->type)) }}</td>
                </tr>
                <tr>
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#A89080;font-weight:600;padding:12px 0;">Expiry Date</td>
                    <td style="font-family:Arial,sans-serif;font-size:13px;font-weight:600;padding:12px 0;color:{{ $document->isExpired() ? '#DC2626' : '#D97706' }};">
                        {{ $document->expiry_date->format('F j, Y') }}
                        @if($document->isExpired()) (Expired) @else (In {{ $document->expiry_date->diffInDays(now()) }} days) @endif
                    </td>
                </tr>
            </table>
        </td></tr>
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr><td style="text-align:center;padding:4px 0 8px;">
            <a href="{{ url('/employee/profile?tab=documents') }}" style="display:inline-block;background-color:#C2531B;color:#ffffff;text-decoration:none;padding:14px 36px;border-radius:12px;font-family:Arial,sans-serif;font-size:14px;font-weight:700;">Upload New Document &rarr;</a>
        </td></tr>
    </table>

</x-emails.layout>