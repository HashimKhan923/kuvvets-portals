<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#C2531B">
    <title>@yield('title', 'Home') — KUVVET</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --accent:         #C2531B;
            --accent-light:   #E87A45;
            --accent-dark:    #9A3F11;
            --accent-bg:      #FEF2EC;
            --accent-border:  #F5D5C0;
            --accent-grad:    linear-gradient(135deg, #C2531B 0%, #E87A45 100%);

            --bg-page:        #F9F6F2;
            --bg-card:        #FFFFFF;
            --bg-input:       #F7F3EF;
            --bg-muted:       #F5F0EB;
            --bg-hover:       #FEF2EC;

            --border:         #F0EAE2;
            --border-strong:  #E8DDD5;

            --text-primary:   #2D1F14;
            --text-secondary: #6B5347;
            --text-muted:     #A89080;

            --green:  #16A34A;  --green-bg:  #F0FDF4;  --green-border: #BBF7D0;
            --yellow: #F59E0B;  --yellow-bg: #FFFBEB;  --yellow-border:#FDE68A;
            --red:    #DC2626;  --red-bg:    #FEF2F2;  --red-border:   #FECACA;
            --blue:   #2563EB;  --blue-bg:   #EFF6FF;  --blue-border:  #BFDBFE;
            --purple: #7C3AED;  --purple-bg: #F5F3FF;  --purple-border:#DDD6FE;

            --shadow-sm: 0 1px 2px rgba(45,31,20,.04);
            --shadow:    0 2px 8px rgba(45,31,20,.06);
            --shadow-lg: 0 8px 32px rgba(45,31,20,.10);
        }

        html, body { height: 100%; }
        body {
            background: var(--bg-page);
            color: var(--text-primary);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
            line-height: 1.5;
            -webkit-tap-highlight-color: transparent;
        }

        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-thumb { background: var(--border-strong); border-radius: 4px; }

        /* ═══════════════════════════════════════════════════
           LAYOUT
        ═══════════════════════════════════════════════════ */
        .app {
            display: flex;
            min-height: 100dvh;
        }

        /* Desktop sidebar */
        .side {
            width: 240px;
            flex-shrink: 0;
            background: var(--bg-card);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100dvh;
            overflow-y: auto;
        }

        .side-brand {
            display: flex; align-items: center; gap: 10px;
            padding: 18px 18px;
            border-bottom: 1px solid var(--border);
        }
        .side-brand-logo {
            width: 38px; height: 38px; border-radius: 10px;
            background: var(--accent-grad);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 700; font-size: 15px;
            box-shadow: 0 4px 12px rgba(194,83,27,.25);
            font-family: 'Space Grotesk', sans-serif;
            letter-spacing: .5px;
        }
        .side-brand-name { font-size: 14px; font-weight: 700; letter-spacing: .8px; }
        .side-brand-sub  { font-size: 10px; color: var(--text-muted); letter-spacing: .4px; }

        .side-nav { padding: 12px 10px; flex: 1; }
        .side-group-label {
            font-size: 10px; font-weight: 600; color: var(--text-muted);
            letter-spacing: 1.2px; text-transform: uppercase;
            padding: 12px 10px 6px;
        }
        .side-item {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 12px; border-radius: 10px;
            color: var(--text-secondary); font-size: 13.5px; font-weight: 500;
            text-decoration: none; margin-bottom: 2px;
            transition: all .15s;
        }
        .side-item i.fa-fw { width: 18px; font-size: 14px; text-align: center; }
        .side-item:hover { background: var(--bg-hover); color: var(--accent); }
        .side-item.active {
            background: var(--accent-bg); color: var(--accent); font-weight: 600;
        }
        .side-item.active i { color: var(--accent); }

        .side-foot { padding: 12px; border-top: 1px solid var(--border); }
        .side-user {
            display: flex; align-items: center; gap: 10px;
            padding: 10px; background: var(--bg-muted);
            border-radius: 10px; cursor: pointer;
            transition: all .15s;
        }
        .side-user:hover { background: var(--bg-hover); }
        .side-user-img {
            width: 36px; height: 36px; border-radius: 50%;
            object-fit: cover; border: 2px solid var(--bg-card);
        }
        .side-user-name { font-size: 12.5px; font-weight: 600; color: var(--text-primary); }
        .side-user-role { font-size: 10.5px; color: var(--text-muted); }

        /* Main */
        .main {
            flex: 1; display: flex; flex-direction: column;
            min-width: 0;
        }

        /* Mobile top bar */
        .top {
            height: 58px;
            background: var(--bg-card);
            border-bottom: 1px solid var(--border);
            padding: 0 18px;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 40;
        }
        .top-title { font-size: 15px; font-weight: 700; font-family: 'Space Grotesk', sans-serif; }
        .top-sub   { font-size: 10.5px; color: var(--text-muted); letter-spacing: .3px; }
        .top-btn {
            width: 38px; height: 38px; border-radius: 10px;
            background: var(--bg-muted); border: none; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            color: var(--text-secondary); font-size: 14px;
            transition: all .15s;
            position: relative;
        }
        .top-btn:hover { background: var(--bg-hover); color: var(--accent); }

        .notif-dot {
            position: absolute; top: 8px; right: 8px;
            width: 7px; height: 7px; border-radius: 50%;
            background: var(--red);
            box-shadow: 0 0 0 2px var(--bg-card);
        }

        /* Accent shimmer line */
        .accent-line {
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--accent), var(--accent-light), var(--accent), transparent);
            background-size: 200% 100%;
            animation: shimmer 3.5s linear infinite;
        }
        @keyframes shimmer { 0%{background-position:-200% 0} 100%{background-position:200% 0} }

        /* Page */
        .page {
            flex: 1;
            padding: 22px 26px 32px;
            animation: pageIn .35s cubic-bezier(.22,.61,.36,1);
        }
        @keyframes pageIn { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:translateY(0)} }

        /* ═══════════════════════════════════════════════════
           MOBILE BOTTOM NAV
        ═══════════════════════════════════════════════════ */
        .bot-nav { display: none; }

        @media (max-width: 860px) {
            .side { display: none; }
            .page { padding: 18px 16px 96px; }
            .top { padding: 0 14px; }

            .bot-nav {
                display: grid;
                grid-template-columns: repeat(5, 1fr);
                position: fixed; bottom: 0; left: 0; right: 0;
                background: var(--bg-card);
                border-top: 1px solid var(--border);
                padding: 8px 8px calc(8px + env(safe-area-inset-bottom));
                z-index: 50;
                box-shadow: 0 -4px 16px rgba(45,31,20,.04);
            }
            .bot-nav a {
                display: flex; flex-direction: column; align-items: center; gap: 3px;
                padding: 8px 4px; border-radius: 10px; text-decoration: none;
                color: var(--text-muted); font-size: 10px; font-weight: 500;
                transition: all .15s;
            }
            .bot-nav a i { font-size: 17px; }
            .bot-nav a.active { color: var(--accent); }
            .bot-nav a.active .bot-icon-wrap {
                background: var(--accent-bg);
            }
            .bot-icon-wrap {
                width: 42px; height: 28px; display: flex; align-items: center; justify-content: center;
                border-radius: 10px; transition: all .15s;
            }
            .bot-nav a.check {
                position: relative;
            }
            .bot-nav a.check .bot-icon-wrap {
                background: var(--accent-grad);
                color: #fff;
                width: 48px; height: 36px;
                box-shadow: 0 6px 16px rgba(194,83,27,.35);
                margin-top: -12px;
            }
        }

        /* ═══════════════════════════════════════════════════
           SHARED UI
        ═══════════════════════════════════════════════════ */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 20px;
            box-shadow: var(--shadow-sm);
        }
        .card-hd {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 14px;
        }
        .card-title {
            font-size: 13px; font-weight: 700;
            font-family: 'Space Grotesk', sans-serif;
            letter-spacing: .3px;
        }
        .card-sub { font-size: 11px; color: var(--text-muted); }

        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 10px 18px; border-radius: 10px; border: none; cursor: pointer;
            font-size: 13px; font-weight: 600; font-family: inherit;
            text-decoration: none; transition: all .2s;
        }
        .btn-primary {
            background: var(--accent-grad); color: #fff;
            box-shadow: 0 4px 12px rgba(194,83,27,.25);
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(194,83,27,.35); }
        .btn-secondary { background: var(--bg-muted); color: var(--text-primary); }
        .btn-secondary:hover { background: var(--bg-hover); color: var(--accent); }
        .btn-ghost { background: transparent; color: var(--text-secondary); }
        .btn-ghost:hover { background: var(--bg-hover); color: var(--accent); }
        .btn-danger { background: var(--red-bg); color: var(--red); border: 1px solid var(--red-border); }
        .btn-danger:hover { background: var(--red); color: #fff; }
        .btn-block { width: 100%; }
        .btn-lg { padding: 14px 24px; font-size: 14px; border-radius: 12px; }

        .badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 10px; border-radius: 999px;
            font-size: 11px; font-weight: 600; letter-spacing: .2px;
        }
        .badge-green  { background: var(--green-bg); color: var(--green); border: 1px solid var(--green-border); }
        .badge-yellow { background: var(--yellow-bg); color: var(--yellow); border: 1px solid var(--yellow-border); }
        .badge-red    { background: var(--red-bg); color: var(--red); border: 1px solid var(--red-border); }
        .badge-blue   { background: var(--blue-bg); color: var(--blue); border: 1px solid var(--blue-border); }
        .badge-accent { background: var(--accent-bg); color: var(--accent); border: 1px solid var(--accent-border); }
        .badge-gray   { background: var(--bg-muted); color: var(--text-secondary); border: 1px solid var(--border); }

        .flash {
            padding: 12px 16px; border-radius: 12px; margin-bottom: 16px;
            font-size: 13px; font-weight: 500;
            display: flex; align-items: center; gap: 10px;
            animation: slideDown .3s ease;
        }
        @keyframes slideDown { from{opacity:0;transform:translateY(-8px)} to{opacity:1;transform:translateY(0)} }
        .flash-success { background: var(--green-bg); color: var(--green); border: 1px solid var(--green-border); }
        .flash-error   { background: var(--red-bg); color: var(--red); border: 1px solid var(--red-border); }
        .flash-info    { background: var(--blue-bg); color: var(--blue); border: 1px solid var(--blue-border); }
        .flash-warning { background: var(--yellow-bg); color: var(--yellow); border: 1px solid var(--yellow-border); }

        /* Dropdown */
        .dropdown {
            background: var(--bg-card); border: 1px solid var(--border);
            border-radius: 12px; padding: 6px;
            box-shadow: var(--shadow-lg);
            min-width: 220px;
        }
        .dropdown-item {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px; border-radius: 8px;
            color: var(--text-primary); font-size: 13px;
            text-decoration: none; cursor: pointer;
            border: none; background: none; width: 100%; text-align: left;
            font-family: inherit;
        }
        .dropdown-item:hover { background: var(--bg-hover); color: var(--accent); }
        .dropdown-item.danger { color: var(--red); }
        .dropdown-item.danger:hover { background: var(--red-bg); color: var(--red); }
        .dropdown-divider { margin: 4px 0; border: none; border-top: 1px solid var(--border); }
    </style>
    @stack('styles')
