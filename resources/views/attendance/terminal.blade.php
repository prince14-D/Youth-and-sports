<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Attendance Terminal | Ministry of Youth and Sports</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=DM+Sans:wght@500;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #050b16;
            --panel: rgba(8,17,32,0.85);
            --border: rgba(255,255,255,0.08);
            --text: #f8fafc;
            --muted: #94a3b8;
            --green: #10b981;
            --blue: #38bdf8;
            --red: #f87171;
            --gold: #f59e0b;
            --radius: 24px;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { height: 100%; }
        body {
            min-height: 100%;
            font-family: 'Inter', sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left,  rgba(56,189,248,0.15), transparent 30%),
                radial-gradient(circle at 85% 10%,   rgba(16,185,129,0.13), transparent 28%),
                linear-gradient(180deg, #050b16 0%, #081120 55%, #050b16 100%);
            overflow-x: hidden;
        }

        /* ── Shell ───────────────────────────────────── */
        .shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        /* ── Terminal Card ───────────────────────────── */
        .terminal {
            width: 100%;
            max-width: 1120px;
            border-radius: var(--radius);
            border: 1px solid var(--border);
            background: var(--panel);
            box-shadow: 0 30px 120px rgba(0,0,0,0.5);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            padding: 20px;
            display: grid;
            grid-template-columns: 1fr 1.05fr;
            gap: 16px;
        }

        /* ── Hero (left) ─────────────────────────────── */
        .hero {
            border-radius: calc(var(--radius) - 6px);
            border: 1px solid var(--border);
            background: rgba(255,255,255,0.03);
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }
        .brand { display: flex; align-items: center; gap: 14px; }
        .brand img {
            width: 62px; height: 62px;
            object-fit: contain;
            background: rgba(255,255,255,0.08);
            border-radius: 16px;
            padding: 8px;
            flex-shrink: 0;
        }
        .brand-text p  { color: var(--muted); font-size: 12px; margin-bottom: 2px; }
        .brand-text h1 {
            font-family: 'DM Sans', sans-serif;
            font-size: clamp(17px, 2.6vw, 30px);
            font-weight: 800;
            line-height: 1.15;
        }
        .brand-sub { color: var(--muted); font-size: 11px; margin-top: 2px; }

        .clock { font-size: clamp(42px, 6.5vw, 84px); line-height: 1; font-weight: 900; letter-spacing: -0.04em; }
        .date-line { margin-top: 8px; font-size: clamp(12px, 1.3vw, 17px); color: rgba(255,255,255,0.8); font-weight: 600; }

        /* Mode badge */
        .mode-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            border-radius: 14px;
            font-weight: 800;
            font-size: 16px;
            letter-spacing: 0.02em;
        }
        .mode-badge.clock-in  { background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.4); color: #34d399; }
        .mode-badge.clock-out { background: rgba(56,189,248,0.12); border: 1px solid rgba(56,189,248,0.35); color: #7dd3fc; }
        .mode-dot { width: 9px; height: 9px; border-radius: 999px; }
        .mode-badge.clock-in  .mode-dot { background: #10b981; box-shadow: 0 0 8px rgba(16,185,129,0.8); }
        .mode-badge.clock-out .mode-dot { background: #38bdf8; box-shadow: 0 0 8px rgba(56,189,248,0.8); }

        .mode-toggle-row { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .mode-toggle-btn {
            padding: 7px 14px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
            border: 1px solid var(--border);
            background: rgba(255,255,255,0.06);
            color: var(--muted);
            cursor: pointer;
            transition: all .15s;
        }
        .mode-toggle-btn:hover { background: rgba(255,255,255,0.1); color: var(--text); }

        /* Status pills */
        .status-grid { display: grid; gap: 8px; }
        .status-pill {
            display: flex; justify-content: space-between; align-items: center;
            gap: 8px; padding: 10px 14px;
            border-radius: 13px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.06);
            font-size: 13px; flex-wrap: wrap;
        }
        .status-pill span { color: var(--muted); }
        .status-pill strong { color: var(--text); text-align: right; }

        /* Footer */
        .footer { display: flex; justify-content: space-between; align-items: center; gap: 8px; font-size: 11px; color: var(--muted); flex-wrap: wrap; margin-top: auto; }
        .device-chip {
            display: inline-flex; gap: 6px; align-items: center;
            padding: 6px 11px; border-radius: 999px;
            background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.8); font-size: 11px;
        }

        /* ── Right Panel ─────────────────────────────── */
        .panel {
            border-radius: calc(var(--radius) - 6px);
            border: 1px solid var(--border);
            background: rgba(255,255,255,0.03);
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .panel h2 { font-family: 'DM Sans', sans-serif; font-size: clamp(18px, 2.4vw, 26px); font-weight: 800; }

        /* ── Camera Area ─────────────────────────────── */
        .cam-wrap {
            position: relative;
            border-radius: 18px;
            overflow: hidden;
            background: #020617;
            aspect-ratio: 4/3;
            border: 3px solid transparent;
            transition: border-color .4s ease, box-shadow .4s ease;
        }
        .cam-wrap.detecting {
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(245,158,11,0.3), 0 0 40px rgba(245,158,11,0.2);
        }
        .cam-wrap.locked {
            border-color: var(--green);
            box-shadow: 0 0 0 3px rgba(16,185,129,0.35), 0 0 50px rgba(16,185,129,0.25);
        }
        .cam-wrap.submitting {
            border-color: var(--blue);
            box-shadow: 0 0 0 4px rgba(56,189,248,0.4), 0 0 60px rgba(56,189,248,0.3);
        }
        .cam-wrap video, .cam-wrap img {
            width: 100%; height: 100%;
            object-fit: cover; display: block;
        }

        /* Face reticle overlay */
        .reticle {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: min(55%, 200px);
            aspect-ratio: 3/4;
            border: 2px solid rgba(255,255,255,0.25);
            border-radius: 50%;
            pointer-events: none;
            transition: border-color .3s, box-shadow .3s;
        }
        .reticle::before, .reticle::after {
            content: '';
            position: absolute;
            width: 18px; height: 18px;
            border-style: solid;
            border-color: inherit;
        }
        .reticle::before { top: -2px; left: -2px; border-width: 3px 0 0 3px; border-radius: 4px 0 0 0; }
        .reticle::after  { bottom: -2px; right: -2px; border-width: 0 3px 3px 0; border-radius: 0 0 4px 0; }
        .cam-wrap.detecting .reticle { border-color: var(--gold); }
        .cam-wrap.locked    .reticle { border-color: var(--green); box-shadow: 0 0 20px rgba(16,185,129,0.6); }
        .cam-wrap.submitting .reticle { border-color: var(--blue); }

        /* Camera live badge */
        .cam-badge {
            position: absolute; left: 12px; top: 12px;
            display: inline-flex; align-items: center; gap: 7px;
            padding: 6px 11px; border-radius: 999px;
            background: rgba(10,15,30,0.85); backdrop-filter: blur(10px);
            font-size: 11px; font-weight: 700; z-index: 5;
        }
        .cam-dot {
            width: 8px; height: 8px; border-radius: 999px; background: #ef4444;
            animation: pulse-r 1.5s infinite;
        }
        @keyframes pulse-r {
            0%,100% { box-shadow: 0 0 0 3px rgba(239,68,68,0.2); }
            50%      { box-shadow: 0 0 0 6px rgba(239,68,68,0.08); }
        }

        /* Face status badge */
        .face-status {
            position: absolute;
            bottom: 12px; left: 50%;
            transform: translateX(-50%);
            display: flex; align-items: center; gap: 7px;
            padding: 7px 14px; border-radius: 999px;
            background: rgba(10,15,30,0.88); backdrop-filter: blur(10px);
            font-size: 12px; font-weight: 700;
            white-space: nowrap; z-index: 5;
            transition: all .3s;
        }
        .face-status.waiting   { color: var(--muted); }
        .face-status.detecting { color: var(--gold); }
        .face-status.locked    { color: var(--green); }
        .face-status.submitting { color: var(--blue); }

        /* Countdown ring */
        .countdown-overlay {
            position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            background: rgba(5,11,22,0.55);
            backdrop-filter: blur(2px);
            z-index: 10;
            pointer-events: none;
            opacity: 0; transition: opacity .3s;
        }
        .countdown-overlay.visible { opacity: 1; }
        .countdown-num {
            font-size: 96px; font-weight: 900; line-height: 1;
            font-family: 'DM Sans', sans-serif;
            animation: pop .5s ease-in-out;
        }
        @keyframes pop {
            0%   { transform: scale(1.4); opacity: 0; }
            100% { transform: scale(1);   opacity: 1; }
        }
        .countdown-label { font-size: 14px; font-weight: 600; color: rgba(255,255,255,0.75); margin-top: 6px; }

        /* Camera hint */
        .cam-hint { font-size: 12px; color: var(--muted); text-align: center; }

        /* ── Employee Result Card ────────────────────── */
        .employee-reveal {
            display: none;
            gap: 14px; align-items: center;
            padding: 14px; border-radius: 16px;
            background: rgba(16,185,129,0.08);
            border: 1px solid rgba(16,185,129,0.3);
            animation: slide-in .35s ease;
        }
        .employee-reveal.visible { display: flex; }
        @keyframes slide-in {
            from { opacity: 0; transform: translateY(6px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .avatar {
            width: 56px; height: 56px; border-radius: 13px;
            object-fit: cover; background: rgba(255,255,255,0.1); flex-shrink: 0;
        }
        .emp-name { font-weight: 700; font-size: 17px; }
        .emp-meta { font-size: 13px; color: var(--muted); margin-top: 3px; }
        .emp-badge {
            margin-left: auto; flex-shrink: 0;
            padding: 6px 12px; border-radius: 10px;
            font-size: 12px; font-weight: 800;
        }
        .emp-badge.in  { background: rgba(16,185,129,0.2); color: #34d399; }
        .emp-badge.out { background: rgba(56,189,248,0.2); color: #7dd3fc; }

        /* ── Manual Buttons (fallback) ───────────────── */
        .manual-row {
            display: grid; grid-template-columns: 1fr 1fr; gap: 10px;
        }
        .btn-action {
            border: 0; border-radius: 14px;
            padding: 13px 10px;
            font-size: 15px; font-weight: 800;
            cursor: pointer; color: #fff;
            transition: transform .12s, opacity .12s, box-shadow .12s;
            letter-spacing: 0.01em;
        }
        .btn-action:hover  { opacity: 0.9; transform: translateY(-1px); }
        .btn-action:active { transform: scale(0.98); }
        .btn-clock-in  { background: linear-gradient(135deg,#10b981,#059669); box-shadow: 0 4px 18px rgba(16,185,129,0.2); }
        .btn-clock-out { background: linear-gradient(135deg,#38bdf8,#2563eb); box-shadow: 0 4px 18px rgba(56,189,248,0.2); }
        .btn-secondary {
            grid-column: 1/-1;
            background: rgba(255,255,255,0.07); color: rgba(255,255,255,0.85);
            border: 1px solid var(--border); font-size: 13px;
            padding: 11px; border-radius: 12px;
        }
        .manual-label {
            text-align: center; font-size: 11px; color: var(--muted);
            letter-spacing: .04em; text-transform: uppercase;
        }

        /* ── Message Box ─────────────────────────────── */
        .msg-box {
            padding: 12px 14px; border-radius: 13px;
            font-size: 13px; line-height: 1.5;
            border: 1px solid var(--border);
            background: rgba(255,255,255,0.04);
            min-height: 48px;
        }
        .msg-box.success { border-color: rgba(16,185,129,0.35); color: #bbf7d0; }
        .msg-box.error   { border-color: rgba(248,113,113,0.35); color: #fecaca; }
        .msg-box.info    { border-color: rgba(56,189,248,0.3);   color: #bae6fd; }
        .msg-box.warn    { border-color: rgba(245,158,11,0.35);  color: #fde68a; }

        /* ── Setup Panel ─────────────────────────────── */
        .setup-panel {
            padding: 14px; border-radius: 14px;
            background: rgba(245,158,11,0.07);
            border: 1px solid rgba(245,158,11,0.25);
            display: none;
        }
        .setup-panel.visible { display: block; }
        .setup-panel label { font-size: 11px; color: var(--muted); display: block; margin-bottom: 5px; margin-top: 8px; }
        .setup-panel input {
            width: 100%; background: rgba(15,23,42,0.9);
            border: 1px solid rgba(255,255,255,0.1); color: var(--text);
            font-size: 13px; padding: 9px 12px; border-radius: 9px;
            outline: none; font-family: monospace;
        }
        .setup-panel input:focus { border-color: rgba(245,158,11,0.6); }
        .setup-btn {
            margin-top: 10px; width: 100%;
            background: linear-gradient(135deg,#f59e0b,#d97706); color: #fff;
            font-size: 13px; padding: 10px; border-radius: 9px; font-weight: 700;
            border: 0; cursor: pointer;
        }

        /* ── Keypad fallback ──────────────────────── */
        .keypad-panel {
            display: none;
            flex-direction: column;
            gap: 10px;
            padding: 14px;
            border-radius: 16px;
            background: rgba(56,189,248,0.07);
            border: 1px solid rgba(56,189,248,0.25);
            animation: slide-in .3s ease;
        }
        .keypad-panel.visible { display: flex; }
        .keypad-header {
            display: flex; justify-content: space-between; align-items: center;
        }
        .keypad-title { font-size: 13px; font-weight: 700; color: var(--blue); }
        .keypad-close {
            background: none; border: none;
            color: var(--muted); font-size: 18px; cursor: pointer;
            padding: 2px 6px; border-radius: 6px;
            transition: color .15s;
        }
        .keypad-close:hover { color: var(--text); }
        .keypad-display {
            background: rgba(15,23,42,0.9);
            border: 1px solid rgba(56,189,248,0.3);
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 24px;
            font-family: monospace;
            font-weight: 700;
            color: var(--text);
            text-align: center;
            letter-spacing: 4px;
            min-height: 52px;
        }
        .keypad-display .placeholder { color: var(--muted); font-size: 14px; letter-spacing: 1px; font-family: 'Inter', sans-serif; font-weight: 400; }
        .keypad-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }
        .key {
            border: 1px solid rgba(255,255,255,0.08);
            background: rgba(255,255,255,0.07);
            color: var(--text);
            border-radius: 10px;
            padding: 14px;
            font-size: 20px;
            font-weight: 700;
            cursor: pointer;
            transition: background .1s, transform .08s;
            font-family: 'DM Sans', sans-serif;
        }
        .key:hover  { background: rgba(255,255,255,0.12); }
        .key:active { transform: scale(0.95); background: rgba(56,189,248,0.18); }
        .key-del  { background: rgba(248,113,113,0.1); border-color: rgba(248,113,113,0.2); color: #f87171; font-size: 16px; }
        .key-del:hover { background: rgba(248,113,113,0.2); }
        .key-submit {
            grid-column: 1 / -1;
            background: linear-gradient(135deg,#38bdf8,#2563eb);
            border: none;
            border-radius: 10px;
            padding: 14px;
            font-size: 15px;
            font-weight: 800;
            color: #fff;
            cursor: pointer;
            transition: opacity .15s, transform .1s;
        }
        .key-submit:hover  { opacity: 0.9; }
        .key-submit:active { transform: scale(0.98); }

        /* ── Cooldown bar ────────────────────────────── */
        .cooldown-bar-wrap {
            height: 4px; border-radius: 2px;
            background: rgba(255,255,255,0.07);
            overflow: hidden; display: none;
        }
        .cooldown-bar-wrap.visible { display: block; }
        .cooldown-bar {
            height: 100%; border-radius: 2px;
            background: linear-gradient(90deg,#10b981,#38bdf8);
            transition: width 1s linear;
        }

        /* ── Responsive ──────────────────────────────── */
        @media (max-width: 900px) {
            .terminal { grid-template-columns: 1fr; max-width: 520px; }
            body { overflow-y: auto; }
        }
        @media (max-width: 480px) {
            .shell { padding: 8px; align-items: flex-start; }
            .terminal { padding: 10px; gap: 10px; border-radius: 18px; }
            .hero { padding: 14px; gap: 12px; }
            .panel { padding: 12px; gap: 10px; }
            .brand img { width: 46px; height: 46px; }
            .clock { font-size: clamp(40px, 14vw, 60px); }
            .manual-row { gap: 8px; }
        }
        @media (min-width: 901px) {
            body { overflow: hidden; }
            .shell { height: 100vh; }
            .terminal { min-height: min(94vh, 960px); }
        }
    </style>
</head>
<body>
<div class="shell">
<main class="terminal" role="main">

    {{-- ── LEFT: Hero ── --}}
    <section class="hero">
        <div>
            <div class="brand">
                <img src="{{ asset('images/logo.png') }}" alt="Ministry logo">
                <div class="brand-text">
                    <p>Republic of Liberia</p>
                    <h1>Ministry of Youth and Sports</h1>
                    <p class="brand-sub">Dedicated Attendance Terminal</p>
                </div>
            </div>
        </div>

        <div>
            <div class="clock"  id="liveClock" aria-live="polite">--:--:--</div>
            <div class="date-line" id="liveDate">Loading…</div>
        </div>

        <div class="mode-toggle-row">
            <div class="mode-badge clock-in" id="modeBadge">
                <span class="mode-dot"></span>
                <span id="modeBadgeText">AUTO CLOCK-IN</span>
            </div>
            <button class="mode-toggle-btn" onclick="toggleMode()" id="modeToggleBtn" title="Override auto mode">Switch</button>
        </div>

        <div class="status-grid">
            <div class="status-pill">
                <span>Terminal</span>
                <strong id="terminalNameEl">{{ $terminalName }}</strong>
            </div>
            <div class="status-pill">
                <span>Auto-Recognition</span>
                <strong id="syncStatus" style="color:#10b981;">Scanning…</strong>
            </div>
            <div class="status-pill">
                <span>Offline Queue</span>
                <strong id="queueCount">0 pending</strong>
            </div>
        </div>

        <div class="footer">
            <span class="device-chip">
                {{ $device?->device_type ?? 'terminal' }} ·
                <span id="deviceIdChip">{{ $device?->device_id ?? 'unregistered' }}</span>
            </span>
            <span>Auto face recognition active</span>
        </div>
    </section>

    {{-- ── RIGHT: Check-In Panel ── --}}
    <section class="panel">
        <div>
            <h2>Employee Check-In</h2>
            <p style="font-size:13px;color:var(--muted);">
                Look directly at the camera — the system will identify and clock you in/out automatically.
            </p>
        </div>

        {{-- Camera --}}
        <div class="cam-wrap" id="camWrap">
            <video id="cameraPreview" autoplay playsinline muted></video>
            <img  id="capturedPreview" alt="Captured photo" style="display:none;">

            <div class="cam-badge">
                <span class="cam-dot"></span> Live Camera
            </div>

            <div class="reticle" id="reticle"></div>

            <div class="face-status waiting" id="faceStatus">
                👁 Position your face in the frame
            </div>

            {{-- Countdown overlay --}}
            <div class="countdown-overlay" id="countdownOverlay">
                <div class="countdown-num" id="countdownNum">3</div>
                <div class="countdown-label" id="countdownLabel">Hold still…</div>
            </div>
        </div>

        {{-- Cooldown progress bar --}}
        <div class="cooldown-bar-wrap" id="cooldownBarWrap">
            <div class="cooldown-bar" id="cooldownBar" style="width:100%"></div>
        </div>

        {{-- Employee Result --}}
        <div class="employee-reveal" id="employeeReveal">
            <img id="employeePhoto" class="avatar" src="{{ asset('images/default-avatar.png') }}" alt="Employee">
            <div>
                <div class="emp-name" id="employeeName">—</div>
                <div class="emp-meta" id="employeeMeta">—</div>
                <div class="emp-meta" id="employeeNumber" style="font-size:11px;margin-top:2px;"></div>
            </div>
            <span class="emp-badge in" id="empActionBadge">Clocked In</span>
        </div>

        {{-- Setup Panel (shown when unregistered) --}}
        <div class="setup-panel" id="setupPanel">
            <div style="color:var(--gold);font-size:12px;font-weight:700;margin-bottom:8px;">⚠ Terminal Not Configured</div>
            <p style="font-size:11px;color:var(--muted);margin-bottom:6px;">Enter the Device ID and Token from HR Admin → Attendance Devices.</p>
            <label>Device ID</label>
            <input type="text" id="setupDeviceId" placeholder="ATD-XXXXXXXXXX">
            <label>Device Token</label>
            <input type="text" id="setupDeviceToken" placeholder="Paste token from Devices page">
            <button class="setup-btn" onclick="activateTerminal()">Activate Terminal</button>
        </div>

        {{-- Message Box --}}
        <div class="msg-box" id="messageBox" role="status" aria-live="polite">
            Ready. Looking for faces…
        </div>

        {{-- Keypad / Employee ID Fallback --}}
        <div class="keypad-panel" id="keypadPanel">
            <div class="keypad-header">
                <span class="keypad-title">📋 Enter Employee ID</span>
                <button class="keypad-close" onclick="hideKeypad()" title="Close">✕</button>
            </div>
            <div class="keypad-display" id="keypadDisplay">
                <span class="placeholder" id="keypadPlaceholder">Type your Employee Number…</span>
            </div>
            <div class="keypad-grid">
                <button class="key" onclick="keypadPress('1')">1</button>
                <button class="key" onclick="keypadPress('2')">2</button>
                <button class="key" onclick="keypadPress('3')">3</button>
                <button class="key" onclick="keypadPress('4')">4</button>
                <button class="key" onclick="keypadPress('5')">5</button>
                <button class="key" onclick="keypadPress('6')">6</button>
                <button class="key" onclick="keypadPress('7')">7</button>
                <button class="key" onclick="keypadPress('8')">8</button>
                <button class="key" onclick="keypadPress('9')">9</button>
                <button class="key" style="font-size:13px;" onclick="keypadPress('CLEAR')">CLR</button>
                <button class="key" onclick="keypadPress('0')">0</button>
                <button class="key key-del" onclick="keypadPress('DEL')">⌫</button>
            </div>
            <button class="key-submit" onclick="keypadSubmit()">
                ✓ Confirm &amp; Clock {{ ucfirst(str_replace('_', ' ', '')) }}
                <span id="keypadActionLabel">In/Out</span>
            </button>
        </div>

        {{-- Manual override --}}
        <div class="manual-label">— Manual Override —</div>
        <div class="manual-row">
            <button class="btn-action btn-clock-in"  onclick="manualPunch('clock_in')">⏵ Clock In</button>
            <button class="btn-action btn-clock-out" onclick="manualPunch('clock_out')">⏹ Clock Out</button>
            <button class="btn-secondary" onclick="showKeypad()" style="background:rgba(56,189,248,0.1);border-color:rgba(56,189,248,0.25);color:var(--blue);">📋 Enter Employee ID</button>
            <button class="btn-secondary" onclick="syncPendingEvents()">↻ Sync Pending</button>
        </div>
    </section>

</main>
</div>

{{-- face-api.js for real face detection --}}
<script src="{{ asset('js/faceapi/face-api.min.js') }}"></script>
<script>
(function () {
    'use strict';

    /* ── Constants ─────────────────────────────────────────────────── */
    const SYNC_URL          = @json(url('/api/v1/attendance/punch'));
    const QUEUE_KEY         = 'attendance_terminal_queue';
    const DEVICE_ID_KEY     = 'attendance_device_id';
    const DEVICE_TOKEN_KEY  = 'attendance_device_token';
    const COOLDOWN_SECONDS  = 45;       // seconds between auto-punches
    const HOLD_SECONDS      = 3;        // seconds face must be held before auto-punch
    const SCAN_INTERVAL_MS  = 400;      // how often to scan for faces

    /* ── Device credentials ────────────────────────────────────────── */
    const urlParams  = new URLSearchParams(window.location.search);
    let deviceId     = urlParams.get('device_id')    || localStorage.getItem(DEVICE_ID_KEY)    || @json($device?->device_id);
    let deviceToken  = urlParams.get('device_token') || localStorage.getItem(DEVICE_TOKEN_KEY) || @json($deviceToken ?: '');
    let terminalLoc  = @json($device?->location);

    if (deviceId)    localStorage.setItem(DEVICE_ID_KEY,    deviceId);
    if (deviceToken) localStorage.setItem(DEVICE_TOKEN_KEY, deviceToken);

    /* ── State ─────────────────────────────────────────────────────── */
    let cameraStream      = null;
    let scanTimer         = null;
    let faceHoldCount     = 0;           // consecutive scans with face detected
    let countdownValue    = 0;
    let countdownTimer    = null;
    let cooldownActive    = false;
    let cooldownTimer     = null;
    let cooldownRemaining = 0;
    let manualMode        = null;        // null = auto, 'clock_in' or 'clock_out' = forced

    /* ── DOM refs ──────────────────────────────────────────────────── */
    const camWrap           = document.getElementById('camWrap');
    const cameraPreview     = document.getElementById('cameraPreview');
    const capturedPreview   = document.getElementById('capturedPreview');
    const faceStatusEl      = document.getElementById('faceStatus');
    const countdownOverlay  = document.getElementById('countdownOverlay');
    const countdownNumEl    = document.getElementById('countdownNum');
    const countdownLabelEl  = document.getElementById('countdownLabel');
    const employeeReveal    = document.getElementById('employeeReveal');
    const employeePhoto     = document.getElementById('employeePhoto');
    const employeeName      = document.getElementById('employeeName');
    const employeeMeta      = document.getElementById('employeeMeta');
    const employeeNumber    = document.getElementById('employeeNumber');
    const empActionBadge    = document.getElementById('empActionBadge');
    const messageBox        = document.getElementById('messageBox');
    const queueCountEl      = document.getElementById('queueCount');
    const syncStatusEl      = document.getElementById('syncStatus');
    const setupPanel        = document.getElementById('setupPanel');
    const modeBadge         = document.getElementById('modeBadge');
    const modeBadgeText     = document.getElementById('modeBadgeText');
    const modeToggleBtn     = document.getElementById('modeToggleBtn');
    const cooldownBarWrap   = document.getElementById('cooldownBarWrap');
    const cooldownBar       = document.getElementById('cooldownBar');

    /* ── Clock ─────────────────────────────────────────────────────── */
    function updateClock() {
        const now = new Date();
        document.getElementById('liveClock').textContent =
            now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        document.getElementById('liveDate').textContent =
            now.toLocaleDateString([], { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        updateModeFromTime();
    }

    /* ── Auto mode based on time ───────────────────────────────────── */
    function getAutoAction() {
        if (manualMode) return manualMode;
        return new Date().getHours() < 13 ? 'clock_in' : 'clock_out'; // noon+1h cutoff
    }

    function updateModeFromTime() {
        if (manualMode) return;
        const action = getAutoAction();
        if (action === 'clock_in') {
            modeBadge.className = 'mode-badge clock-in';
            modeBadgeText.textContent = '● AUTO CLOCK-IN';
        } else {
            modeBadge.className = 'mode-badge clock-out';
            modeBadgeText.textContent = '● AUTO CLOCK-OUT';
        }
    }

    window.toggleMode = function () {
        const auto = getAutoAction();
        if (!manualMode) {
            manualMode = auto === 'clock_in' ? 'clock_out' : 'clock_in';
            modeToggleBtn.textContent = 'Auto';
            modeToggleBtn.title = 'Return to automatic mode';
        } else {
            manualMode = null;
            modeToggleBtn.textContent = 'Switch';
            modeToggleBtn.title = 'Override auto mode';
        }
        updateModeFromTime();
        if (manualMode) {
            modeBadge.className = 'mode-badge ' + manualMode.replace('_', '-');
            modeBadgeText.textContent = (manualMode === 'clock_in' ? '● FORCED CLOCK-IN' : '● FORCED CLOCK-OUT');
        }
    };

    /* ── Message helper ────────────────────────────────────────────── */
    function setMsg(text, type) {
        messageBox.className = 'msg-box' + (type ? ' ' + type : '');
        messageBox.textContent = text;
    }

    /* ── Queue helpers ─────────────────────────────────────────────── */
    function readQueue()  { try { return JSON.parse(localStorage.getItem(QUEUE_KEY) || '[]'); } catch(e) { return []; } }
    function writeQueue(items) { localStorage.setItem(QUEUE_KEY, JSON.stringify(items)); queueCountEl.textContent = items.length + ' pending'; }

    /* ── Terminal status ───────────────────────────────────────────── */
    function updateTerminalStatus() {
        const ok = deviceId && deviceToken;
        syncStatusEl.textContent  = ok ? 'Scanning…' : 'Not configured';
        syncStatusEl.style.color  = ok ? '#10b981' : '#f59e0b';
        document.getElementById('deviceIdChip').textContent = deviceId || 'unregistered';
        ok ? setupPanel.classList.remove('visible') : setupPanel.classList.add('visible');
    }

    window.activateTerminal = function () {
        const id  = document.getElementById('setupDeviceId').value.trim();
        const tok = document.getElementById('setupDeviceToken').value.trim();
        if (!id || !tok) { setMsg('Enter both Device ID and Device Token.', 'error'); return; }
        deviceId = id; deviceToken = tok;
        localStorage.setItem(DEVICE_ID_KEY, id);
        localStorage.setItem(DEVICE_TOKEN_KEY, tok);
        updateTerminalStatus();
        setMsg('Terminal activated for Device: ' + id, 'success');
    };

    /* ── Camera ────────────────────────────────────────────────────── */
    async function startCamera() {
        try {
            cameraStream = await navigator.mediaDevices.getUserMedia({
                video: { width: { ideal: 1280 }, height: { ideal: 720 }, facingMode: 'user' },
                audio: false,
            });
            cameraPreview.srcObject = cameraStream;
            // Wait for video to be playing and dimensions available
            await new Promise(function(resolve) {
                cameraPreview.onloadedmetadata = function() {
                    cameraPreview.play().then(resolve).catch(resolve);
                };
            });
            await new Promise(function(r) { setTimeout(r, 600); }); // ensure videoWidth populated
            initFaceScanner();
        } catch (err) {
            setMsg('Camera access required. Please allow camera permissions and refresh.', 'error');
            syncStatusEl.textContent = 'Camera error';
            syncStatusEl.style.color = '#f87171';
        }
    }

    function captureFrame() {
        const canvas  = document.createElement('canvas');
        canvas.width  = cameraPreview.videoWidth  || 640;
        canvas.height = cameraPreview.videoHeight || 480;
        canvas.getContext('2d').drawImage(cameraPreview, 0, 0, canvas.width, canvas.height);
        return canvas.toDataURL('image/jpeg', 0.9);
    }

    /* ── Face Detection (face-api.js TinyFaceDetector) ─────────────── */
    let faceApiReady  = false;
    let scanCanvas    = null;
    let scanCtx       = null;
    const WEIGHTS_URL = '{{ asset("js/faceapi/weights") }}';

    // Descriptor (recognition) models are heavier than TinyFaceDetector and
    // load separately — the fast detection loop below never waits on them.
    // If they fail to load, punches still work via the server-side hash
    // fallback in AttendanceService, just without descriptor-based matching.
    let descriptorModelsReady = false;

    async function initFaceScanner() {
        // Set up a reusable off-screen canvas for frame capture
        scanCanvas        = document.createElement('canvas');
        scanCanvas.width  = 320;
        scanCanvas.height = 240;
        scanCtx           = scanCanvas.getContext('2d');

        setFaceStatus('waiting', '⏳ Loading face detector…');
        syncStatusEl.textContent = 'Loading…';
        syncStatusEl.style.color = '#f59e0b';

        try {
            await faceapi.nets.tinyFaceDetector.loadFromUri(WEIGHTS_URL);
            faceApiReady = true;
            setFaceStatus('waiting', '👁 Position your face in the frame');
            syncStatusEl.textContent = 'Scanning…';
            syncStatusEl.style.color = '#10b981';
            setMsg('Camera active. Stand in front of the camera to clock in/out.', 'info');
        } catch (e) {
            // Model load failed — fall back to presence detection
            faceApiReady = false;
            setFaceStatus('waiting', '👁 Position your face in the frame');
            syncStatusEl.textContent = 'Scanning (fallback)';
            syncStatusEl.style.color = '#f59e0b';
            setMsg('Face model not loaded. You can still use the keypad to clock in/out.', 'warn');
        }

        // Load the heavier recognition models in the background — not
        // required for the countdown UX, only for descriptor extraction
        // at the moment of actual submission.
        Promise.all([
            faceapi.nets.faceLandmark68Net.loadFromUri(WEIGHTS_URL),
            faceapi.nets.faceRecognitionNet.loadFromUri(WEIGHTS_URL),
        ]).then(function () {
            descriptorModelsReady = true;
        }).catch(function (e) {
            descriptorModelsReady = false;
            console.warn('Face recognition models unavailable — punches will use the fallback matcher:', e);
        });

        // Start the scan loop
        scanTimer = setInterval(runScanCycle, SCAN_INTERVAL_MS);
    }

    async function extractFaceDescriptor() {
        if (!faceApiReady || !descriptorModelsReady) return null;
        try {
            const opts = new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.4 });
            const result = await faceapi.detectSingleFace(cameraPreview, opts)
                .withFaceLandmarks()
                .withFaceDescriptor();
            return result && result.descriptor ? Array.from(result.descriptor) : null;
        } catch (e) {
            return null;
        }
    }

    async function detectFace() {
        if (!cameraStream || cooldownActive) return false;
        if (cameraPreview.readyState < 2 || cameraPreview.videoWidth === 0) return false;

        if (faceApiReady) {
            // ── Primary: face-api.js TinyFaceDetector ──
            try {
                const opts = new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.4 });
                const result = await faceapi.detectSingleFace(cameraPreview, opts);
                return !!result;  // true if a face found
            } catch (e) {
                // fall through to presence fallback
            }
        }

        // ── Fallback: brightness & variance in center of frame ──
        // (used when face-api model unavailable)
        try {
            scanCtx.drawImage(cameraPreview, 0, 0, 320, 240);
            // Sample the center 40% of the frame where a face would be
            const cx = 96, cy = 48, cw = 128, ch = 144;
            const imgData = scanCtx.getImageData(cx, cy, cw, ch).data;
            let brightness = 0, variance = 0;
            const samples = imgData.length / 4;
            for (let i = 0; i < imgData.length; i += 4) {
                brightness += (imgData[i] * 0.299 + imgData[i+1] * 0.587 + imgData[i+2] * 0.114);
            }
            brightness /= samples;
            for (let i = 0; i < imgData.length; i += 4) {
                const lum = imgData[i] * 0.299 + imgData[i+1] * 0.587 + imgData[i+2] * 0.114;
                variance += (lum - brightness) * (lum - brightness);
            }
            variance /= samples;
            // A face typically: brightness 80-200, variance > 400 (skin texture)
            return brightness > 70 && brightness < 220 && variance > 300;
        } catch(e) {
            return false;
        }
    }

    async function runScanCycle() {
        if (cooldownActive) return;

        const facePresent = await detectFace();

        if (facePresent) {
            faceHoldCount++;

            if (faceHoldCount === 1) {
                // Face just appeared
                setCamState('detecting');
                setFaceStatus('detecting', '👤 Face detected — hold still…');
            }

            const holdScans = Math.ceil((HOLD_SECONDS * 1000) / SCAN_INTERVAL_MS);

            if (faceHoldCount >= holdScans && countdownValue === 0 && !countdownTimer) {
                // Start countdown
                startCountdown();
            }

            // Update countdown display
            if (countdownTimer) {
                const remaining = HOLD_SECONDS - Math.floor((faceHoldCount - holdScans) * SCAN_INTERVAL_MS / 1000);
                // (managed by startCountdown)
            }
        } else {
            // Face lost — reset
            if (faceHoldCount > 0) {
                faceHoldCount = 0;
                cancelCountdown();
                setCamState('');
                setFaceStatus('waiting', '👁 Position your face in the frame');
            }
        }
    }

    function startCountdown() {
        countdownValue = HOLD_SECONDS;
        countdownOverlay.classList.add('visible');
        countdownNumEl.textContent = countdownValue;
        countdownLabelEl.textContent = 'Hold still — auto ' + (getAutoAction() === 'clock_in' ? 'Clock-In' : 'Clock-Out') + '…';
        setCamState('locked');
        setFaceStatus('locked', '✅ Face locked — submitting in ' + countdownValue + 's…');

        countdownTimer = setInterval(function () {
            countdownValue--;
            countdownNumEl.textContent = countdownValue;
            setFaceStatus('locked', '✅ Face locked — submitting in ' + countdownValue + 's…');

            if (countdownValue <= 0) {
                clearInterval(countdownTimer);
                countdownTimer = null;
                autoSubmit();
            }
        }, 1000);
    }

    function cancelCountdown() {
        if (countdownTimer) { clearInterval(countdownTimer); countdownTimer = null; }
        countdownValue = 0;
        countdownOverlay.classList.remove('visible');
    }

    function setCamState(state) {
        camWrap.className = 'cam-wrap' + (state ? ' ' + state : '');
    }

    function setFaceStatus(type, text) {
        faceStatusEl.className = 'face-status ' + type;
        faceStatusEl.textContent = text;
    }

    /* ── Cooldown ───────────────────────────────────────────────── */
    function startCooldown() {
        cooldownActive    = true;
        cooldownRemaining = COOLDOWN_SECONDS;
        cooldownBarWrap.classList.add('visible');
        cooldownBar.style.width = '100%';

        faceHoldCount = 0;
        setCamState('');
        setFaceStatus('waiting', '⏳ Cooldown — ' + cooldownRemaining + 's before next scan');
        syncStatusEl.textContent = 'Cooldown…';
        syncStatusEl.style.color = '#f59e0b';

        const step = 1;
        cooldownTimer = setInterval(function () {
            cooldownRemaining -= step;
            const pct = (cooldownRemaining / COOLDOWN_SECONDS) * 100;
            cooldownBar.style.width = Math.max(0, pct) + '%';
            setFaceStatus('waiting', '⏳ Next scan in ' + cooldownRemaining + 's');

            if (cooldownRemaining <= 0) {
                clearInterval(cooldownTimer);
                cooldownActive = false;
                cooldownBarWrap.classList.remove('visible');
                setFaceStatus('waiting', '👁 Position your face in the frame');
                syncStatusEl.textContent = 'Scanning…';
                syncStatusEl.style.color = '#10b981';
                // Hide employee card after cooldown
                employeeReveal.classList.remove('visible');
            }
        }, step * 1000);
    }

    /* ── Submit ─────────────────────────────────────────────────── */
    async function doSubmit(action, capturedPhoto, faceDescriptor) {
        if (!deviceId || !deviceToken) {
            setMsg('Terminal not configured. Enter Device ID and Token above.', 'error');
            setupPanel.classList.add('visible');
            return;
        }

        setCamState('submitting');
        setFaceStatus('submitting', '📡 Submitting attendance…');
        setMsg('Identifying employee and recording attendance…', '');

        const payload = {
            employee_identifier: null,
            action:             action,
            device_id:          deviceId,
            device_token:       deviceToken,
            attendance_method:  'face',
            captured_photo:     capturedPhoto,
            face_descriptor:    faceDescriptor || null,
            terminal_location:  terminalLoc,
            attendance_date:    new Date().toISOString().slice(0, 10),
            submitted_at:       new Date().toISOString(),
            submission_uuid:    (crypto.randomUUID ? crypto.randomUUID() : Date.now().toString(36) + Math.random().toString(36)),
            notes:              null,
        };

        try {
            const data = await postAttendance(payload);
            onSuccess(data, action);
        } catch (err) {
            if (!navigator.onLine) { queueSubmission(payload); return; }
            const errMsg = err.message || 'Identification failed.';
            setMsg('❌ ' + errMsg, 'error');
            setCamState('');
            setFaceStatus('waiting', '👁 Try again — position face in the frame');
            cancelCountdown();
            faceHoldCount = 0;

            // Auto-show keypad if face recognition failed so employee can use ID
            if (errMsg.toLowerCase().includes('no matching') || errMsg.toLowerCase().includes('detected')) {
                setTimeout(function () {
                    setMsg('Face not recognized. Use the keypad below to enter your Employee ID.', 'warn');
                    showKeypad();
                }, 1200);
            }
        }
    }

    async function autoSubmit() {
        countdownOverlay.classList.remove('visible');
        const action = getAutoAction();
        const photo  = captureFrame();
        const descriptor = await extractFaceDescriptor();

        // Flash the capture
        capturedPreview.src = photo;
        capturedPreview.style.display = 'block';
        cameraPreview.style.display = 'none';
        setTimeout(function() {
            capturedPreview.style.display = 'none';
            cameraPreview.style.display = 'block';
        }, 800);

        await doSubmit(action, photo, descriptor);
    }

    function onSuccess(data, action) {
        const name   = data.employee?.name        || 'Employee Identified';
        const dept   = data.employee?.department  || '';
        const no     = data.employee?.employee_no || '';
        const label  = [name, dept].filter(Boolean).join(' — ');

        setMsg('✅ ' + label + '\n' + data.message, 'success');

        // Show employee card
        employeeReveal.classList.add('visible');
        employeePhoto.src = data.employee?.photo_url || employeePhoto.src;
        employeeName.textContent  = name;
        employeeMeta.textContent  = dept;
        employeeNumber.textContent = no;

        const isIn = action === 'clock_in';
        empActionBadge.textContent = isIn ? 'Clocked In ✓' : 'Clocked Out ✓';
        empActionBadge.className   = 'emp-badge ' + (isIn ? 'in' : 'out');

        syncStatusEl.textContent = 'Recorded!';
        syncStatusEl.style.color = '#10b981';

        // Begin cooldown
        startCooldown();
        faceHoldCount = 0;

        syncPendingEvents();
    }

    /* ── Manual punch override ─────────────────────────────────── */
    window.manualPunch = async function (action) {
        cancelCountdown();
        faceHoldCount = 0;
        const photo = captureFrame();
        const descriptor = await extractFaceDescriptor();
        await doSubmit(action, photo, descriptor);
    };

    /* ── Network ────────────────────────────────────────────────── */
    async function postAttendance(payload) {
        const res = await fetch(SYNC_URL, {
            method: 'POST',
            headers: {
                'Content-Type':  'application/json',
                'Accept':        'application/json',
                'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(payload),
        });
        const data = await res.json().catch(function () { return {}; });
        if (!res.ok) {
            let msg = data.message || 'Request failed.';
            if (data.errors) {
                const k = Object.keys(data.errors)[0];
                if (k) msg = data.errors[k][0] || msg;
            }
            throw new Error(msg);
        }
        return data;
    }

    function queueSubmission(payload) {
        const q = readQueue(); q.push(payload); writeQueue(q);
        setMsg('📶 Offline — attendance saved locally. Will sync when reconnected.', 'warn');
    }

    window.syncPendingEvents = async function () {
        const queue = readQueue();
        if (!queue.length) { writeQueue(queue); return; }
        if (!navigator.onLine) { syncStatusEl.textContent = 'Offline'; return; }
        syncStatusEl.textContent = 'Syncing…';
        const remaining = [];
        for (const p of queue) {
            try { await postAttendance(p); } catch(e) { remaining.push(p); }
        }
        writeQueue(remaining);
        syncStatusEl.textContent = remaining.length ? 'Pending sync' : 'Scanning…';
    };

    /* ── Keypad Fallback (Employee ID entry) ───────────────────── */
    let keypadValue = '';
    const keypadPanel     = document.getElementById('keypadPanel');
    const keypadDisplay   = document.getElementById('keypadDisplay');
    const keypadPlaceholder = document.getElementById('keypadPlaceholder');
    const keypadActionLbl = document.getElementById('keypadActionLabel');

    window.showKeypad = function () {
        keypadValue = '';
        renderKeypadDisplay();
        const action = getAutoAction();
        keypadActionLbl.textContent = action === 'clock_in' ? 'In' : 'Out';
        keypadPanel.classList.add('visible');
        keypadPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    };

    window.hideKeypad = function () {
        keypadPanel.classList.remove('visible');
        keypadValue = '';
    };

    function renderKeypadDisplay() {
        if (keypadValue === '') {
            keypadDisplay.innerHTML = '<span class="placeholder" id="keypadPlaceholder">Type your Employee Number…</span>';
        } else {
            keypadDisplay.textContent = keypadValue;
        }
    }

    window.keypadPress = function (key) {
        if (key === 'DEL')   { keypadValue = keypadValue.slice(0, -1); }
        else if (key === 'CLEAR') { keypadValue = ''; }
        else if (keypadValue.length < 12) { keypadValue += key; }
        renderKeypadDisplay();
    };

    window.keypadSubmit = async function () {
        if (!keypadValue.trim()) {
            setMsg('Please enter your Employee Number on the keypad.', 'error');
            return;
        }
        const action = getAutoAction();
        const photo  = captureFrame();
        hideKeypad();
        cancelCountdown();
        faceHoldCount = 0;

        if (!deviceId || !deviceToken) {
            setMsg('Terminal not configured. Enter Device ID and Token.', 'error');
            setupPanel.classList.add('visible');
            return;
        }

        setCamState('submitting');
        setFaceStatus('submitting', '📡 Submitting via Employee ID…');
        setMsg('Looking up Employee #' + keypadValue + '…', '');

        const payload = {
            employee_identifier: keypadValue.trim(),
            action:              action,
            device_id:           deviceId,
            device_token:        deviceToken,
            attendance_method:   'manual',
            captured_photo:      photo,
            terminal_location:   terminalLoc,
            attendance_date:     new Date().toISOString().slice(0, 10),
            submitted_at:        new Date().toISOString(),
            submission_uuid:     (crypto.randomUUID ? crypto.randomUUID() : Date.now().toString(36) + Math.random().toString(36)),
            notes:               'Keypad ID entry',
        };

        try {
            const data = await postAttendance(payload);
            onSuccess(data, action);
        } catch (err) {
            if (!navigator.onLine) { queueSubmission(payload); return; }
            setMsg('❌ ' + (err.message || 'ID not found. Try again.'), 'error');
            setCamState('');
            setFaceStatus('waiting', '👁 Position your face in the frame');
        }
    };

    /* ── Init ───────────────────────────────────────────────────── */
    setInterval(updateClock, 1000);
    updateClock();
    writeQueue(readQueue());
    updateTerminalStatus();
    startCamera();
    window.addEventListener('online', syncPendingEvents);
})();
</script>
</body>
</html>
