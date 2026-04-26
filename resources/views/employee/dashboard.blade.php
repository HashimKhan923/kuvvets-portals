@extends('employee.layouts.app')
@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $firstName = explode(' ', $employee->full_name)[0];
@endphp
@section('title', 'Home')
@section('page-title', $greeting . ', ' . $firstName)
@section('page-sub', now()->format('l, F j, Y'))

@push('styles')
<style>
    /* ═══════════════════════════════════════════════════
       DASHBOARD
    ═══════════════════════════════════════════════════ */
    .grid-main { display: grid; grid-template-columns: 2fr 1fr; gap: 18px; }
    @media (max-width: 960px) { .grid-main { grid-template-columns: 1fr; } }

    /* Hero card */
    .hero-check {
        background: linear-gradient(135deg, #2D1F14 0%, #3d2a1c 100%);
        color: #fff; border-radius: 20px; padding: 26px;
        position: relative; overflow: hidden;
        display: flex; flex-direction: column; gap: 18px;
    }
    .hero-check::before {
        content:''; position:absolute; top:-60%; right:-20%;
        width:440px; height:440px;
        background: radial-gradient(circle, rgba(232,122,69,.25), transparent 60%);
        pointer-events:none;
    }
    .hero-check::after {
        content:''; position:absolute; bottom:-40%; left:-10%;
        width:320px; height:320px;
        background: radial-gradient(circle, rgba(245,158,11,.15), transparent 60%);
        pointer-events:none;
    }
    .hero-row { position:relative; z-index:1; }

    .hero-top {
        display:flex; align-items:center; justify-content:space-between;
        gap:12px; flex-wrap:wrap;
    }
    .hero-date {
        font-size: 10.5px; letter-spacing: 1.2px; text-transform: uppercase;
        color: rgba(255,255,255,.6); font-weight: 600;
    }
    .hero-status {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 5px 12px; border-radius: 999px;
        background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.15);
        font-size: 11px; font-weight: 600;
    }
    .hero-status .dot { width: 6px; height: 6px; border-radius: 50%; }
    .hero-status.s-out .dot    { background: #94a3b8; }
    .hero-status.s-in .dot     { background: #22c55e; animation: pulse-dot 2s infinite; }
    .hero-status.s-break .dot  { background: #F59E0B; animation: pulse-dot 2s infinite; }
    .hero-status.s-done .dot   { background: #3B82F6; }
    @keyframes pulse-dot { 0%,100%{box-shadow:0 0 0 0 rgba(34,197,94,.7)} 50%{box-shadow:0 0 0 6px rgba(34,197,94,0)} }

    .hero-clock-wrap { display:flex; align-items:baseline; gap:12px; flex-wrap:wrap; }
    .hero-clock {
        font-family:'Space Grotesk',sans-serif;
        font-size: 52px; font-weight: 700; letter-spacing: -1px; line-height: 1;
        font-variant-numeric: tabular-nums;
    }
    .hero-clock .secs { font-size: 26px; opacity: .55; font-weight: 500; }
    .hero-zone { font-size: 11px; color: rgba(255,255,255,.5); letter-spacing: .5px; }

    /* Shift progress */
    .shift-bar {
        position:relative; z-index:1;
        background: rgba(255,255,255,.08);
        border-radius: 999px;
        height: 6px; overflow: hidden;
    }
    .shift-fill {
        height: 100%;
        background: linear-gradient(90deg, #22c55e, #E87A45, #F59E0B);
        border-radius: 999px;
        transition: width .6s ease;
    }
    .shift-meta {
        display:flex; justify-content:space-between; font-size:10.5px;
        color: rgba(255,255,255,.55); margin-top: 6px;
        letter-spacing: .3px;
    }

    /* Location pill */
    .loc-pill {
        display:inline-flex; align-items:center; gap:6px;
        padding: 5px 11px; border-radius: 999px;
        background: rgba(255,255,255,.06);
        border: 1px solid rgba(255,255,255,.12);
        font-size: 11px; color: rgba(255,255,255,.85);
    }
    .loc-pill i { color: #E87A45; font-size: 10px; }

    /* Action buttons */
    .hero-actions {
        position:relative; z-index:1;
        display:flex; gap:10px; flex-wrap:wrap;
    }
    .big-btn {
        flex: 1; min-width: 150px;
        height: 58px; border-radius: 14px; border:none; cursor:pointer;
        font: inherit; font-weight: 700; font-size: 14.5px;
        display:flex; align-items:center; justify-content:center; gap:10px;
        transition: all .2s;
    }
    .big-btn i { font-size: 17px; }
    .big-btn.primary {
        background: linear-gradient(135deg, #C2531B, #E87A45);
        color:#fff; box-shadow: 0 8px 24px rgba(194,83,27,.4);
    }
    .big-btn.primary:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(194,83,27,.5); }
    .big-btn.primary:disabled { opacity: .55; cursor: not-allowed; }
    .big-btn.out {
        background: linear-gradient(135deg, #3b82f6, #60a5fa);
        color:#fff; box-shadow: 0 8px 24px rgba(59,130,246,.4);
    }
    .big-btn.out:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(59,130,246,.5); }
    .big-btn.break {
        background: linear-gradient(135deg, #F59E0B, #FBBF24);
        color:#1a0f00; box-shadow: 0 8px 24px rgba(245,158,11,.35);
    }
    .big-btn.break:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(245,158,11,.45); }
    .big-btn.break-end {
        background: linear-gradient(135deg, #16a34a, #22c55e);
        color:#fff; box-shadow: 0 8px 24px rgba(22,163,74,.35);
        animation: breakPulse 2s ease-in-out infinite;
    }
    @keyframes breakPulse { 0%,100%{box-shadow:0 8px 24px rgba(22,163,74,.35)} 50%{box-shadow:0 8px 24px rgba(22,163,74,.6), 0 0 0 6px rgba(22,163,74,.1)} }
    .big-btn.done {
        background: rgba(255,255,255,.05);
        border: 1px solid rgba(255,255,255,.15);
        color: rgba(255,255,255,.7);
        cursor: default;
    }
    .big-btn.qr {
        background: rgba(255,255,255,.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,.2);
        color: #fff; flex: 0 0 auto; padding: 0 20px;
    }
    .big-btn.qr:hover { background: rgba(255,255,255,.15); }
    .hero-spinner {
        display:inline-block; width:16px; height:16px;
        border:2px solid rgba(255,255,255,.3); border-top-color:#fff;
        border-radius:50%; animation: spin .7s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* Metric tiles in hero */
    .hero-metrics {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 8px;
        position:relative; z-index:1;
    }
    @media (max-width:520px) { .hero-metrics { grid-template-columns: repeat(2, 1fr); } }
    .hero-metric {
        background: rgba(255,255,255,.05);
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 12px;
        padding: 10px 12px;
    }
    .hero-metric-lbl {
        font-size: 9.5px; color: rgba(255,255,255,.55);
        letter-spacing: .6px; text-transform: uppercase; font-weight: 600;
        display: flex; align-items: center; gap: 5px;
    }
    .hero-metric-lbl i { font-size: 9px; }
    .hero-metric-val {
        font-size: 16px; font-weight: 700; margin-top: 4px;
        font-family: 'Space Grotesk', sans-serif;
        font-variant-numeric: tabular-nums;
    }
    .hero-metric-val .unit { font-size: 10px; font-weight: 500; color: rgba(255,255,255,.5); margin-left: 1px; }
    .hero-metric.accent { background: rgba(232,122,69,.12); border-color: rgba(232,122,69,.25); }
    .hero-metric.warn   { background: rgba(245,158,11,.12); border-color: rgba(245,158,11,.25); }
    .hero-metric.late   { background: rgba(220,38,38,.12);  border-color: rgba(220,38,38,.25); }

    /* Break banner */
    .break-banner {
        position:relative; z-index:1;
        display:flex; align-items:center; gap:12px;
        background: rgba(245,158,11,.15);
        border: 1px solid rgba(245,158,11,.3);
        border-radius: 12px;
        padding: 12px 14px;
    }
    .break-banner i { color: #F59E0B; font-size: 16px; }
    .break-banner-txt { font-size: 12.5px; font-weight: 600; color: #fde68a; }
    .break-banner-time { font-size: 11px; color: rgba(253,230,138,.65); margin-top:1px; font-variant-numeric: tabular-nums; }

    /* Right column */
    .tiny-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 16px;
    }
    .tiny-title {
        font-size: 11px; color: var(--text-muted); text-transform: uppercase;
        letter-spacing: .8px; font-weight: 600; margin-bottom: 8px;
    }
    .tiny-val {
        font-family:'Space Grotesk',sans-serif;
        font-size: 22px; font-weight: 700; color: var(--text-primary);
        font-variant-numeric: tabular-nums;
    }
    .tiny-sub { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
    .stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }

    /* 7-day strip */
    .strip { display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; }
    .strip-day {
        text-align: center; padding: 10px 6px;
        border-radius: 10px; background: var(--bg-muted);
        border: 1px solid transparent;
    }
    .strip-day.today { border-color: var(--accent); background: var(--accent-bg); }
    .strip-dow { font-size: 10px; color: var(--text-muted); font-weight: 600; letter-spacing: .4px; }
    .strip-num { font-size: 15px; font-weight: 700; margin-top: 3px; }
    .strip-dot { width: 6px; height: 6px; border-radius: 50%; margin: 6px auto 0; }
    .strip-dot.present { background: var(--green); }
    .strip-dot.late    { background: var(--yellow); }
    .strip-dot.absent  { background: var(--red); }
    .strip-dot.leave   { background: var(--purple); }
    .strip-dot.off     { background: transparent; border: 1px dashed var(--border-strong); }
    .strip-dot.weekend { background: var(--border-strong); }

    .loc-item {
        display: flex; align-items: center; gap: 12px;
        padding: 12px; border-radius: 12px; background: var(--bg-muted);
        margin-bottom: 8px;
    }
    .loc-item:last-child { margin-bottom: 0; }
    .loc-ico {
        width: 36px; height: 36px; border-radius: 10px;
        background: var(--accent-bg); color: var(--accent);
        display: flex; align-items: center; justify-content: center; font-size: 14px;
        flex-shrink: 0;
    }
    .loc-name { font-size: 13px; font-weight: 600; color: var(--text-primary); }
    .loc-meta { font-size: 11px; color: var(--text-muted); }

    /* Break reason menu */
    .break-menu {
        position: absolute; top: calc(100% + 8px); right: 0;
        background: #fff; border: 1px solid var(--border);
        border-radius: 12px; padding: 6px;
        box-shadow: 0 12px 32px rgba(0,0,0,.15);
        min-width: 180px;
        z-index: 50;
    }
    .break-opt {
        display: flex; align-items: center; gap: 10px;
        padding: 10px 12px; border-radius: 8px;
        color: var(--text-primary); font-size: 13px; cursor: pointer;
        border: none; background: none; width: 100%; text-align: left;
        font-family: inherit;
    }
    .break-opt:hover { background: var(--bg-hover); color: var(--accent); }
    .break-opt i { width: 18px; color: var(--accent); }

    /* ═══════════ QR Scanner Modal ═══════════ */
    .modal-overlay {
        position: fixed; inset: 0; z-index: 100;
        background: rgba(15, 10, 5, .75); backdrop-filter: blur(6px);
        display: flex; align-items: center; justify-content: center;
        padding: 16px; animation: fadeIn .2s ease;
    }
    @keyframes fadeIn { from{opacity:0} to{opacity:1} }
    .modal-box {
        background: var(--bg-card); border-radius: 20px;
        width: 100%; max-width: 440px; overflow: hidden;
        box-shadow: 0 20px 60px rgba(0,0,0,.3);
        animation: slideUp .25s cubic-bezier(.22,.61,.36,1);
    }
    @keyframes slideUp { from{opacity:0;transform:translateY(30px) scale(.96)} to{opacity:1;transform:translateY(0) scale(1)} }
    .modal-hd {
        display: flex; align-items: center; justify-content: space-between;
        padding: 16px 20px; border-bottom: 1px solid var(--border);
    }
    .modal-title { font-family:'Space Grotesk',sans-serif; font-size: 15px; font-weight: 700; }
    .modal-close {
        width: 32px; height: 32px; border-radius: 10px;
        background: var(--bg-muted); border: none; cursor: pointer;
        color: var(--text-secondary); font-size: 14px;
        display: flex; align-items: center; justify-content: center;
    }
    .modal-close:hover { background: var(--red-bg); color: var(--red); }
    .scanner-wrap { position: relative; aspect-ratio: 1/1; width: 100%; background: #000; }
    #qr-reader { width: 100%; height: 100%; }
    #qr-reader video { object-fit: cover; }
    #qr-reader__dashboard_section_csr { display: none !important; }
    .scanner-overlay { position: absolute; inset: 0; pointer-events: none; display: flex; align-items: center; justify-content: center; }
    .scan-frame {
        width: 70%; aspect-ratio: 1/1; position: relative; border-radius: 8px;
    }
    .scan-frame::before, .scan-frame::after, .corner-tl, .corner-br {
        content: ''; position: absolute; width: 28px; height: 28px; border: 4px solid #fff;
    }
    .scan-frame::before { top:-4px; left:-4px; border-right:none; border-bottom:none; border-top-left-radius: 10px; }
    .scan-frame::after  { top:-4px; right:-4px; border-left:none; border-bottom:none; border-top-right-radius: 10px; }
    .corner-tl { bottom:-4px; left:-4px; border-right:none; border-top:none; border-bottom-left-radius: 10px; }
    .corner-br { bottom:-4px; right:-4px; border-left:none; border-top:none; border-bottom-right-radius: 10px; }
    .scan-line {
        position: absolute; left: 0; right: 0; height: 2px;
        background: linear-gradient(90deg, transparent, #E87A45, transparent);
        box-shadow: 0 0 12px #E87A45;
        animation: scanLine 2s linear infinite;
    }
    @keyframes scanLine { 0%{top:0%} 50%{top:100%} 100%{top:0%} }
    .modal-body { padding: 16px 20px 20px; }
    .modal-msg {
        padding: 10px 12px; border-radius: 10px;
        font-size: 12.5px; font-weight: 500;
        display: flex; align-items: center; gap: 8px;
        background: var(--blue-bg); color: var(--blue); border: 1px solid var(--blue-border);
    }

    /* Toast */
    .toast {
        position: fixed; top: 20px; left: 50%; transform: translateX(-50%);
        background: var(--bg-card); border: 1px solid var(--border);
        border-radius: 14px; padding: 12px 18px;
        box-shadow: 0 12px 32px rgba(45,31,20,.15);
        display: flex; align-items: center; gap: 10px;
        font-size: 13px; font-weight: 600;
        z-index: 200; max-width: calc(100vw - 32px);
        animation: toastIn .3s cubic-bezier(.22,.61,.36,1);
    }
    .toast.success { border-color: var(--green-border); background: var(--green-bg); color: var(--green); }
    .toast.error   { border-color: var(--red-border);   background: var(--red-bg);   color: var(--red); }
    .toast.info    { border-color: var(--blue-border);  background: var(--blue-bg);  color: var(--blue); }
    .toast.warning { border-color: var(--yellow-border);background: var(--yellow-bg);color: var(--yellow); }
    @keyframes toastIn { from{opacity:0;transform:translate(-50%,-16px)} to{opacity:1;transform:translate(-50%,0)} }

    @media (max-width:500px) {
        .hero-clock { font-size: 44px; }
        .hero-clock .secs { font-size: 22px; }
        .big-btn { min-width: 130px; font-size: 14px; height: 54px; }
    }
</style>
@endpush

@section('content')

<div class="grid-main" x-data="checkInApp()" x-init="init()">

    {{-- ═══════════ LEFT: Hero + Week strip ═══════════ --}}
    <div>
        <div class="hero-check">
            {{-- Row 1: date + status --}}
            <div class="hero-row hero-top">
                <div class="hero-date">{{ now()->format('l • F j') }}</div>
                <div class="hero-status" :class="'s-' + statusClass">
                    <span class="dot"></span>
                    <span x-text="statusText"></span>
                </div>
            </div>

            {{-- Row 2: Clock --}}
            <div class="hero-row">
                <div class="hero-clock-wrap">
                    <div class="hero-clock">
                        <span x-text="nowTime.hm"></span><span class="secs" x-text="':' + nowTime.s"></span>
                    </div>
                    <div class="hero-zone">PKT • {{ now()->format('T') }}</div>
                </div>
            </div>

            {{-- Shift progress --}}
            @if($shift)
                @php
                    $shiftStart = \Carbon\Carbon::parse(now()->format('Y-m-d') . ' ' . $shift->start_time);
                    $shiftEnd   = \Carbon\Carbon::parse(now()->format('Y-m-d') . ' ' . $shift->end_time);
                    if ($shift->is_night_shift) $shiftEnd->addDay();
                    $shiftTotalMins = $shiftEnd->diffInMinutes($shiftStart);
                    $elapsedMins = now()->lt($shiftStart) ? 0 : min($shiftTotalMins, now()->diffInMinutes($shiftStart));
                    $pct = $shiftTotalMins > 0 ? min(100, round(($elapsedMins / $shiftTotalMins) * 100)) : 0;
                @endphp
                <div class="hero-row">
                    <div class="shift-bar"><div class="shift-fill" style="width:{{ $pct }}%"></div></div>
                    <div class="shift-meta">
                        <span><i class="fa-solid fa-play" style="font-size:8px;"></i> {{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }}</span>
                        <span>{{ $shift->name }} • {{ $pct }}% of shift</span>
                        <span>{{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }} <i class="fa-solid fa-flag-checkered" style="font-size:8px;"></i></span>
                    </div>
                </div>
            @endif

            {{-- Assigned locations --}}
            <div class="hero-row" style="display:flex;flex-wrap:wrap;gap:6px;">
                @forelse($activeLocations as $loc)
                    <span class="loc-pill"><i class="fa-solid fa-location-dot"></i>{{ $loc->name }}</span>
                @empty
                    <span class="loc-pill" style="background:rgba(220,38,38,.15);border-color:rgba(220,38,38,.3);color:#fca5a5;">
                        <i class="fa-solid fa-triangle-exclamation" style="color:#fca5a5;"></i>
                        No location assigned — contact HR
                    </span>
                @endforelse
            </div>

            {{-- Break banner (only during active break) --}}
            <template x-if="onBreak">
                <div class="break-banner">
                    <i class="fa-solid fa-mug-hot"></i>
                    <div style="flex:1;min-width:0;">
                        <div class="break-banner-txt">On break <span x-text="breakReason ? '— ' + breakReason : ''"></span></div>
                        <div class="break-banner-time">Started <span x-text="breakStartedDisplay"></span> • <span x-text="liveBreakElapsed"></span></div>
                    </div>
                </div>
            </template>

            {{-- Actions --}}
            <div class="hero-actions">
                {{-- Check IN --}}
                <template x-if="!checkedIn">
                    <button class="big-btn primary" :disabled="busy || !hasLocation" @click="doCheckIn()">
                        <template x-if="busy && action==='in'"><span class="hero-spinner"></span></template>
                        <template x-if="!(busy && action==='in')"><i class="fa-solid fa-fingerprint"></i></template>
                        <span x-text="busy && action==='in' ? 'Checking in…' : 'Check In'"></span>
                    </button>
                </template>

                {{-- Break (when checked in, not on break, not done) --}}
                <template x-if="checkedIn && !checkedOut && !onBreak">
                    <div style="position:relative;flex:1;min-width:150px;" x-data="{menu:false}" @click.outside="menu=false">
                        <button class="big-btn break" style="width:100%;" :disabled="busy" @click="menu=!menu">
                            <template x-if="busy && action==='break'"><span class="hero-spinner" style="border-color:rgba(0,0,0,.2);border-top-color:#1a0f00;"></span></template>
                            <template x-if="!(busy && action==='break')"><i class="fa-solid fa-mug-hot"></i></template>
                            <span>Start Break</span>
                        </button>
                        <div class="break-menu" x-show="menu" x-transition style="display:none;">
                            <button class="break-opt" @click="menu=false;startBreak('lunch')"><i class="fa-solid fa-utensils"></i> Lunch</button>
                            <button class="break-opt" @click="menu=false;startBreak('prayer')"><i class="fa-solid fa-mosque"></i> Prayer</button>
                            <button class="break-opt" @click="menu=false;startBreak('tea')"><i class="fa-solid fa-mug-hot"></i> Tea / Snack</button>
                            <button class="break-opt" @click="menu=false;startBreak('personal')"><i class="fa-solid fa-user-clock"></i> Personal</button>
                            <button class="break-opt" @click="menu=false;startBreak(null)"><i class="fa-solid fa-pause"></i> Other</button>
                        </div>
                    </div>
                </template>

                {{-- Break ACTIVE — resume button --}}
                <template x-if="onBreak">
                    <button class="big-btn break-end" :disabled="busy" @click="endBreak()">
                        <template x-if="busy && action==='break-end'"><span class="hero-spinner"></span></template>
                        <template x-if="!(busy && action==='break-end')"><i class="fa-solid fa-play"></i></template>
                        <span>Resume Work</span>
                    </button>
                </template>

                {{-- Check OUT --}}
                <template x-if="checkedIn && !checkedOut && !onBreak">
                    <button class="big-btn out" :disabled="busy" @click="doCheckOut()">
                        <template x-if="busy && action==='out'"><span class="hero-spinner"></span></template>
                        <template x-if="!(busy && action==='out')"><i class="fa-solid fa-door-open"></i></template>
                        <span x-text="busy && action==='out' ? 'Checking out…' : 'Check Out'"></span>
                    </button>
                </template>

                {{-- Day done --}}
                <template x-if="checkedOut">
                    <button class="big-btn done" disabled>
                        <i class="fa-solid fa-circle-check"></i> Day Complete
                    </button>
                </template>

                {{-- QR --}}
                <template x-if="!checkedOut && !onBreak">
                    <button class="big-btn qr" :disabled="busy" @click="openScanner()">
                        <i class="fa-solid fa-qrcode"></i>
                        <span class="hide-on-mobile">Scan QR</span>
                    </button>
                </template>
            </div>

            {{-- Metric tiles --}}
            <div class="hero-metrics">
                <div class="hero-metric accent">
                    <div class="hero-metric-lbl"><i class="fa-solid fa-right-to-bracket"></i> Check-in</div>
                    <div class="hero-metric-val" x-text="checkInTime || '—'"></div>
                </div>
                <div class="hero-metric">
                    <div class="hero-metric-lbl"><i class="fa-solid fa-right-from-bracket"></i> Check-out</div>
                    <div class="hero-metric-val" x-text="checkOutTime || '—'"></div>
                </div>
                <div class="hero-metric">
                    <div class="hero-metric-lbl"><i class="fa-solid fa-business-time"></i> Working</div>
                    <div class="hero-metric-val">
                        <span x-text="workingHuman.h"></span><span class="unit">h</span>
                        <span x-text="workingHuman.m"></span><span class="unit">m</span>
                    </div>
                </div>
                <div class="hero-metric warn">
                    <div class="hero-metric-lbl"><i class="fa-solid fa-mug-hot"></i> Break</div>
                    <div class="hero-metric-val">
                        <span x-text="breakHuman.h"></span><span class="unit">h</span>
                        <span x-text="breakHuman.m"></span><span class="unit">m</span>
                    </div>
                </div>
                {{-- Late + overtime tiles — only if relevant --}}
                <template x-if="lateMinutes > 0">
                    <div class="hero-metric late">
                        <div class="hero-metric-lbl"><i class="fa-solid fa-clock"></i> Late</div>
                        <div class="hero-metric-val">
                            <span x-text="formatMins(lateMinutes).h"></span><span class="unit">h</span>
                            <span x-text="formatMins(lateMinutes).m"></span><span class="unit">m</span>
                        </div>
                    </div>
                </template>
                <template x-if="overtimeMinutes > 0">
                    <div class="hero-metric" style="background:rgba(124,58,237,.14);border-color:rgba(124,58,237,.25);">
                        <div class="hero-metric-lbl"><i class="fa-solid fa-bolt"></i> Overtime</div>
                        <div class="hero-metric-val">
                            <span x-text="formatMins(overtimeMinutes).h"></span><span class="unit">h</span>
                            <span x-text="formatMins(overtimeMinutes).m"></span><span class="unit">m</span>
                        </div>
                    </div>
                </template>
                <template x-if="earlyLeaveMinutes > 0">
                    <div class="hero-metric" style="background:rgba(236,72,153,.14);border-color:rgba(236,72,153,.25);">
                        <div class="hero-metric-lbl"><i class="fa-solid fa-person-walking-arrow-right"></i> Early out</div>
                        <div class="hero-metric-val">
                            <span x-text="formatMins(earlyLeaveMinutes).h"></span><span class="unit">h</span>
                            <span x-text="formatMins(earlyLeaveMinutes).m"></span><span class="unit">m</span>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Break history today --}}
            @if($today && $today->breakSessions->count())
                <div class="hero-row" style="display:flex;flex-wrap:wrap;gap:6px;">
                    <div style="font-size:10px;color:rgba(255,255,255,.5);letter-spacing:.6px;text-transform:uppercase;width:100%;font-weight:600;margin-bottom:2px;">
                        Today's breaks
                    </div>
                    @foreach($today->breakSessions as $bs)
                        <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:999px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);font-size:10.5px;color:rgba(255,255,255,.75);">
                            <i class="fa-solid {{ \App\Models\BreakSession::reasonIcon($bs->reason) }}" style="font-size:9px;color:#F59E0B;"></i>
                            {{ \App\Models\BreakSession::reasonLabel($bs->reason) }}
                            @if($bs->ended_at)
                                • {{ $bs->duration_minutes }}m
                            @else
                                • <span style="color:#FBBF24;">active</span>
                            @endif
                        </span>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- 7-day strip --}}
        <div class="card" style="margin-top:18px;">
            <div class="card-hd">
                <div>
                    <div class="card-title">This Week</div>
                    <div class="card-sub">Last 7 days at a glance</div>
                </div>
                <a href="{{ route('employee.attendance.index') }}" class="btn btn-ghost" style="padding:6px 12px;font-size:12px;">
                    View all <i class="fa-solid fa-arrow-right" style="font-size:10px;"></i>
                </a>
            </div>
            <div class="strip">
                @foreach($last7 as $d)
                    @php
                        $cls = match($d['status']) {
                            'present','work_from_home' => 'present',
                            'late' => 'late',
                            'absent' => 'absent',
                            'on_leave' => 'leave',
                            'holiday','weekend' => 'weekend',
                            default => 'off',
                        };
                    @endphp
                    <div class="strip-day {{ $d['is_today'] ? 'today' : '' }}">
                        <div class="strip-dow">{{ $d['date'] }}</div>
                        <div class="strip-num">{{ $d['day'] }}</div>
                        <div class="strip-dot {{ $cls }}"></div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ═══════════ RIGHT: Stats column ═══════════ --}}
    <div>
        <div class="card" style="margin-bottom:16px;">
            <div class="card-hd">
                <div>
                    <div class="card-title">This Month</div>
                    <div class="card-sub">{{ now()->format('F Y') }}</div>
                </div>
                <span class="badge badge-accent">
                    <i class="fa-solid fa-calendar-check"></i>
                    {{ now()->day }}
                </span>
            </div>
            <div class="stats-grid">
                <div class="tiny-card" style="padding:14px;background:var(--green-bg);border-color:var(--green-border);">
                    <div class="tiny-title" style="color:var(--green);">Present</div>
                    <div class="tiny-val" style="color:var(--green);">{{ $monthStats->present_days ?? 0 }}</div>
                    <div class="tiny-sub" style="color:var(--green);opacity:.8;">days</div>
                </div>
                <div class="tiny-card" style="padding:14px;background:var(--yellow-bg);border-color:var(--yellow-border);">
                    <div class="tiny-title" style="color:var(--yellow);">Late</div>
                    <div class="tiny-val" style="color:var(--yellow);">{{ $monthStats->late_days ?? 0 }}</div>
                    <div class="tiny-sub" style="color:var(--yellow);opacity:.8;">days</div>
                </div>
                <div class="tiny-card" style="padding:14px;background:var(--red-bg);border-color:var(--red-border);">
                    <div class="tiny-title" style="color:var(--red);">Absent</div>
                    <div class="tiny-val" style="color:var(--red);">{{ $monthStats->absent_days ?? 0 }}</div>
                    <div class="tiny-sub" style="color:var(--red);opacity:.8;">days</div>
                </div>
                <div class="tiny-card" style="padding:14px;background:var(--purple-bg);border-color:var(--purple-border);">
                    <div class="tiny-title" style="color:var(--purple);">Overtime</div>
                    <div class="tiny-val" style="color:var(--purple);">{{ intdiv($monthStats->overtime_minutes ?? 0, 60) }}</div>
                    <div class="tiny-sub" style="color:var(--purple);opacity:.8;">hours</div>
                </div>
                <div class="tiny-card" style="padding:14px;">
                    <div class="tiny-title">Total Worked</div>
                    <div class="tiny-val">{{ intdiv($monthStats->total_minutes ?? 0, 60) }}<span style="font-size:14px;color:var(--text-muted);">h</span></div>
                    <div class="tiny-sub">{{ ($monthStats->total_minutes ?? 0) % 60 }}m</div>
                </div>
                <div class="tiny-card" style="padding:14px;">
                    <div class="tiny-title">Total Break</div>
                    <div class="tiny-val">{{ intdiv($monthStats->break_minutes_total ?? 0, 60) }}<span style="font-size:14px;color:var(--text-muted);">h</span></div>
                    <div class="tiny-sub">{{ ($monthStats->break_minutes_total ?? 0) % 60 }}m</div>
                </div>
            </div>
        </div>

        {{-- Employee --}}
        <div class="card" style="margin-bottom:16px;">
            <div class="card-hd"><div class="card-title">You</div></div>
            <div style="display:flex;align-items:center;gap:14px;">
                <img src="{{ auth()->user()->avatar_url }}" style="width:54px;height:54px;border-radius:14px;object-fit:cover;" alt="">
                <div style="flex:1;min-width:0;">
                    <div style="font-size:15px;font-weight:700;">{{ $employee->full_name }}</div>
                    <div style="font-size:11.5px;color:var(--text-muted);">{{ $employee->employee_id }}</div>
                    <div style="font-size:11.5px;color:var(--text-muted);margin-top:2px;">{{ $employee->designation?->name ?? '—' }}</div>
                </div>
            </div>
            @if($shift)
            <div style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:var(--bg-muted);border-radius:10px;margin-top:12px;">
                <i class="fa-solid fa-clock" style="color:var(--accent);font-size:14px;"></i>
                <div style="flex:1;">
                    <div style="font-size:12px;font-weight:600;">{{ $shift->name }}</div>
                    <div style="font-size:10.5px;color:var(--text-muted);">{{ $shift->timing }} • {{ $shift->break_minutes }}m allowed break</div>
                </div>
            </div>
            @endif
        </div>

        @if($activeLocations->count())
        <div class="card">
            <div class="card-hd">
                <div>
                    <div class="card-title">Your Locations</div>
                    <div class="card-sub">Where you can check in</div>
                </div>
            </div>
            @foreach($activeLocations as $loc)
                <div class="loc-item">
                    <div class="loc-ico"><i class="fa-solid {{ $loc->typeIcon() }}"></i></div>
                    <div style="flex:1;min-width:0;">
                        <div class="loc-name">{{ $loc->name }}{{ $loc->pivot->is_primary ? ' ★' : '' }}</div>
                        <div class="loc-meta">{{ $loc->city ?? $loc->typeLabel() }} • {{ $loc->radius_meters }}m radius</div>
                    </div>
                </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

{{-- QR scanner modal --}}
<template x-teleport="body">
    <div x-data="{}" x-show="$store.qr.open" style="display:none;">
        <div class="modal-overlay" @click.self="$store.qr.close()">
            <div class="modal-box">
                <div class="modal-hd">
                    <div class="modal-title"><i class="fa-solid fa-qrcode" style="color:var(--accent);margin-right:8px;"></i>Scan Location QR</div>
                    <button class="modal-close" @click="$store.qr.close()"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="scanner-wrap">
                    <div id="qr-reader"></div>
                    <div class="scanner-overlay">
                        <div class="scan-frame">
                            <div class="corner-tl"></div>
                            <div class="corner-br"></div>
                            <div class="scan-line"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="modal-msg">
                        <i class="fa-solid fa-circle-info"></i>
                        <span>Point your camera at the location's QR code.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

{{-- Toast --}}
<div x-data x-show="$store.toast.show" x-transition class="toast" :class="$store.toast.type" style="display:none;">
    <i class="fa-solid" :class="{'fa-circle-check':$store.toast.type==='success','fa-circle-xmark':$store.toast.type==='error','fa-circle-info':$store.toast.type==='info','fa-triangle-exclamation':$store.toast.type==='warning'}"></i>
    <span x-text="$store.toast.msg"></span>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('toast', {
        show:false, msg:'', type:'info',
        push(msg, type='info', ms=4000) {
            this.msg=msg; this.type=type; this.show=true;
            clearTimeout(this._t); this._t=setTimeout(()=>this.show=false, ms);
        }
    });
    Alpine.store('qr', {
        open:false, _scanner:null, _callback:null,
        async start(cb) {
            this.open=true; this._callback=cb;
            await Alpine.nextTick();
            setTimeout(()=>this._boot(), 100);
        },
        _boot() {
            try {
                this._scanner = new Html5Qrcode("qr-reader");
                this._scanner.start(
                    { facingMode: "environment" },
                    { fps: 10, qrbox: { width: 250, height: 250 } },
                    decoded => { if (this._callback) this._callback(decoded); this.close(); },
                    () => {}
                ).catch(() => {
                    Alpine.store('toast').push('Camera access denied or unavailable','error');
                    this.open=false;
                });
            } catch(e) {
                Alpine.store('toast').push('Camera not available on this device','error');
                this.open=false;
            }
        },
        close() {
            if (this._scanner) {
                try { this._scanner.stop().then(()=>this._scanner.clear()).catch(()=>{}); } catch(e){}
                this._scanner=null;
            }
            this.open=false; this._callback=null;
        }
    });
});

function checkInApp() {
    return {
        nowTime: { hm:'00:00', s:'00' },
        busy: false,
        action: null,

        // attendance state
        checkedIn:    @json((bool) $today?->check_in),
        checkedOut:   @json((bool) $today?->check_out),
        checkInTime:  @json($today?->check_in  ? \Carbon\Carbon::parse($today->check_in)->format('h:i A')  : null),
        checkOutTime: @json($today?->check_out ? \Carbon\Carbon::parse($today->check_out)->format('h:i A') : null),
        checkInISO:   @json($today?->check_in?->toIso8601String()),
        checkOutISO:  @json($today?->check_out?->toIso8601String()),
        hasLocation:  @json($activeLocations->count() > 0),

        // field breakdown (server-computed values)
        storedBreakMins: @json($today->break_minutes ?? 0),
        lateMinutes:     @json($today->late_minutes ?? 0),
        overtimeMinutes: @json($today->overtime_minutes ?? 0),
        earlyLeaveMinutes: @json($today->early_leave_minutes ?? 0),

        // break
        onBreak:        @json((bool) $activeBreak),
        breakStartISO:  @json($activeBreak?->started_at?->toIso8601String()),
        breakReason:    @json($activeBreak ? \App\Models\BreakSession::reasonLabel($activeBreak->reason) : null),
        liveBreakElapsed: '0m',
        breakStartedDisplay: @json($activeBreak ? \Carbon\Carbon::parse($activeBreak->started_at)->format('h:i A') : null),

        init() {
            this.tick(); setInterval(()=>this.tick(), 1000);
        },
        tick() {
            // live clock
            const parts = new Intl.DateTimeFormat('en-GB', {
                timeZone: 'Asia/Karachi',
                hour:'2-digit', minute:'2-digit', second:'2-digit', hour12:false
            }).formatToParts(new Date()).reduce((a,p)=>(a[p.type]=p.value,a),{});
            this.nowTime = { hm: parts.hour+':'+parts.minute, s: parts.second };

            // live break timer
            if (this.onBreak && this.breakStartISO) {
                const diff = Math.max(0, Math.floor((Date.now() - new Date(this.breakStartISO).getTime()) / 60000));
                const h = Math.floor(diff/60), m = diff%60;
                this.liveBreakElapsed = h ? `${h}h ${m}m` : `${m}m`;
            }
        },

        get statusClass() {
            if (this.checkedOut) return 'done';
            if (this.onBreak)    return 'break';
            if (this.checkedIn)  return 'in';
            return 'out';
        },
        get statusText() {
            if (this.checkedOut) return 'Completed';
            if (this.onBreak)    return 'On break';
            if (this.checkedIn)  return 'At work';
            return 'Not checked in';
        },

        get workingHuman() {
            if (!this.checkInISO) return { h:'0', m:'00' };
            const start = new Date(this.checkInISO);
            const end   = this.checkedOut ? new Date(this.checkOutISO) : new Date();
            let mins = Math.max(0, Math.floor((end - start) / 60000));
            // subtract break time (server stored + live active break)
            mins -= this.storedBreakMins;
            if (this.onBreak && this.breakStartISO) {
                mins -= Math.max(0, Math.floor((Date.now() - new Date(this.breakStartISO).getTime()) / 60000));
            }
            mins = Math.max(0, mins);
            return { h: String(Math.floor(mins/60)), m: String(mins%60).padStart(2,'0') };
        },

        get breakHuman() {
            let mins = this.storedBreakMins;
            if (this.onBreak && this.breakStartISO) {
                mins += Math.max(0, Math.floor((Date.now() - new Date(this.breakStartISO).getTime()) / 60000));
            }
            return { h: String(Math.floor(mins/60)), m: String(mins%60).padStart(2,'0') };
        },

        formatMins(m) {
            m = Math.max(0, m|0);
            return { h: String(Math.floor(m/60)), m: String(m%60).padStart(2,'0') };
        },

        // ═════ Geolocation ═════
        getPosition() {
            return new Promise((resolve, reject) => {
                if (!navigator.geolocation) return reject(new Error('Geolocation is not supported.'));
                navigator.geolocation.getCurrentPosition(
                    pos => resolve({ lat:pos.coords.latitude, lng:pos.coords.longitude, accuracy:pos.coords.accuracy }),
                    err => {
                        const msg = { 1:'Location permission denied. Please allow location access.',
                                      2:'Location unavailable. Check your GPS signal.',
                                      3:'Location request timed out.' }[err.code] || 'Unable to get location.';
                        reject(new Error(msg));
                    },
                    { enableHighAccuracy:true, timeout:15000, maximumAge:0 }
                );
            });
        },

        // ═════ Check in/out ═════
        async doCheckIn(qrToken=null) {
            if (this.busy) return;
            this.busy=true; this.action='in';
            try {
                Alpine.store('toast').push('Getting your location…','info',2000);
                const pos = await this.getPosition();
                await this._submit('in', { ...pos, qr_token: qrToken });
            } catch(e) { Alpine.store('toast').push(e.message || 'Check-in failed','error'); }
            finally { this.busy=false; this.action=null; }
        },
        async doCheckOut(qrToken=null) {
            if (this.busy) return;
            if (this.onBreak) { Alpine.store('toast').push('End your break first.','warning'); return; }
            this.busy=true; this.action='out';
            try {
                Alpine.store('toast').push('Getting your location…','info',2000);
                const pos = await this.getPosition();
                await this._submit('out', { ...pos, qr_token: qrToken });
            } catch(e) { Alpine.store('toast').push(e.message || 'Check-out failed','error'); }
            finally { this.busy=false; this.action=null; }
        },
        async _submit(type, payload) {
            const url = type==='in'
                ? @json(route('employee.attendance.check-in'))
                : @json(route('employee.attendance.check-out'));
            const res = await fetch(url, {
                method:'POST',
                headers: {
                    'Content-Type':'application/json',
                    'Accept':'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With':'XMLHttpRequest',
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (!res.ok) { Alpine.store('toast').push(data.message || 'Something went wrong','error',5000); return; }

            if (type==='in') {
                this.checkedIn = true;
                this.checkInTime = new Date(data.attendance.check_in).toLocaleTimeString('en-US',{hour:'2-digit',minute:'2-digit',hour12:true});
                this.checkInISO  = data.attendance.check_in;
                this.lateMinutes = data.attendance.late_minutes || 0;
            } else {
                this.checkedOut = true;
                this.checkOutTime = new Date(data.attendance.check_out).toLocaleTimeString('en-US',{hour:'2-digit',minute:'2-digit',hour12:true});
                this.checkOutISO  = data.attendance.check_out;
                this.storedBreakMins    = data.attendance.break_minutes || 0;
                this.overtimeMinutes    = data.attendance.overtime_minutes || 0;
                this.earlyLeaveMinutes  = data.attendance.early_leave_minutes || 0;
                this.lateMinutes        = data.attendance.late_minutes || 0;
            }
            Alpine.store('toast').push(data.message + (data.distance != null ? ' (~'+data.distance+'m)' : ''),'success',5000);
        },

        // ═════ Breaks ═════
        async startBreak(reason) {
            if (this.busy || !this.checkedIn || this.checkedOut) return;
            this.busy=true; this.action='break';
            try {
                const res = await fetch(@json(route('employee.attendance.break.start')), {
                    method:'POST',
                    headers:{
                        'Content-Type':'application/json','Accept':'application/json',
                        'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With':'XMLHttpRequest'
                    },
                    body: JSON.stringify({ reason })
                });
                const data = await res.json();
                if (!res.ok) { Alpine.store('toast').push(data.message,'error'); return; }

                this.onBreak = true;
                this.breakStartISO = data.break.started_at;
                this.breakStartedDisplay = new Date(data.break.started_at).toLocaleTimeString('en-US',{hour:'2-digit',minute:'2-digit',hour12:true});
                this.breakReason = reason ? ({lunch:'Lunch',prayer:'Prayer',tea:'Tea break',personal:'Personal'})[reason] : 'Break';
                Alpine.store('toast').push('Break started','success');
            } catch(e) { Alpine.store('toast').push('Failed to start break','error'); }
            finally { this.busy=false; this.action=null; }
        },
        async endBreak() {
            if (this.busy || !this.onBreak) return;
            this.busy=true; this.action='break-end';
            try {
                const res = await fetch(@json(route('employee.attendance.break.end')), {
                    method:'POST',
                    headers:{
                        'Content-Type':'application/json','Accept':'application/json',
                        'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With':'XMLHttpRequest'
                    }
                });
                const data = await res.json();
                if (!res.ok) { Alpine.store('toast').push(data.message,'error'); return; }

                this.storedBreakMins += (data.minutes || 0);
                this.onBreak = false;
                this.breakStartISO = null;
                this.breakReason = null;
                Alpine.store('toast').push(data.message,'success');
            } catch(e) { Alpine.store('toast').push('Failed to end break','error'); }
            finally { this.busy=false; this.action=null; }
        },

        // ═════ QR ═════
        openScanner() {
            if (this.busy) return;
            Alpine.store('qr').start(decoded => {
                let token = decoded;
                try { const obj = JSON.parse(decoded); if (obj && obj.token) token = obj.token; } catch(e) {}
                if (!this.checkedIn)       this.doCheckIn(token);
                else if (!this.checkedOut) this.doCheckOut(token);
                else Alpine.store('toast').push('You have already completed today','info');
            });
        }
    };
}
</script>
@endpush