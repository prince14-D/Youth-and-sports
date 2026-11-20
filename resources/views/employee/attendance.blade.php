<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <meta name="theme-color" content="#12355b">
    <title>Employee Attendance | Ministry of Youth and Sports</title>
    <style>
        :root { --navy:#12355b; --red:#dc2626; --ink:#172033; --muted:#667085; --line:#d9e1ec; --soft:#f4f7fb; --green:#07855b; }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; font-family:Arial,sans-serif; color:var(--ink); background:linear-gradient(135deg,#eef4f9,#ffffff 48%,#fef2f2); }
        .shell { max-width:1080px; margin:0 auto; padding:24px; }
        .topbar { display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:22px; }
        .brand { display:flex; align-items:center; gap:12px; }
        .brand img { width:48px; height:48px; object-fit:contain; }
        .eyebrow { color:var(--red); font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; }
        h1 { color:var(--navy); font-size:24px; margin:2px 0; }
        .clock { color:var(--navy); font-size:28px; font-weight:800; text-align:right; }
        .date { color:var(--muted); font-size:12px; text-align:right; }
        .grid { display:grid; grid-template-columns:minmax(0,1.3fr) minmax(300px,.7fr); gap:20px; }
        .card { background:#fff; border:1px solid var(--line); border-radius:14px; box-shadow:0 14px 40px rgba(18,53,91,.08); padding:24px; }
        .status { display:flex; align-items:center; gap:8px; color:var(--green); font-size:13px; font-weight:700; margin-bottom:18px; }
        .dot { width:9px; height:9px; border-radius:50%; background:var(--green); }
        .copy { color:var(--muted); font-size:14px; line-height:1.5; }
        label { display:block; color:var(--navy); font-size:13px; font-weight:700; margin:16px 0 7px; }
        input { width:100%; border:1px solid var(--line); border-radius:9px; padding:12px; font-size:16px; }
        input:focus { outline:2px solid #bfdbfe; border-color:#2563eb; }
        .actions { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top:18px; }
        button { border:0; border-radius:9px; padding:13px; color:#fff; cursor:pointer; font-weight:700; font-size:14px; }
        button:disabled { opacity:.5; cursor:not-allowed; }
        .in { background:var(--green); } .out { background:#2563eb; } .secondary { background:var(--navy); width:100%; margin-top:12px; }
        .camera { display:none; margin-top:18px; } .camera.open { display:block; }
        video { width:100%; max-height:360px; object-fit:cover; border-radius:10px; background:#09111f; }
        .capture { background:#d97706; width:100%; margin-top:10px; }
        .cancel { background:#64748b; width:100%; margin-top:8px; }
        .notice { display:none; padding:12px; border-radius:9px; margin-top:16px; font-size:13px; white-space:pre-line; }
        .notice.show { display:block; } .ok { color:#065f46; background:#d1fae5; } .error { color:#991b1b; background:#fee2e2; } .wait { color:#1e40af; background:#dbeafe; }
        .profile { display:flex; align-items:center; gap:12px; border-bottom:1px solid var(--line); padding-bottom:18px; margin-bottom:18px; }
        .avatar { width:54px; height:54px; object-fit:cover; border-radius:50%; border:2px solid #e2e8f0; }
        .profile strong { display:block; color:var(--navy); } .profile span { color:var(--muted); font-size:12px; }
        .metric { display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid var(--line); font-size:13px; }
        .metric:last-child { border-bottom:0; } .metric span { color:var(--muted); } .metric strong { color:var(--navy); }
        details { margin-top:20px; border-top:1px solid var(--line); padding-top:14px; } summary { color:var(--navy); cursor:pointer; font-size:13px; font-weight:700; }
        .device-help { color:var(--muted); font-size:11px; line-height:1.4; margin-top:8px; }
        .back { color:var(--navy); font-size:13px; text-decoration:none; font-weight:700; }
        @media (max-width:760px) { .shell{padding:14px;} .topbar{align-items:flex-start;} h1{font-size:19px;} .clock{font-size:20px;} .grid{grid-template-columns:1fr;} .card{padding:18px;} }
    </style>
</head>
<body>
<div class="shell">
    <header class="topbar">
        <div class="brand">
            <img src="{{ asset('images/logo.png') }}" alt="Ministry logo">
            <div><div class="eyebrow">Republic of Liberia</div><h1>Employee Attendance</h1></div>
        </div>
        <div><div class="clock" id="liveClock">--:--:--</div><div class="date" id="liveDate">Loading...</div></div>
    </header>

    <div class="grid">
        <main class="card">
            <div class="status"><span class="dot"></span><span id="onlineStatus">Online mode</span></div>
            <h2>Clock In / Out</h2>
            <p class="copy">Enter your employee number. Clock in requires a quick photo. Clock out does not require a photo.</p>
            <label for="employeeNumber">Employee Number</label>
            <input id="employeeNumber" value="{{ $employee->employee_no }}" autocomplete="off" maxlength="50">
            <div class="actions">
                <button class="in" id="clockIn">Clock In</button>
                <button class="out" id="clockOut">Clock Out</button>
            </div>
            <div class="camera" id="cameraBox">
                <video id="camera" autoplay playsinline muted></video>
                <button class="capture" id="capture">Take Photo &amp; Clock In</button>
                <button class="cancel" id="cancel">Cancel</button>
            </div>
            <div class="notice" id="notice" role="status"></div>
            <details>
                <summary>Registered device setup</summary>
                <p class="device-help">Use the Device ID and token issued by HR. Location permission is required when the device has a geofence.</p>
                <label for="deviceId">Device ID</label>
                <input id="deviceId" value="{{ $deviceId ?? '' }}" placeholder="ATD-XXXXXXXXXX">
                <label for="deviceToken">Device Token</label>
                <input id="deviceToken" type="password" value="{{ $deviceToken ?? '' }}" placeholder="Device token">
                <button class="secondary" id="saveDevice">Save Registered Device</button>
            </details>
        </main>

        <aside class="card">
            <div class="profile">
                <img class="avatar" src="{{ $employee->photo_url }}" alt="{{ $employee->full_name }}">
                <div><strong>{{ $employee->full_name }}</strong><span>{{ $employee->employee_no }} · {{ $employee->designation }}</span></div>
            </div>
            <h3>Today</h3>
            <div class="metric"><span>Attendance status</span><strong id="todayStatus">Checking...</strong></div>
            <div class="metric"><span>Device</span><strong id="deviceStatus">{{ $device?->name ?? 'Not configured' }}</strong></div>
            <div class="metric"><span>Location</span><strong id="locationStatus">Checking...</strong></div>
            <a class="back" href="{{ route('employee.dashboard') }}">Back to employee dashboard</a>
        </aside>
    </div>
</div>
<script>
(() => {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const employeeNumber = document.getElementById('employeeNumber');
    const deviceId = document.getElementById('deviceId');
    const deviceToken = document.getElementById('deviceToken');
    const cameraBox = document.getElementById('cameraBox');
    const camera = document.getElementById('camera');
    const notice = document.getElementById('notice');
    const locationStatus = document.getElementById('locationStatus');
    let stream = null;
    let location = null;

    const savedId = localStorage.getItem('attendance_device_id');
    const savedToken = localStorage.getItem('attendance_device_token');
    if (!deviceId.value && savedId) deviceId.value = savedId;
    if (!deviceToken.value && savedToken) deviceToken.value = savedToken;

    const show = (type, message) => { notice.className = 'notice show ' + type; notice.textContent = message; };
    const busy = (value) => document.querySelectorAll('button').forEach((button) => button.disabled = value);
    const updateClock = () => {
        const now = new Date();
        document.getElementById('liveClock').textContent = now.toLocaleTimeString([], { hour12: false });
        document.getElementById('liveDate').textContent = now.toLocaleDateString([], { weekday:'long', year:'numeric', month:'long', day:'numeric' });
    };
    updateClock(); setInterval(updateClock, 1000);
    if ('serviceWorker' in navigator) navigator.serviceWorker.register('{{ asset('sw.js') }}').catch(() => {});

    const locate = () => new Promise((resolve, reject) => {
        if (!navigator.geolocation) return reject(new Error('Location is not supported by this device.'));
        navigator.geolocation.getCurrentPosition((position) => {
            location = { latitude: position.coords.latitude, longitude: position.coords.longitude };
            locationStatus.textContent = 'Available'; resolve(location);
        }, () => reject(new Error('Location permission is required to use attendance.')), { enableHighAccuracy:true, timeout:10000, maximumAge:30000 });
    });

    const closeCamera = () => { if (stream) stream.getTracks().forEach((track) => track.stop()); stream = null; cameraBox.classList.remove('open'); };
    document.getElementById('saveDevice').onclick = () => {
        localStorage.setItem('attendance_device_id', deviceId.value.trim());
        localStorage.setItem('attendance_device_token', deviceToken.value.trim());
        show('ok', 'Registered device saved on this phone.');
    };
    document.getElementById('clockIn').onclick = async () => {
        if (!employeeNumber.value.trim()) return show('error', 'Enter your employee number first.');
        try {
            await locate();
            stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode:'user' } });
            camera.srcObject = stream; cameraBox.classList.add('open'); show('wait', 'Camera ready. Take your photo to clock in.');
        } catch (error) { show('error', error.message || 'Camera and location access are required.'); }
    };
    document.getElementById('cancel').onclick = () => { closeCamera(); notice.className = 'notice'; };
    document.getElementById('capture').onclick = async () => {
        const canvas = document.createElement('canvas'); canvas.width = camera.videoWidth; canvas.height = camera.videoHeight;
        canvas.getContext('2d').drawImage(camera, 0, 0); await submit('clock_in', canvas.toDataURL('image/jpeg', .82)); closeCamera();
    };
    document.getElementById('clockOut').onclick = async () => { try { await locate(); await submit('clock_out', null); } catch (error) { show('error', error.message); } };

    async function submit(action, photo) {
        if (!deviceId.value.trim() || !deviceToken.value.trim()) return show('error', 'Enter or save the registered Device ID and token first.');
        busy(true); show('wait', 'Submitting attendance...');
        try {
            const response = await fetch('{{ route('employee.attendance.punch') }}', { method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf}, body:JSON.stringify({ employee_identifier:employeeNumber.value.trim(), action, captured_photo:photo, device_id:deviceId.value.trim(), device_token:deviceToken.value.trim(), latitude:location.latitude, longitude:location.longitude, submission_uuid:crypto.randomUUID() }) });
            const data = await response.json();
            if (!response.ok) throw new Error(data.errors ? Object.values(data.errors)[0][0] : (data.message || 'Attendance could not be recorded.'));
            document.getElementById('todayStatus').textContent = data.attendance.status || 'Recorded'; show('ok', data.message); setTimeout(() => window.location.reload(), 1200);
        } catch (error) { show('error', error.message || 'Attendance could not be recorded.'); } finally { busy(false); }
    }
})();
</script>
</body>
</html>
