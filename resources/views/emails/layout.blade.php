<!DOCTYPE html>
<html lang="en" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <title>{{ $subject ?? 'KUVVET' }}</title>
    <!--[if mso]>
    <noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript>
    <![endif]-->
    <style>
        body, table, td, p, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
        body { margin: 0 !important; padding: 0 !important; background-color: #F5F0EB; }
        @media only screen and (max-width: 620px) {
            .email-container { width: 100% !important; }
            .email-body-td   { padding: 24px 20px !important; }
            .header-td       { padding: 20px 22px !important; }
            .footer-td       { padding: 18px 22px !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#F5F0EB;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#F5F0EB;">
<tr><td align="center" style="padding:32px 16px;">

    <table role="presentation" class="email-container" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;">

        {{-- HEADER --}}
        <tr>
            <td class="header-td" style="background-color:#2D1F14;border-radius:18px 18px 0 0;padding:28px 36px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td style="vertical-align:middle;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="vertical-align:middle;width:42px;height:42px;background-color:#C2531B;border-radius:11px;text-align:center;">
                                        <span style="font-family:Arial,sans-serif;font-size:18px;font-weight:800;color:#ffffff;letter-spacing:1px;line-height:42px;">K</span>
                                    </td>
                                    <td style="vertical-align:middle;padding-left:10px;">
                                        <div style="font-family:Arial,sans-serif;font-size:18px;font-weight:800;color:#ffffff;letter-spacing:1px;">KUVVET</div>
                                        <div style="font-family:Arial,sans-serif;font-size:9px;color:rgba(255,255,255,0.5);letter-spacing:2px;text-transform:uppercase;margin-top:2px;">HR Portal</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td style="vertical-align:middle;text-align:right;">
                            <div style="font-family:Arial,sans-serif;font-size:11px;color:rgba(255,255,255,0.5);">{{ now()->format('M j, Y') }}</div>
                            <div style="font-family:Arial,sans-serif;font-size:10px;color:rgba(255,255,255,0.3);margin-top:2px;">Automated email</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        {{-- COLOR STRIP --}}
        <tr>
            <td height="4" style="background-color:#C2531B;font-size:0;line-height:0;">&nbsp;</td>
        </tr>

        {{-- BODY --}}
        <tr>
            <td class="email-body-td" style="background-color:#ffffff;padding:36px;">
                {!! $slot !!}
            </td>
        </tr>

        {{-- FOOTER --}}
        <tr>
            <td class="footer-td" style="background-color:#F7F3EF;border-radius:0 0 18px 18px;padding:24px 36px;text-align:center;">
                <div style="font-family:Arial,sans-serif;font-size:13px;font-weight:700;color:#2D1F14;margin-bottom:6px;">KUVVET Private Limited</div>
                <div style="font-family:Arial,sans-serif;font-size:11px;color:#A89080;line-height:1.6;margin-bottom:12px;">
                    Karachi, Pakistan &nbsp;&middot;&nbsp;
                    <a href="mailto:{{ config('mail.from.address') }}" style="color:#C2531B;text-decoration:none;">{{ config('mail.from.address') }}</a>
                </div>
                <div style="font-family:Arial,sans-serif;font-size:11px;color:#A89080;margin-bottom:12px;">
                    <a href="{{ url('/employee/login') }}" style="color:#C2531B;text-decoration:none;">Employee Portal</a>
                    &nbsp;&middot;&nbsp;
                    <a href="{{ url('/admin/login') }}" style="color:#C2531B;text-decoration:none;">Admin Portal</a>
                </div>
                <div style="font-family:Arial,sans-serif;font-size:10px;color:#C4B8B0;line-height:1.5;">
                    This is an automated email from KUVVET HR System. Please do not reply to this email.<br>
                    If you believe this email was sent in error, please contact HR.
                </div>
            </td>
        </tr>

    </table>

</td></tr>
</table>
</body>
</html>