</head>
<body>

<div class="app">

    {{-- ═══════════ Desktop Sidebar ═══════════ --}}
    <aside class="side">
        <a href="{{ route('employee.dashboard') }}" class="side-brand" style="text-decoration:none;color:inherit;">
            <div class="side-brand-logo">K</div>
            <div>
                <div class="side-brand-name">KUVVET</div>
                <div class="side-brand-sub">Employee Portal</div>
            </div>
        </a>

        <nav class="side-nav">
            <div class="side-group-label">Main</div>
            <a href="{{ route('employee.dashboard') }}" class="side-item {{ request()->routeIs('employee.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-house fa-fw"></i> Home
            </a>
            <a href="{{ route('employee.attendance.index') }}" class="side-item {{ request()->routeIs('employee.attendance.*') ? 'active' : '' }}">
                <i class="fa-solid fa-clock fa-fw"></i> Attendance
            </a>
            <a href="{{ route('employee.leaves.index') }}" class="side-item {{ request()->routeIs('employee.leaves.*') ? 'active' : '' }}">
                <i class="fa-solid fa-umbrella-beach fa-fw"></i> Leaves
            </a>
            <a href="{{ route('employee.payslips.index') }}" class="side-item {{ request()->routeIs('employee.payslips.*') ? 'active' : '' }}">
                <i class="fa-solid fa-receipt fa-fw"></i> Payslips
            </a>

            <div class="side-group-label">My Info</div>
            <a href="{{ route('employee.profile.index') }}" class="side-item {{ request()->routeIs('employee.profile.*') ? 'active' : '' }}">
                <i class="fa-solid fa-user fa-fw"></i> Profile
            </a>
            <a href="{{ route('employee.documents.index') }}" class="side-item {{ request()->routeIs('employee.documents.*') ? 'active' : '' }}">
                <i class="fa-solid fa-folder fa-fw"></i> Documents
            </a>
        </nav>

        <div class="side-foot">
            <div class="side-user" x-data="{open:false}" @click="open=!open" style="position:relative;">
                <img src="{{ auth()->user()->avatar_url }}" class="side-user-img" alt="">
                <div style="flex:1;min-width:0;">
                    <div class="side-user-name" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ auth()->user()->employee?->full_name ?? auth()->user()->name }}
                    </div>
                    <div class="side-user-role">{{ auth()->user()->employee?->employee_id ?? 'Employee' }}</div>
                </div>
                <i class="fa-solid fa-ellipsis-vertical" style="color:var(--text-muted);"></i>

                <div x-show="open" @click.outside="open=false" x-transition
                     class="dropdown" style="position:absolute;bottom:calc(100% + 8px);left:0;right:0;">
                    <a href="{{ route('employee.profile.index') }}" class="dropdown-item">
                        <i class="fa-solid fa-user-circle fa-fw"></i> My Profile
                    </a>
                    <hr class="dropdown-divider">
                    <form method="POST" action="{{ route('employee.logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item danger">
                            <i class="fa-solid fa-right-from-bracket fa-fw"></i> Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    {{-- ═══════════ Main ═══════════ --}}
    <div class="main">

        {{-- Top bar (mobile + desktop) --}}
        <header class="top">
            <div>
                <div class="top-title">@yield('page-title', 'Home')</div>
                <div class="top-sub">@yield('page-sub', 'KUVVET Employee Portal')</div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;" x-data="{notif:false}">
                <button class="top-btn" @click="notif=!notif">
                    <i class="fa-solid fa-bell"></i>
                    <span class="notif-dot"></span>
                </button>
                <div x-show="notif" @click.outside="notif=false" x-transition
                     class="dropdown" style="position:absolute;right:14px;top:54px;width:300px;padding:0;overflow:hidden;">
                    <div style="padding:14px 16px;border-bottom:1px solid var(--border);font-weight:700;font-size:13px;">
                        Notifications
                    </div>
                    <div style="padding:32px 16px;text-align:center;color:var(--text-muted);font-size:12px;">
                        <i class="fa-solid fa-bell-slash" style="font-size:24px;margin-bottom:8px;display:block;"></i>
                        No new notifications
                    </div>
                </div>
            </div>
        </header>

        <div class="accent-line"></div>

        {{-- Page --}}
        <main class="page">
            @if(session('success')) <div class="flash flash-success"><i class="fa-solid fa-circle-check"></i>{{ session('success') }}</div> @endif
            @if(session('error'))   <div class="flash flash-error"><i class="fa-solid fa-circle-xmark"></i>{{ session('error') }}</div> @endif
            @if(session('info'))    <div class="flash flash-info"><i class="fa-solid fa-circle-info"></i>{{ session('info') }}</div> @endif
            @if(session('warning')) <div class="flash flash-warning"><i class="fa-solid fa-triangle-exclamation"></i>{{ session('warning') }}</div> @endif

            @yield('content')
        </main>
    </div>
