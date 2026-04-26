@extends('employee.layouts.app')
@section('title', 'My Profile')
@section('page-title', 'My Profile')
@section('page-sub', 'Manage your personal information & documents')

@push('styles')
<style>
    /* ═══════════════════════════════════════════════════
       PROFILE PAGE
    ═══════════════════════════════════════════════════ */
    .prof-wrap { max-width: 980px; }

    /* ═══════════════════════════════════════════════════
       HERO
    ═══════════════════════════════════════════════════ */
    .prof-hero {
        background: linear-gradient(135deg, #2D1F14 0%, #3d2a1c 100%);
        color: #fff; border-radius: 20px;
        position: relative; overflow: hidden;
        margin-bottom: 18px;
    }
    .prof-hero::before {
        content:''; position:absolute; top:-60%; right:-15%;
        width:480px; height:480px;
        background: radial-gradient(circle, rgba(232,122,69,.22), transparent 60%);
        pointer-events:none;
    }
    .prof-hero::after {
        content:''; position:absolute; bottom:-40%; left:-10%;
        width:320px; height:320px;
        background: radial-gradient(circle, rgba(245,158,11,.15), transparent 60%);
        pointer-events:none;
    }

    .prof-hero-body {
        padding: 28px;
        position: relative; z-index: 1;
        display: flex; align-items: center; gap: 22px;
        flex-wrap: wrap;
    }

    /* Avatar */
    .avatar-wrap { position: relative; }
    .prof-avatar {
        width: 110px; height: 110px; border-radius: 28px;
        object-fit: cover;
        border: 3px solid rgba(255,255,255,.15);
        box-shadow: 0 10px 30px rgba(0,0,0,.25);
    }
    .avatar-badge-edit {
        position: absolute; bottom: -4px; right: -4px;
        width: 34px; height: 34px; border-radius: 50%;
        background: var(--accent-grad);
        border: 3px solid #3d2a1c;
        display: flex; align-items: center; justify-content: center;
        color: #fff; cursor: pointer;
        box-shadow: 0 4px 12px rgba(194,83,27,.4);
        transition: transform .15s;
    }
    .avatar-badge-edit:hover { transform: scale(1.1); }
    .avatar-badge-edit input { display: none; }

    .prof-name {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 24px; font-weight: 700; line-height: 1.2;
    }
    .prof-desig { font-size: 13px; color: rgba(255,255,255,.75); margin-top: 4px; }
    .prof-meta {
        display: flex; gap: 8px; flex-wrap: wrap;
        margin-top: 12px;
    }
    .prof-meta-chip {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 5px 11px; border-radius: 999px;
        background: rgba(255,255,255,.08);
        border: 1px solid rgba(255,255,255,.12);
        font-size: 11px; font-weight: 500;
        color: rgba(255,255,255,.85);
    }
    .prof-meta-chip i { font-size: 10px; color: #E87A45; }

    .prof-body-spacer { flex: 1; min-width: 200px; }

    /* Completion % */
    .completion-circle {
        width: 86px; height: 86px;
        position: relative;
        display: flex; align-items: center; justify-content: center;
    }
    .completion-circle svg { width: 100%; height: 100%; transform: rotate(-90deg); }
    .completion-circle .bg {
        fill: none; stroke: rgba(255,255,255,.1); stroke-width: 7;
    }
    .completion-circle .fg {
        fill: none; stroke: url(#grad); stroke-width: 7; stroke-linecap: round;
        transition: stroke-dashoffset .6s ease;
    }
    .completion-num {
        position: absolute; font-family:'Space Grotesk',sans-serif;
        font-size: 20px; font-weight: 700; color: #fff;
        font-variant-numeric: tabular-nums;
    }
    .completion-num small { font-size: 10px; color: rgba(255,255,255,.55); font-weight: 500; margin-left: 1px; }

    .completion-wrap {
        display: flex; align-items: center; gap: 12px;
    }
    .completion-txt-lbl { font-size: 10px; color: rgba(255,255,255,.55); letter-spacing: .8px; text-transform: uppercase; font-weight: 600; }
    .completion-txt-val { font-size: 13px; font-weight: 700; margin-top: 2px; }

    /* ═══════════════════════════════════════════════════
       TABS
    ═══════════════════════════════════════════════════ */
    .tab-bar {
        display: flex; gap: 4px;
        background: var(--bg-card); border: 1px solid var(--border);
        border-radius: 12px; padding: 4px;
        overflow-x: auto;
        margin-bottom: 18px;
    }
    .tab-bar a {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 9px 16px; border-radius: 8px;
        font-size: 13px; font-weight: 600;
        color: var(--text-secondary); text-decoration: none;
        white-space: nowrap;
        transition: all .15s;
    }
    .tab-bar a.active { background: var(--accent-bg); color: var(--accent); }
    .tab-bar a:not(.active):hover { background: var(--bg-hover); color: var(--accent); }
    .tab-bar a i { font-size: 12px; }
    .tab-count {
        font-size: 10px; padding: 2px 6px; border-radius: 6px;
        background: rgba(194,83,27,.15); color: var(--accent);
        font-weight: 700;
    }
    .tab-bar a.active .tab-count { background: var(--accent); color: #fff; }

    /* ═══════════════════════════════════════════════════
       SHARED (cards, fields)
    ═══════════════════════════════════════════════════ */
    .info-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 16px;
        margin-bottom: 16px;
        overflow: hidden;
    }
    .info-card-hd {
        padding: 14px 18px;
        border-bottom: 1px solid var(--border);
        display: flex; align-items: center; justify-content: space-between;
    }
    .info-card-title {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 13px; font-weight: 700; letter-spacing: .3px;
    }
    .info-card-title i { margin-right: 6px; color: var(--accent); }
    .info-card-sub { font-size: 11px; color: var(--text-muted); margin-top: 2px; }

    .info-grid {
        display: grid; grid-template-columns: 1fr 1fr; gap: 0;
    }
    @media (max-width: 640px) { .info-grid { grid-template-columns: 1fr; } }

    .info-row {
        padding: 14px 18px;
        border-bottom: 1px solid var(--border);
        border-right: 1px solid var(--border);
    }
    .info-grid > .info-row:nth-child(2n) { border-right: none; }
    .info-grid > .info-row:nth-last-child(-n+2):not(:nth-last-child(odd)) { border-bottom: none; }
    .info-grid > .info-row:nth-last-child(1) { border-bottom: none; }
    @media (max-width: 640px) {
        .info-row { border-right: none; }
    }

    .info-lbl {
        font-size: 10.5px; color: var(--text-muted);
        letter-spacing: .6px; text-transform: uppercase; font-weight: 600;
    }
    .info-val {
        font-size: 13.5px; font-weight: 600; color: var(--text-primary);
        margin-top: 4px;
        word-break: break-word;
    }
    .info-val.empty { color: var(--text-muted); font-style: italic; font-weight: 400; }
    .info-val.locked {
        display: inline-flex; align-items: center; gap: 5px;
    }
    .info-val.locked::after {
        content: ''; display: inline-block;
        width: 13px; height: 13px;
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='13' height='13' viewBox='0 0 24 24' fill='%23A89080'%3E%3Cpath d='M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zM9 6c0-1.66 1.34-3 3-3s3 1.34 3 3v2H9V6z'/%3E%3C/svg%3E");
        background-size: contain;
    }

    /* ═══════════════════════════════════════════════════
       EDIT FORM
    ═══════════════════════════════════════════════════ */
    .form-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 22px;
        margin-bottom: 16px;
    }

    .form-section-title {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 13px; font-weight: 700;
        margin-bottom: 14px; letter-spacing: .3px;
        display: flex; align-items: center; gap: 8px;
    }
    .form-section-title i { color: var(--accent); }

    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px; }
    @media (max-width: 640px) { .form-row { grid-template-columns: 1fr; } }
    .form-row.triple { grid-template-columns: 1fr 1fr 1fr; }
    @media (max-width: 640px) { .form-row.triple { grid-template-columns: 1fr; } }
    .form-row.full { grid-template-columns: 1fr; }

    .field { }
    .field label {
        display: block;
        font-size: 11px; font-weight: 600;
        color: var(--text-primary);
        letter-spacing: .5px; text-transform: uppercase;
        margin-bottom: 8px;
    }
    .field label .hint { color: var(--text-muted); font-weight: 500; text-transform: none; letter-spacing: 0; font-size: 10px; margin-left: 6px; }
    .field input[type="text"],
    .field input[type="email"],
    .field input[type="tel"],
    .field input[type="date"],
    .field input[type="password"],
    .field select,
    .field textarea {
        width: 100%; height: 42px;
        padding: 0 14px;
        background: var(--bg-input);
        border: 1.5px solid transparent;
        border-radius: 10px;
        font: inherit; font-size: 13.5px; color: var(--text-primary);
        transition: all .15s;
    }
    .field textarea { height: auto; padding: 10px 14px; min-height: 80px; resize: vertical; }
    .field input:focus, .field select:focus, .field textarea:focus {
        outline: none; border-color: var(--accent); background: #fff;
        box-shadow: 0 0 0 3px rgba(194,83,27,.08);
    }
    .field input:disabled, .field input[readonly] {
        background: var(--bg-muted); color: var(--text-muted); cursor: not-allowed;
    }
    .field-err {
        font-size: 11.5px; color: var(--red); margin-top: 6px; font-weight: 600;
        display: flex; align-items: center; gap: 5px;
    }

    .form-actions {
        display: flex; justify-content: flex-end; gap: 10px;
        padding-top: 18px; border-top: 1px solid var(--border);
        margin-top: 10px;
    }

    /* ═══════════════════════════════════════════════════
       PASSWORD STRENGTH
    ═══════════════════════════════════════════════════ */
    .pw-strength-bar {
        height: 6px; border-radius: 999px;
        background: var(--bg-muted); overflow: hidden;
        margin-top: 8px;
    }
    .pw-strength-fill {
        height: 100%; width: 0%;
        border-radius: 999px;
        transition: all .3s;
    }
    .pw-strength-fill.weak   { background: var(--red);    width: 25%; }
    .pw-strength-fill.fair   { background: var(--yellow); width: 50%; }
    .pw-strength-fill.good   { background: #3b82f6;       width: 75%; }
    .pw-strength-fill.strong { background: var(--green);  width: 100%; }

    .pw-strength-label { font-size: 11px; font-weight: 700; margin-top: 6px; letter-spacing: .3px; }
    .pw-strength-label.weak   { color: var(--red); }
    .pw-strength-label.fair   { color: var(--yellow); }
    .pw-strength-label.good   { color: #3b82f6; }
    .pw-strength-label.strong { color: var(--green); }

    .pw-reqs { margin-top: 10px; font-size: 11.5px; }
    .pw-req {
        display: flex; align-items: center; gap: 6px;
        padding: 3px 0;
        color: var(--text-muted);
        transition: color .15s;
    }
    .pw-req.met { color: var(--green); }
    .pw-req.met i { color: var(--green); }
    .pw-req i { width: 14px; color: var(--border-strong); font-size: 10px; }

    /* ═══════════════════════════════════════════════════
       DOCUMENTS
    ═══════════════════════════════════════════════════ */
    .doc-upload-box {
        background: var(--bg-card);
        border: 2px dashed var(--border-strong);
        border-radius: 16px;
        padding: 24px;
        text-align: center;
        cursor: pointer;
        transition: all .15s;
        margin-bottom: 20px;
    }
    .doc-upload-box:hover { border-color: var(--accent); background: var(--bg-hover); }
    .doc-upload-ico {
        width: 52px; height: 52px;
        background: var(--accent-bg); color: var(--accent);
        border-radius: 14px; margin: 0 auto 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px;
    }
    .doc-upload-hd { font-size: 15px; font-weight: 700; color: var(--text-primary); font-family: 'Space Grotesk',sans-serif; }
    .doc-upload-sub { font-size: 12px; color: var(--text-muted); margin-top: 4px; }

    .doc-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 14px; }

    .doc-item {
        background: var(--bg-card); border: 1px solid var(--border);
        border-radius: 14px; padding: 16px;
        display: flex; gap: 12px;
        transition: all .15s;
    }
    .doc-item:hover { border-color: var(--accent-border); box-shadow: 0 4px 12px rgba(45,31,20,.05); }
    .doc-ico-wrap {
        width: 44px; height: 44px; border-radius: 11px;
        flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px;
    }
    .doc-ico-wrap.pdf  { background: rgba(220,38,38,.1);  color: #DC2626; }
    .doc-ico-wrap.img  { background: rgba(59,130,246,.1); color: #3B82F6; }
    .doc-ico-wrap.doc  { background: rgba(37,99,235,.1);  color: #2563EB; }
    .doc-ico-wrap.other{ background: var(--bg-muted); color: var(--text-muted); }

    .doc-body { flex: 1; min-width: 0; }
    .doc-title {
        font-size: 13px; font-weight: 700; color: var(--text-primary);
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .doc-meta {
        font-size: 11px; color: var(--text-muted); margin-top: 2px;
        display: flex; gap: 8px; align-items: center; flex-wrap: wrap;
    }
    .doc-meta .sep { color: var(--border-strong); }
    .doc-status {
        margin-top: 8px;
        display: flex; gap: 4px; flex-wrap: wrap;
    }
    .doc-actions {
        display: flex; flex-direction: column; gap: 4px;
    }
    .doc-act-btn {
        width: 30px; height: 30px; border-radius: 8px;
        background: var(--bg-muted); border: none; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        color: var(--text-secondary); font-size: 12px;
        transition: all .15s;
        text-decoration: none;
    }
    .doc-act-btn:hover { background: var(--accent-bg); color: var(--accent); }
    .doc-act-btn.danger:hover { background: var(--red-bg); color: var(--red); }

    /* ═══════════════════════════════════════════════════
       COMPLETION BREAKDOWN
    ═══════════════════════════════════════════════════ */
    .completion-grid {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 10px;
    }
    .completion-item {
        padding: 12px 14px;
        background: var(--bg-muted);
        border-radius: 12px;
    }
    .completion-item-hd {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 8px;
    }
    .completion-item-name { font-size: 12px; font-weight: 700; color: var(--text-primary); }
    .completion-item-pct { font-size: 11.5px; font-weight: 700; color: var(--text-secondary); font-variant-numeric: tabular-nums; }
    .completion-item-bar { height: 5px; border-radius: 999px; background: var(--bg-card); overflow: hidden; }
    .completion-item-fill {
        height: 100%; border-radius: 999px;
        background: var(--accent-grad);
        transition: width .4s ease;
    }
    .completion-item.done .completion-item-fill { background: linear-gradient(90deg,#16A34A,#22C55E); }

    /* Modal */
    .modal-overlay {
        position: fixed; inset: 0; z-index: 100;
        background: rgba(15,10,5,.6); backdrop-filter: blur(5px);
        display: flex; align-items: center; justify-content: center;
        padding: 16px;
        animation: fadeIn .2s ease;
    }
    @keyframes fadeIn { from{opacity:0} to{opacity:1} }
    .modal-box {
        background: var(--bg-card); border-radius: 20px;
        width: 100%; max-width: 540px;
        max-height: 90vh; overflow-y: auto;
        animation: slideUp .25s cubic-bezier(.22,.61,.36,1);
    }
    @keyframes slideUp { from{opacity:0;transform:translateY(30px) scale(.96)} to{opacity:1;transform:translateY(0) scale(1)} }
    .modal-hd {
        padding: 18px 22px; border-bottom: 1px solid var(--border);
        display: flex; align-items: center; justify-content: space-between;
    }
    .modal-title { font-family:'Space Grotesk',sans-serif; font-size: 16px; font-weight: 700; }
    .modal-close {
        width: 34px; height: 34px; border-radius: 10px;
        background: var(--bg-muted); border: none; cursor: pointer;
        color: var(--text-secondary);
        display: flex; align-items: center; justify-content: center;
    }
    .modal-body { padding: 22px; }
</style>
@endpush

@section('content')

<div class="prof-wrap">

    {{-- ═══════════════════════════ HERO ═══════════════════════════ --}}
    <div class="prof-hero">
        <div class="prof-hero-body">
            {{-- Avatar --}}
            <div class="avatar-wrap">
                <img src="{{ $employee->avatar ? asset('storage/'.$employee->avatar) : auth()->user()->avatar_url }}"
                     class="prof-avatar" alt="{{ $employee->full_name }}">
                <form method="POST" action="{{ route('employee.profile.avatar') }}" enctype="multipart/form-data" id="avatarForm">
                    @csrf
                    <label class="avatar-badge-edit" title="Change photo">
                        <i class="fa-solid fa-camera"></i>
                        <input type="file" name="avatar" accept="image/*" onchange="document.getElementById('avatarForm').submit()">
                    </label>
                </form>
            </div>

            {{-- Name block --}}
            <div style="flex:1; min-width: 220px;">
                <div class="prof-name">{{ $employee->full_name }}</div>
                <div class="prof-desig">
                    {{ $employee->designation?->name ?? 'Employee' }}
                    @if($employee->department) · {{ $employee->department->name }} @endif
                </div>
                <div class="prof-meta">
                    <span class="prof-meta-chip"><i class="fa-solid fa-id-badge"></i>{{ $employee->employee_id }}</span>
                    @if($employee->joining_date)
                        <span class="prof-meta-chip"><i class="fa-solid fa-calendar"></i>Joined {{ $employee->joining_date->format('M Y') }}</span>
                    @endif
                    <span class="prof-meta-chip"><i class="fa-solid fa-briefcase"></i>{{ ucfirst(str_replace('_',' ',$employee->employment_type)) }}</span>
                    @if($employee->employment_status === 'active')
                        <span class="prof-meta-chip" style="background:rgba(34,197,94,.15);border-color:rgba(34,197,94,.3);color:#86efac;">
                            <i class="fa-solid fa-circle-check" style="color:#86efac;"></i>Active
                        </span>
                    @endif
                </div>
            </div>

            {{-- Completion --}}
            <div class="completion-wrap">
                <div class="completion-circle">
                    <svg>
                        <defs>
                            <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#E87A45"/>
                                <stop offset="100%" stop-color="#F59E0B"/>
                            </linearGradient>
                        </defs>
                        <circle cx="43" cy="43" r="36" class="bg"/>
                        <circle cx="43" cy="43" r="36" class="fg"
                                stroke-dasharray="226.2"
                                stroke-dashoffset="{{ 226.2 - (226.2 * $completion['overall_pct'] / 100) }}"/>
                    </svg>
                    <div class="completion-num">{{ $completion['overall_pct'] }}<small>%</small></div>
                </div>
                <div>
                    <div class="completion-txt-lbl">Profile</div>
                    <div class="completion-txt-val">
                        @if($completion['overall_pct'] === 100)
                            Complete ✨
                        @elseif($completion['overall_pct'] >= 70)
                            Almost there
                        @elseif($completion['overall_pct'] >= 40)
                            Getting there
                        @else
                            Needs info
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════ TABS ═══════════════════════════ --}}
    <div class="tab-bar">
        <a href="{{ route('employee.profile.index', ['tab'=>'overview']) }}" class="{{ $tab==='overview' ? 'active' : '' }}">
            <i class="fa-solid fa-id-card"></i> Overview
        </a>
        <a href="{{ route('employee.profile.index', ['tab'=>'edit']) }}" class="{{ $tab==='edit' ? 'active' : '' }}">
            <i class="fa-solid fa-pen"></i> Edit Details
        </a>
        <a href="{{ route('employee.profile.index', ['tab'=>'employment']) }}" class="{{ $tab==='employment' ? 'active' : '' }}">
            <i class="fa-solid fa-briefcase"></i> Employment
        </a>
        <a href="{{ route('employee.profile.index', ['tab'=>'documents']) }}" class="{{ $tab==='documents' ? 'active' : '' }}">
            <i class="fa-solid fa-folder"></i> Documents
            @if($documents->count())
                <span class="tab-count">{{ $documents->count() }}</span>
            @endif
        </a>
        <a href="{{ route('employee.profile.index', ['tab'=>'password']) }}" class="{{ $tab==='password' ? 'active' : '' }}">
            <i class="fa-solid fa-lock"></i> Password
        </a>
    </div>

    {{-- ═══════════════════════════ CONTENT BY TAB ═══════════════════════════ --}}

    @if($tab === 'overview')
        @include('employee.profile._overview', compact('employee','completion'))
    @elseif($tab === 'edit')
        @include('employee.profile._edit', compact('employee'))
    @elseif($tab === 'employment')
        @include('employee.profile._employment', compact('employee'))
    @elseif($tab === 'documents')
        @include('employee.profile._documents', compact('documents'))
    @elseif($tab === 'password')
        @include('employee.profile._password')
    @endif

</div>
@endsection