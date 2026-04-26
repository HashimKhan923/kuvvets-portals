<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — KUVVET Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh; background: #040810;
            font-family: 'Sora', sans-serif;
            display: flex; align-items: center; justify-content: center;
            overflow: hidden;
        }

        /* Animated grid background */
        .bg-grid {
            position: fixed; inset: 0; z-index: 0;
            background-image:
                linear-gradient(rgba(186,117,23,.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(186,117,23,.04) 1px, transparent 1px);
            background-size: 40px 40px;
        }

        /* Glowing orbs */
        .orb {
            position: fixed; border-radius: 50%; filter: blur(80px); z-index: 0;
            animation: drift 8s ease-in-out infinite;
        }
        .orb-1 { width: 400px; height: 400px; background: rgba(186,117,23,.07); top: -100px; left: -100px; }
        .orb-2 { width: 300px; height: 300px; background: rgba(239,159,39,.05); bottom: -80px; right: -80px; animation-delay: 4s; }
        @keyframes drift { 0%,100%{transform:translate(0,0)} 50%{transform:translate(20px,20px)} }

        /* Card */
        .login-card {
            position: relative; z-index: 10;
            background: #080c10;
            border: 1px solid #1e2a35;
            border-radius: 16px;
            width: 100%;
            max-width: 420px;
            padding: 40px 36px;
            box-shadow: 0 0 60px rgba(0,0,0,.5);
        }

        /* Gold shimmer border top */
        .login-card::before {
            content: '';
            position: absolute; top: 0; left: 10%; right: 10%;
            height: 1px;
            background: linear-gradient(90deg, transparent, #BA7517, #EF9F27, #BA7517, transparent);
            animation: shimmer 3s linear infinite;
            background-size: 200% 100%;
        }
        @keyframes shimmer { 0%{background-position:-200% 0} 100%{background-position:200% 0} }

        .brand { display: flex; align-items: center; gap: 12px; margin-bottom: 28px; }
        .brand-icon {
            width: 42px; height: 42px; background: #1a1200;
            border: 1.5px solid #BA7517; border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
        }
        .brand-name { font-family: 'Space Grotesk', sans-serif; font-size: 22px; font-weight: 700; color: #EF9F27; letter-spacing: 2px; }
        .brand-sub  { font-size: 10px; color: #3a3020; letter-spacing: .8px; }

        h2 { font-family: 'Space Grotesk', sans-serif; font-size: 18px; font-weight: 600; color: #d4c5a0; margin-bottom: 4px; }
        .subtitle { font-size: 12px; color: #5a5040; margin-bottom: 28px; }

        .form-group { margin-bottom: 18px; }
        label { display: block; font-size: 11px; font-weight: 500; color: #7a6a50; letter-spacing: .6px; margin-bottom: 6px; text-transform: uppercase; }

        .input-wrap { position: relative; }
        .input-wrap i { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: #3a3020; font-size: 13px; }
        input[type="email"],
        input[type="text"],
        input[type="password"] {
            width: 100%; background: #0d1117;
            border: 1px solid #1e2a35; border-radius: 8px;
            padding: 11px 14px 11px 38px;
            color: #d4c5a0; font-size: 14px; font-family: 'Sora', sans-serif;
            outline: none; transition: border-color .2s, box-shadow .2s;
        }
        input:focus { border-color: #BA7517; box-shadow: 0 0 0 3px rgba(186,117,23,.08); }
        input::placeholder { color: #2a2a1a; }

        .toggle-pass { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #3a3020; font-size: 13px; }
        .toggle-pass:hover { color: #BA7517; }

        .remember-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 22px; }
        .checkbox-wrap { display: flex; align-items: center; gap: 7px; font-size: 12px; color: #5a5040; cursor: pointer; }
        input[type="checkbox"] { width: 14px; height: 14px; accent-color: #BA7517; cursor: pointer; }
        .forgot { font-size: 12px; color: #BA7517; text-decoration: none; }
        .forgot:hover { color: #EF9F27; }

        .btn-login {
            width: 100%; padding: 12px;
            background: linear-gradient(135deg, #BA7517, #EF9F27);
            color: #0d1117; border: none; border-radius: 8px;
            font-size: 14px; font-weight: 600; font-family: 'Space Grotesk', sans-serif;
            letter-spacing: .5px; cursor: pointer;
            transition: opacity .2s, transform .15s;
        }
        .btn-login:hover { opacity: .92; }
        .btn-login:active { transform: scale(.98); }
        .btn-login:disabled { opacity: .5; cursor: not-allowed; }

        .error-msg { background: #1a0505; border: 1px solid #3a1010; border-radius: 7px; padding: 9px 14px; font-size: 12px; color: #E24B4A; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }

        .divider { border: none; border-top: 1px solid #1e2a35; margin: 24px 0 16px; }
        .footer-note { text-align: center; font-size: 11px; color: #2a2a1a; }
        .footer-note a { color: #BA7517; text-decoration: none; }

        @keyframes fadeUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
        .login-card { animation: fadeUp .5s ease; }
    </style>
</head>
<body>
    <div class="bg-grid"></div>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <div class="login-card">
        <!-- Brand -->
        <div class="brand">
            <div class="brand-icon">⬡</div>
            <div>
                <div class="brand-name">KUVVET</div>
                <div class="brand-sub">PRIVATE LIMITED · HR PORTAL</div>
            </div>
        </div>

        <h2>Welcome back</h2>
        <p class="subtitle">Sign in to access your workspace</p>

        <!-- Error Messages -->
        @if($errors->any())
            <div class="error-msg">
                <i class="fa-solid fa-triangle-exclamation"></i>
                {{ $errors->first() }}
            </div>
        @endif

        @if(session('info'))
            <div style="background:#001015;border:1px solid #0a2a35;border-radius:7px;padding:9px 14px;font-size:12px;color:#378ADD;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
                <i class="fa-solid fa-circle-info"></i> {{ session('info') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.post') }}" id="loginForm">
            @csrf

            <div class="form-group">
                <label>Email or Username</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="text" name="email"
                           value="{{ old('email') }}"
                           placeholder="you@kuvvet.com or username"
                           autocomplete="username"
                           required autofocus>
                </div>
            </div>

            <div class="form-group">
                <label>Password</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" id="password"
                           placeholder="Enter your password"
                           autocomplete="current-password"
                           required>
                    <button type="button" class="toggle-pass" onclick="togglePassword()">
                        <i class="fa-solid fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <div class="remember-row">
                <label class="checkbox-wrap">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    Keep me signed in
                </label>
                <a href="#" class="forgot">Forgot password?</a>
            </div>

            <button type="submit" class="btn-login" id="loginBtn">
                <i class="fa-solid fa-arrow-right-to-bracket"></i> Sign In
            </button>
        </form>

        <hr class="divider">
        <p class="footer-note">
            Authorized personnel only · <a href="#">Contact IT Support</a>
        </p>
        <p class="footer-note" style="margin-top:6px;">
            © {{ date('Y') }} KUVVET Private Limited · Karachi, Pakistan
        </p>
    </div>

    <script>
        function togglePassword() {
            const p = document.getElementById('password');
            const i = document.getElementById('eyeIcon');
            if (p.type === 'password') {
                p.type = 'text';
                i.className = 'fa-solid fa-eye-slash';
            } else {
                p.type = 'password';
                i.className = 'fa-solid fa-eye';
            }
        }
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Signing in...';
        });
    </script>
</body>
</html>