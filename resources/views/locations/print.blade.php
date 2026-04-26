<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>QR Code — {{ $location->name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        @page { size: A4; margin: 18mm; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; color: #2D1F14; background: #F5F0EB; }

        .sheet {
            max-width: 174mm; margin: 20px auto;
            background: #fff;
            border: 2px solid #2D1F14;
            border-radius: 12px;
            padding: 28px 26px;
            page-break-after: always;
        }

        .brand { display: flex; align-items: center; gap: 12px; margin-bottom: 22px; }
        .brand-logo { width: 44px; height: 44px; border-radius: 10px; background: linear-gradient(135deg, #C2531B, #E87A45); color: #fff; font-family: 'Space Grotesk', sans-serif; font-weight: 700; font-size: 18px; display: flex; align-items: center; justify-content: center; letter-spacing: 1px; }
        .brand-name { font-family: 'Space Grotesk', sans-serif; font-size: 16px; font-weight: 800; letter-spacing: .5px; }
        .brand-sub  { font-size: 10px; color: #A89080; letter-spacing: .8px; }

        .title {
            text-align: center;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 13px; font-weight: 700;
            color: #6B5347; letter-spacing: 3px; text-transform: uppercase;
            margin-bottom: 4px;
        }

        .loc-name {
            text-align: center;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 26px; font-weight: 700;
            line-height: 1.2;
        }
        .loc-code {
            text-align: center;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 13px; font-weight: 600;
            color: #C2531B;
            letter-spacing: 2px;
            margin-top: 4px;
        }

        .qr-container {
            background: linear-gradient(135deg, #FEF2EC, #fff);
            border: 2px dashed #F5D5C0;
            border-radius: 16px;
            padding: 30px;
            margin: 22px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            max-width: 100%;
        }
        .qr-container img { width: 240px; height: 240px; display: block; }

        .instructions {
            margin-top: 20px;
            padding: 16px 20px;
            background: #F5F0EB;
            border-radius: 12px;
            text-align: center;
        }
        .instructions-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 12px; font-weight: 700;
            color: #6B5347; letter-spacing: 1.5px; text-transform: uppercase;
            margin-bottom: 10px;
        }
        .steps {
            display: grid; grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }
        .step {
            padding: 12px 8px;
            background: #fff;
            border-radius: 10px;
            border: 1px solid #F0EAE2;
        }
        .step-num {
            width: 26px; height: 26px; border-radius: 50%;
            background: #C2531B; color: #fff;
            font-weight: 700; font-size: 13px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 6px;
        }
        .step-txt {
            font-size: 11px; line-height: 1.4; color: #2D1F14; font-weight: 600;
        }

        .meta {
            margin-top: 20px;
            padding-top: 14px;
            border-top: 1px dashed #E8DDD5;
            display: flex; justify-content: space-between; align-items: center;
            font-size: 10px; color: #A89080;
        }

        .address {
            text-align: center;
            margin-top: 14px;
            font-size: 11.5px;
            color: #6B5347;
            line-height: 1.5;
        }
        .address i { color: #C2531B; margin-right: 4px; }

        .no-print {
            max-width: 174mm; margin: 0 auto 16px; display: flex; gap: 10px; justify-content: center;
        }
        .no-print button, .no-print a {
            padding: 10px 22px; border-radius: 10px; border: none; cursor: pointer;
            font: inherit; font-weight: 600; font-size: 13px; text-decoration: none;
            display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-print { background: linear-gradient(135deg, #C2531B, #E87A45); color: #fff; }
        .btn-back { background: #E8DDD5; color: #2D1F14; }

        @media print {
            body { background: #fff; }
            .sheet { border: 1px solid #2D1F14; margin: 0; box-shadow: none; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <a href="{{ route('locations.show', $location) }}" class="btn-back">
        ← Back
    </a>
    <button onclick="window.print()" class="btn-print">
        🖨️ Print Now
    </button>
</div>

<div class="sheet">
    <div class="brand">
        <div class="brand-logo">K</div>
        <div>
            <div class="brand-name">KUVVET</div>
            <div class="brand-sub">ATTENDANCE CHECK-IN</div>
        </div>
    </div>

    <div class="title">Location QR Code</div>
    <div class="loc-name">{{ $location->name }}</div>
    <div class="loc-code">{{ $location->code }}</div>

    <div class="qr-container">
        <img src="{{ route('locations.qr', $location) }}" alt="QR Code">
    </div>

    @if($location->address || $location->city)
        <div class="address">
            <i>📍</i>
            @if($location->address){{ $location->address }} · @endif
            {{ $location->city }}@if($location->province), {{ $location->province }} @endif
        </div>
    @endif

    <div class="instructions">
        <div class="instructions-title">How to Check In</div>
        <div class="steps">
            <div class="step">
                <div class="step-num">1</div>
                <div class="step-txt">Open KUVVET app or portal on your phone</div>
            </div>
            <div class="step">
                <div class="step-num">2</div>
                <div class="step-txt">Tap the <strong>Scan QR</strong> button on dashboard</div>
            </div>
            <div class="step">
                <div class="step-num">3</div>
                <div class="step-txt">Point camera at this code — done!</div>
            </div>
        </div>
    </div>

    <div class="meta">
        <span>Geofence: {{ $location->radius_meters }}m radius</span>
        <span>Generated: {{ now()->format('M j, Y') }}</span>
    </div>
</div>

<script>
    // Auto-trigger print dialog after tiny delay (user can cancel)
    // window.addEventListener('load', () => setTimeout(() => window.print(), 500));
</script>
</body>
</html>