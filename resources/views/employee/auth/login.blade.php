<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#C2531B">
    <title>Sign In — KUVVET Employee Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *,*::before,*::after { box-sizing:border-box; margin:0; padding:0; }
        :root {
            --accent: #C2531B;
            --accent-light: #E87A45;
            --accent-grad: linear-gradient(135deg,#C2531B 0%,#E87A45 50%,#F59E0B 100%);
            --bg: #F9F6F2;
            --card: #FFFFFF;
            --text: #2D1F14;
            --muted: #A89080;
            --border: #F0EAE2;
            --input: #F7F3EF;
            --red: #DC2626;
        }
        html,body { min-height:100dvh; }
        body {
            background: var(--bg);
            font-family:'Plus Jakarta Sans',sans-serif;
            color: var(--text);
            display:flex; align-items:center; justify-content:center;
            padding:20px;
            position:relative; overflow:hidden;
        }
        /* Background blobs */
        .blob {
            position:fixed; border-radius:50%; filter:blur(100px);
            pointer-events:none; z-index:0;
        }
        .blob-1 { width:520px; height:520px; background:rgba(194,83,27,.12); top:-200px; left:-160px; animation:float 14s ease-in-out infinite; }
        .blob-2 { width:420px; height:420px; background:rgba(245,158,11,.12); bottom:-180px; right:-140px; animation:float 18s ease-in-out infinite 5s; }
        .blob-3 { width:260px; height:260px; background:rgba(232,122,69,.10); top:30%; right:10%; animation:float 16s ease-in-out infinite 2s; }
        @keyframes float { 0%,100%{transform:translate(0,0)} 33%{transform:translate(30px,-20px)} 66%{transform:translate(-20px,30px)} }

        /* Grid overlay */
        body::before {
            content:''; position:fixed; inset:0; z-index:0; pointer-events:none;
            background-image:
                linear-gradient(rgba(194,83,27,.04) 1px,transparent 1px),
                linear-gradient(90deg, rgba(194,83,27,.04) 1px,transparent 1px);
            background-size: 44px 44px;
            mask-image: radial-gradient(circle at center,black 30%,transparent 75%);
        }

        .wrap { position:relative; z-index:10; width:100%; max-width: 920px; display:grid; grid-template-columns: 1fr 1fr; gap:0; }

        /* Left branding panel */
        .hero {
            padding:50px 48px;
            background: var(--accent-grad);
            color:#fff;
            border-radius: 24px 0 0 24px;
            display:flex; flex-direction:column; justify-content:space-between;
            min-height: 580px;
            position:relative; overflow:hidden;
        }
        .hero::after {
            content:''; position:absolute; inset:0;
            background: radial-gradient(circle at 100% 0%, rgba(255,255,255,.2), transparent 50%),
                        radial-gradient(circle at 0% 100%, rgba(255,255,255,.15), transparent 50%);
            pointer-events:none;
        }
        .hero-logo {
            width:52px; height:52px; border-radius:14px;
            background: rgba(255,255,255,.18);
            border: 1.5px solid rgba(255,255,255,.35);
            backdrop-filter: blur(10px);
            display:flex;align-items:center;justify-content:center;
            font-weight:700; font-size:20px; font-family:'Space Grotesk',sans-serif;
            letter-spacing:1px;
            position:relative; z-index:1;
        }
        .hero-big {
            font-family:'Space Grotesk',sans-serif;
            font-weight:700; font-size:34px;
            line-height:1.15; letter-spacing:-0.5px;
            margin-top:auto;
            position:relative; z-index:1;
        }
        .hero-big span { opacity:.75; font-weight:500; }
        .hero-sub {
            margin-top:12px;
            font-size:13.5px; opacity:.85; line-height:1.6;
            max-width: 320px;
            position:relative; z-index:1;
        }
        .hero-feats { display:flex; flex-direction:column; gap:10px; margin-top:32px; position:relative; z-index:1; }
        .hero-feat { display:flex; align-items:center; gap:10px; font-size:12.5px; opacity:.9; }
        .hero-feat i { width:28px; height:28px; border-radius:8px; background:rgba(255,255,255,.15); display:flex;align-items:center;justify-content:center; font-size:11px; }

        /* Right form panel */
        .card {
            padding:50px 44px;
            background: var(--card);
            border-radius: 0 24px 24px 0;
            border: 1px solid var(--border);
            border-left: none;
            box-shadow: 0 20px 60px rgba(45,31,20,.08);
            display:flex; flex-direction:column; justify-content:center;
        }
        .card h1 {
            font-family:'Space Grotesk',sans-serif;
            font-size: 26px; font-weight:700; color: var(--text);
            letter-spacing:-0.3px;
        }
        .card p.sub { font-size:13px; color: var(--muted); margin-top: 6px; margin-bottom: 32px; }

        label {
            display:block; font-size:11px; font-weight:600; color: var(--text);
            letter-spacing:.6px; text-transform:uppercase; margin-bottom:8px;
        }
        .input-wrap { position:relative; margin-bottom:18px; }
        .input-wrap .ico {
            position:absolute; left:16px; top:50%; transform:translateY(-50%);
            color: var(--muted); font-size:14px; pointer-events:none;
        }
        input {
            width:100%; height:48px;
            background: var(--input);
            border: 1.5px solid transparent;
            padding: 0 16px 0 44px;
            border-radius: 12px;
            font: inherit; font-size: 14px; color: var(--text);
            transition: all .2s;
        }
        input:focus { outline:none; border-color: var(--accent); background:#fff; box-shadow: 0 0 0 4px rgba(194,83,27,.08); }

        .btn-login {
            width:100%; height:50px; border:none; cursor:pointer;
            background: var(--accent-grad); color:#fff;
            border-radius: 12px;
            font: inherit; font-weight:600; font-size:14px;
            display:flex; align-items:center; justify-content:center; gap:10px;
            box-shadow: 0 8px 20px rgba(194,83,27,.25);
            transition: all .2s;
            margin-top: 12px;
        }
        .btn-login:hover { transform:translateY(-1px); box-shadow: 0 10px 24px rgba(194,83,27,.35); }
        .btn-login:active { transform:translateY(0); }

        .row-between { display:flex; align-items:center; justify-content:space-between; margin-bottom:6px; }
        .remember { display:flex; align-items:center; gap:8px; font-size:12.5px; color: var(--muted); cursor:pointer; }
        .remember input[type=checkbox] { width:16px; height:16px; accent-color: var(--accent); cursor:pointer; }

        .err {
            margin-top:6px; color: var(--red); font-size:12px;
            display:flex; align-items:center; gap:6px;
        }
        .flash-err {
            padding: 12px 14px;
            background: #FEF2F2; border:1px solid #FECACA; color: var(--red);
            border-radius: 10px; font-size:12.5px; margin-bottom: 18px;
            display:flex; align-items:center; gap:8px;
        }
        .flash-info {
            padding: 12px 14px;
            background: #EFF6FF; border:1px solid #BFDBFE; color: #2563EB;
            border-radius: 10px; font-size:12.5px; margin-bottom: 18px;
            display:flex; align-items:center; gap:8px;
        }

        .foot {
            margin-top: 28px; text-align:center;
            font-size: 12px; color: var(--muted);
        }
        .foot a { color: var(--accent); text-decoration:none; font-weight:600; }

        @media (max-width: 780px) {
            .wrap { grid-template-columns: 1fr; max-width: 440px; }
            .hero { display:none; }
            .card { border-radius: 20px; border-left: 1px solid var(--border); padding: 38px 28px; }
        }
    </style>
</head>
<body>

<div class="blob blob-1"></div>
<div class="blob blob-2"></div>
<div class="blob blob-3"></div>

<div class="wrap">

    {{-- Hero / Branding panel --}}
    <div class="hero">
        <div class="hero-logo">K</div>

        <div>
            <div class="hero-big">Welcome back.<br><span>Let's get to work.</span></div>
            <div class="hero-sub">
                Sign in to manage your attendance, leaves, payslips, and everything KUVVET.
            </div>

            <div class="hero-feats">
                <div class="hero-feat"><i class="fa-solid fa-location-crosshairs"></i> GPS-verified check-ins</div>
                <div class="hero-feat"><i class="fa-solid fa-qrcode"></i> Scan-and-go QR attendance</div>
                <div class="hero-feat"><i class="fa-solid fa-mobile-screen"></i> Built for desktop &amp; mobile</div>
            </div>
        </div>
    </div>

    {{-- Form panel --}}
    <div class="card">
        <h1>Sign in</h1>
        <p class="sub">Access your KUVVET employee portal</p>

        @if(session('error'))
            <div class="flash-err"><i class="fa-solid fa-circle-xmark"></i>{{ session('error') }}</div>
        @endif
        @if(session('info'))
            <div class="flash-info"><i class="fa-solid fa-circle-info"></i>{{ session('info') }}</div>
        @endif

        <form method="POST" action="{{ route('employee.login.post') }}">
            @csrf

            <label for="login">Employee ID, Username or Email</label>
            <div class="input-wrap">
                <i class="fa-solid fa-user ico"></i>
                <input type="text" id="login" name="login" value="{{ old('login') }}"
                       placeholder="KVT-001 or you@kuvvet.com" autofocus required>
            </div>
            @error('login') <div class="err" style="margin-top:-12px;margin-bottom:12px;"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div> @enderror

            <label for="password">Password</label>
            <div class="input-wrap">
                <i class="fa-solid fa-lock ico"></i>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>

            <div class="row-between">
                <label class="remember">
                    <input type="checkbox" name="remember"> Remember me
                </label>
                {{-- <a href="#" style="font-size:12.5px;color:var(--accent);text-decoration:none;font-weight:600;">Forgot password?</a> --}}
            </div>

            <button type="submit" class="btn-login">
                Sign in <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>

        <div class="foot">
            Not an employee? <a href="{{ route('admin.login') }}">Admin login</a>
        </div>
    </div>

</div>

</body>
</html>