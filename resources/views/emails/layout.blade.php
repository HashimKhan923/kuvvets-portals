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
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; display: block; }
        body { margin: 0 !important; padding: 0 !important; background-color: #EDE8DF; }
        @media only screen and (max-width: 620px) {
            .email-container { width: 100% !important; }
            .email-body-td   { padding: 24px 20px !important; }
            .header-td       { padding: 16px 22px !important; }
            .footer-td       { padding: 18px 22px !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#F5F0E8;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#EDE8DF;">
<tr><td align="center" style="padding:32px 16px;">

    <table role="presentation" class="email-container" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;">

        {{-- ── HEADER ─────────────────────────────────────────── --}}
        <tr>
            <td class="header-td" style="background-color:#0F0901;border-radius:16px 16px 0 0;padding:20px 32px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td style="vertical-align:middle;">
                            <img src="https://portals.kuvvets.com/kuvvet_logo.png"
                                 alt="KUVVET"
                                 width="130"
                                 style="height:auto;max-height:52px;width:auto;max-width:160px;">
                        </td>
                        <td style="vertical-align:middle;text-align:right;">
                            <div style="font-family:Arial,sans-serif;font-size:11px;color:rgba(255,255,255,0.45);">{{ now()->format('M j, Y') }}</div>
                            <div style="font-family:Arial,sans-serif;font-size:10px;color:rgba(255,255,255,0.25);margin-top:2px;">Automated email</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        {{-- ── GOLD ACCENT STRIP ───────────────────────────────── --}}
        <tr>
            <td height="3" style="background-color:#D4A843;font-size:0;line-height:0;">&nbsp;</td>
        </tr>

        {{-- ── BODY ────────────────────────────────────────────── --}}
        <tr>
            <td class="email-body-td" style="background-color:#ffffff;padding:36px;">
                {!! $slot !!}
            </td>
        </tr>

        {{-- ── FOOTER ──────────────────────────────────────────── --}}
        <tr>
            <td class="footer-td" style="background-color:#ffffff;border-top:2px solid #D4A843;border-radius:0 0 16px 16px;padding:22px 36px;text-align:center;">
                <img src="https://portals.kuvvets.com/kuvvet_dark_logo.jpeg"
                     alt="KUVVET"
                     width="80"
                     style="height:auto;margin:0 auto 10px;">
                <div style="font-family:Arial,sans-serif;font-size:12px;font-weight:700;color:#1A0F09;margin-bottom:5px;">
                    KUVVET
                </div>
                <div style="font-family:Arial,sans-serif;font-size:11px;color:#8A7060;line-height:1.7;margin-bottom:10px;">
                    Pakistan &nbsp;&middot;&nbsp;
                    <a href="mailto:{{ config('mail.from.address') }}" style="color:#D4A843;text-decoration:none;">{{ config('mail.from.address') }}</a>
                </div>
                <div style="font-family:Arial,sans-serif;font-size:11px;margin-bottom:12px;">
                    <a href="{{ url('/employee/login') }}" style="color:#D4A843;text-decoration:none;">Employee Portal</a>
                    &nbsp;&middot;&nbsp;
                    <a href="{{ url('/admin/login') }}" style="color:#D4A843;text-decoration:none;">Admin Portal</a>
                </div>
                <div style="font-family:Arial,sans-serif;font-size:10px;color:#B0A090;line-height:1.6;">
                    This is an automated email from the KUVVET HR System. Please do not reply.<br>
                    If you received this in error, please contact HR.
                </div>
            </td>
        </tr>

    </table>

</td></tr>
</table>
</body>
</html>
