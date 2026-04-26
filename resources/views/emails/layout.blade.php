<!DOCTYPE html>
<html lang="en" xmlns:v="urn:schemas-microsoft-com:vml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <title>{{ $subject ?? 'KUVVET' }}</title>
    <!--[if mso]>
    <noscript>
        <xml><o:OfficeDocumentSettings>
            <o:PixelsPerInch>96</o:PixelsPerInch>
        </o:OfficeDocumentSettings></xml>
    </noscript>
    <![endif]-->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            background-color: #F5F0EB;
            margin: 0 !important;
            padding: 0 !important;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        table { border-spacing: 0 !important; border-collapse: collapse !important; }
        img { border: 0; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; }
        a { color: inherit; }

        .email-wrapper {
            width: 100%;
            background: #F5F0EB;
            padding: 32px 16px;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
        }

        /* Header */
        .email-header {
            background: linear-gradient(135deg, #2D1F14 0%, #3d2a1c 100%);
            border-radius: 18px 18px 0 0;
            padding: 28px 36px;
            position: relative;
            overflow: hidden;
        }
        .email-header-inner {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .email-logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .email-logo-badge {
            width: 42px;
            height: 42px;
            border-radius: 11px;
            background: linear-gradient(135deg, #C2531B, #E87A45);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 18px;
            color: #fff;
            letter-spacing: 1px;
        }
        .email-logo-text {
            font-size: 18px;
            font-weight: 800;
            color: #fff;
            letter-spacing: 1px;
        }
        .email-logo-sub {
            font-size: 9px;
            color: rgba(255,255,255,.5);
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 1px;
        }
        .email-header-date {
            font-size: 11px;
            color: rgba(255,255,255,.5);
            text-align: right;
        }

        /* Color strip */
        .email-strip {
            height: 4px;
            background: linear-gradient(90deg, #C2531B 0%, #E87A45 50%, #F59E0B 100%);
        }

        /* Body */
        .email-body {
            background: #FFFFFF;
            padding: 36px;
        }

        /* Status banner */
        .status-banner {
            border-radius: 14px;
            padding: 20px 24px;
            margin-bottom: 28px;
            text-align: center;
        }
        .status-banner.orange { background: #FEF2EC; border: 1.5px solid #F5D5C0; }
        .status-banner.green  { background: #F0FDF4; border: 1.5px solid #BBF7D0; }
        .status-banner.red    { background: #FEF2F2; border: 1.5px solid #FECACA; }
        .status-banner.blue   { background: #EFF6FF; border: 1.5px solid #BFDBFE; }
        .status-banner.yellow { background: #FFFBEB; border: 1.5px solid #FDE68A; }
        .status-banner.purple { background: #F5F3FF; border: 1.5px solid #DDD6FE; }

        .status-icon {
            font-size: 36px;
            margin-bottom: 10px;
            display: block;
        }
        .status-title {
            font-size: 20px;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 6px;
        }
        .status-banner.orange .status-title { color: #C2531B; }
        .status-banner.green  .status-title { color: #16A34A; }
        .status-banner.red    .status-title { color: #DC2626; }
        .status-banner.blue   .status-title { color: #2563EB; }
        .status-banner.yellow .status-title { color: #D97706; }
        .status-banner.purple .status-title { color: #7C3AED; }
        .status-sub {
            font-size: 13px;
            color: #6B5347;
            line-height: 1.5;
        }

        /* Greeting */
        .greeting {
            font-size: 15px;
            color: #6B5347;
            margin-bottom: 18px;
        }
        .greeting strong {
            color: #2D1F14;
            font-weight: 700;
        }

        /* Detail rows */
        .detail-box {
            background: #F7F3EF;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 24px;
        }
        .detail-box-title {
            padding: 12px 18px;
            background: #F0EAE2;
            font-size: 11px;
            font-weight: 700;
            color: #6B5347;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        .detail-row {
            display: flex;
            padding: 12px 18px;
            border-bottom: 1px solid #F0EAE2;
            font-size: 13px;
        }
        .detail-row:last-child { border-bottom: none; }
        .detail-label {
            width: 160px;
            flex-shrink: 0;
            color: #A89080;
            font-weight: 600;
        }
        .detail-value {
            flex: 1;
            color: #2D1F14;
            font-weight: 600;
        }

        /* Body text */
        .body-text {
            font-size: 14px;
            color: #4A3728;
            line-height: 1.7;
            margin-bottom: 20px;
        }

        /* CTA button */
        .cta-wrap {
            text-align: center;
            margin: 28px 0;
        }
        .cta-btn {
            display: inline-block;
            background: linear-gradient(135deg, #C2531B, #E87A45);
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 36px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.3px;
            box-shadow: 0 6px 16px rgba(194, 83, 27, 0.3);
        }

        /* Info callout */
        .callout {
            border-radius: 10px;
            padding: 14px 18px;
            margin-bottom: 20px;
            font-size: 12.5px;
            line-height: 1.6;
            display: flex;
            gap: 12px;
        }
        .callout.info   { background: #EFF6FF; border-left: 4px solid #3B82F6; color: #1E40AF; }
        .callout.warning{ background: #FFFBEB; border-left: 4px solid #F59E0B; color: #92400E; }
        .callout.success{ background: #F0FDF4; border-left: 4px solid #22C55E; color: #14532D; }
        .callout.danger { background: #FEF2F2; border-left: 4px solid #EF4444; color: #7F1D1D; }
        .callout-icon   { font-size: 16px; flex-shrink: 0; padding-top: 1px; }

        /* Credentials box */
        .creds-box {
            background: #2D1F14;
            color: #fff;
            border-radius: 12px;
            padding: 20px 24px;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
        }
        .creds-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            border-bottom: 1px solid rgba(255,255,255,.08);
            font-size: 13px;
        }
        .creds-row:last-child { border-bottom: none; }
        .creds-key   { color: rgba(255,255,255,.55); font-family: inherit; }
        .creds-value { color: #E87A45; font-weight: 700; font-family: inherit; }

        /* Divider */
        .divider {
            height: 1px;
            background: #F0EAE2;
            margin: 24px 0;
        }

        /* Footer */
        .email-footer {
            background: #F7F3EF;
            border-radius: 0 0 18px 18px;
            padding: 24px 36px;
            text-align: center;
        }
        .footer-company {
            font-size: 13px;
            font-weight: 700;
            color: #2D1F14;
            margin-bottom: 4px;
        }
        .footer-address {
            font-size: 11px;
            color: #A89080;
            line-height: 1.6;
            margin-bottom: 14px;
        }
        .footer-links {
            font-size: 11px;
            color: #A89080;
            margin-bottom: 12px;
        }
        .footer-links a {
            color: #C2531B;
            text-decoration: none;
        }
        .footer-disclaimer {
            font-size: 10px;
            color: #C4B8B0;
            line-height: 1.5;
        }

        /* Mobile */
        @media (max-width: 600px) {
            .email-wrapper { padding: 16px 8px; }
            .email-body { padding: 24px 20px; }
            .email-header { padding: 20px 22px; }
            .email-footer { padding: 18px 22px; }
            .detail-label { width: 120px; font-size: 12px; }
            .detail-value { font-size: 12px; }
            .status-title { font-size: 18px; }
        }
    </style>
</head>
<body>
<div class="email-wrapper">
<div class="email-container">

    {{-- ═══ Header ═══ --}}
    <div class="email-header">
        <div class="email-header-inner">
            <div class="email-logo">
                <div class="email-logo-badge">K</div>
                <div>
                    <div class="email-logo-text">KUVVET</div>
                    <div class="email-logo-sub">HR Portal</div>
                </div>
            </div>
            <div class="email-header-date">
                {{ now()->format('M j, Y') }}<br>
                <span style="font-size:10px;color:rgba(255,255,255,.35);">Automated email</span>
            </div>
        </div>
    </div>
    <div class="email-strip"></div>

    {{-- ═══ Body ═══ --}}
    <div class="email-body">
        {{ $slot }}
    </div>

    {{-- ═══ Footer ═══ --}}
    <div class="email-footer">
        <div class="footer-company">KUVVET Private Limited</div>
        <div class="footer-address">
            Karachi, Pakistan &nbsp;·&nbsp;
            <a href="mailto:{{ config('mail.from.address') }}" style="color:#C2531B;">{{ config('mail.from.address') }}</a>
        </div>
        <div class="footer-links">
            <a href="{{ url('/employee/login') }}">Employee Portal</a>
            &nbsp;·&nbsp;
            <a href="{{ url('/admin/login') }}">Admin Portal</a>
        </div>
        <div class="footer-disclaimer">
            This is an automated email from KUVVET HR System. Please do not reply to this email.<br>
            If you believe this email was sent in error, please contact HR.
        </div>
    </div>

</div>
</div>
</body>
</html>