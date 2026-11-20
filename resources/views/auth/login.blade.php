<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Login') | MYS HRMS</title>
    <meta name="description" content="Secure Login — Ministry of Youth and Sports HRMS, Republic of Liberia">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=DM+Sans:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --navy: #000a60; --accent: #d2143a; --navy-light: #001280;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #000030 0%, #000a60 50%, #001280 100%);
            display: flex; align-items: center; justify-content: center;
            position: relative; overflow: hidden;
        }
        /* Animated background circles */
        body::before, body::after {
            content: ''; position: absolute; border-radius: 50%;
            background: rgba(201,168,76,.06);
        }
        body::before { width: 600px; height: 600px; top: -200px; right: -200px; }
        body::after  { width: 400px; height: 400px; bottom: -150px; left: -150px; }

        .auth-card {
            background: rgba(255,255,255,.97);
            border-radius: 20px;
            width: 100%; max-width: 440px;
            padding: 40px 36px;
            box-shadow: 0 25px 60px rgba(0,0,0,.35), 0 0 0 1px rgba(255,255,255,.1);
            position: relative; z-index: 2;
            animation: slideUp .4s ease forwards;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .auth-logo {
            width: 68px; height: 68px;
            background: linear-gradient(135deg, var(--accent), #a8892e);
            border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            font-family: 'DM Sans', sans-serif;
            font-size: 26px; font-weight: 700;
            color: var(--navy);
            margin: 0 auto 20px;
            box-shadow: 0 8px 24px rgba(201,168,76,.4);
        }

        .auth-title {
            font-family: 'DM Sans', sans-serif;
            font-size: 22px; font-weight: 700;
            color: var(--navy);
            text-align: center; margin-bottom: 4px;
        }
        .auth-subtitle {
            font-size: 12px; color: #374151; text-align: center;
            margin-bottom: 28px; letter-spacing: .3px;
            font-weight: 500;
        }

        .form-label { font-size: 12.5px; font-weight: 600; color: #374151; }
        .form-control {
            border-radius: 10px; border: 1.5px solid #e5e7eb;
            font-size: 14px; padding: 11px 14px;
            transition: border-color .2s, box-shadow .2s;
        }
        .form-control:focus {
            border-color: var(--navy); box-shadow: 0 0 0 3px rgba(15,31,61,.1);
            outline: none;
        }
        .input-group .form-control { border-radius: 10px 0 0 10px !important; }
        .input-group .btn { border-radius: 0 10px 10px 0 !important; border: 1.5px solid #e5e7eb; border-left: none; background: #f9fafb; color: #6b7280; }
        .input-group .btn:hover { background: #f3f4f6; }

        .btn-login {
            background: linear-gradient(135deg, var(--navy), var(--navy-light));
            color: #fff; border: none; border-radius: 10px;
            font-weight: 600; font-size: 14px; padding: 12px;
            width: 100%; transition: all .2s;
            box-shadow: 0 4px 14px rgba(15,31,61,.35);
        }
        .btn-login:hover { box-shadow: 0 6px 20px rgba(15,31,61,.45); transform: translateY(-1px); color: #fff; }

        .remember-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
        .form-check-input:checked { background-color: var(--navy); border-color: var(--navy); }

        .divider { text-align: center; color: #9ca3af; font-size: 11px; margin: 20px 0; position: relative; }
        .divider::before, .divider::after {
            content: ''; position: absolute; top: 50%;
            width: 38%; height: 1px; background: #e5e7eb;
        }
        .divider::before { left: 0; } .divider::after { right: 0; }

        .auth-footer {
            text-align: center; margin-top: 24px;
            font-size: 11.5px; color: #9ca3af;
        }
        .auth-footer a { color: var(--accent); text-decoration: none; font-weight: 600; }

        .flag-stripe {
            display: flex; gap: 4px; justify-content: center;
            margin-bottom: 20px;
        }
        .flag-stripe span {
            height: 4px; border-radius: 2px; flex: 1;
        }

        .invalid-feedback { font-size: 11.5px; }
        .is-invalid { border-color: #ef4444 !important; }
    </style>
</head>
<body>
    <div class="auth-card">
        <!-- Liberian flag colors -->
        <div class="flag-stripe">
            <span style="background:#bf0a30;"></span>
            <span style="background:#fff;border:1px solid #e5e7eb;"></span>
            <span style="background:#bf0a30;"></span>
            <span style="background:#fff;border:1px solid #e5e7eb;"></span>
            <span style="background:#003897;"></span>
        </div>

        @if(file_exists(public_path('images/logo.png')))
            <div class="text-center mb-3">
                <img src="{{ asset('images/logo.png') }}" alt="MYS Logo" style="width: 80px; height: 80px; object-fit: contain;">
            </div>
        @else
            <div class="auth-logo">MYS</div>
        @endif
        <h1 class="auth-title">Welcome Back</h1>
        <p class="auth-subtitle">
            Ministry of Youth and Sports<br>
            Human Resource Management System — Republic of Liberia
        </p>

        @if($errors->any())
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" role="alert" style="border-radius:10px;font-size:13px;">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>{{ $errors->first() }}</span>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" role="alert" style="border-radius:10px;font-size:13px;">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}" id="loginForm">
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" id="email" name="email"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email') }}"
                    placeholder="you@mys.gov.lr"
                    autocomplete="email" autofocus required>
                @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" id="password" name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        placeholder="Enter your password"
                        autocomplete="current-password" required>
                    <button type="button" class="btn" id="togglePass" tabindex="-1">
                        <i class="bi bi-eye" id="eyeIcon"></i>
                    </button>
                </div>
                @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="remember-row">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                    <label class="form-check-label" for="remember" style="font-size:12.5px;">Remember me</label>
                </div>
                <a href="#" style="font-size:12.5px;color:var(--accent);text-decoration:none;font-weight:600;">
                    <a href="{{ route('password.request') }}">Forgot Password?</a>
                </a>
            </div>

            <button type="submit" class="btn btn-login" id="loginBtn">
                <span id="loginText"><i class="bi bi-box-arrow-in-right me-2"></i>Sign In to HRMS</span>
                <span id="loginLoading" style="display:none;">
                    <span class="spinner-border spinner-border-sm me-2"></span>Authenticating...
                </span>
            </button>
        </form>

        <div class="auth-footer">
            <div>© {{ date('Y') }} Ministry of Youth and Sports, Republic of Liberia</div>
            <div class="mt-1">HRMS v1.0.0 — Phase 1</div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePass').addEventListener('click', function() {
            const pass = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            if (pass.type === 'password') {
                pass.type = 'text'; icon.className = 'bi bi-eye-slash';
            } else {
                pass.type = 'password'; icon.className = 'bi bi-eye';
            }
        });

        // Loading state on submit
        document.getElementById('loginForm').addEventListener('submit', function() {
            document.getElementById('loginBtn').disabled = true;
            document.getElementById('loginText').style.display = 'none';
            document.getElementById('loginLoading').style.display = 'inline-flex';
            document.getElementById('loginLoading').style.alignItems = 'center';
        });
    </script>
</body>
</html>