</div>

{{-- ═══════════ Mobile Bottom Nav ═══════════ --}}
<nav class="bot-nav">
    <a href="{{ route('employee.dashboard') }}" class="{{ request()->routeIs('employee.dashboard') ? 'active' : '' }}">
        <div class="bot-icon-wrap"><i class="fa-solid fa-house"></i></div>
        <span>Home</span>
    </a>
    <a href="{{ route('employee.attendance.index') }}" class="{{ request()->routeIs('employee.attendance.*') ? 'active' : '' }}">
        <div class="bot-icon-wrap"><i class="fa-solid fa-clock"></i></div>
        <span>Attendance</span>
    </a>
    <a href="{{ route('employee.dashboard') }}#check-in" class="check">
        <div class="bot-icon-wrap"><i class="fa-solid fa-fingerprint"></i></div>
        <span style="margin-top:2px;font-weight:600;">Check</span>
    </a>
    <a href="{{ route('employee.leaves.index') }}" class="{{ request()->routeIs('employee.leaves.*') ? 'active' : '' }}">
        <div class="bot-icon-wrap"><i class="fa-solid fa-umbrella-beach"></i></div>
        <span>Leaves</span>
    </a>
    <a href="{{ route('employee.profile.index') }}" class="{{ request()->routeIs('employee.profile.*') ? 'active' : '' }}">
        <div class="bot-icon-wrap"><i class="fa-solid fa-user"></i></div>
        <span>Profile</span>
    </a>
</nav>

<script src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.13.3/cdn.min.js" defer></script>
@stack('scripts')
</body>
</html>