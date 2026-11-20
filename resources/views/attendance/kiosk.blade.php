<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Attendance Kiosk | Ministry of Youth and Sports</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
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
            --radius: 24px;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }
        body {
            font-family: 'Inter', sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(56,189,248,0.15), transparent 30%),
                radial-gradient(circle at 85% 10%, rgba(16,185,129,0.13), transparent 28%),
                linear-gradient(180deg, #050b16 0%, #081120 55%, #050b16 100%);
        }
        .shell { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 16px; }
        .card {
            width: 100%;
            max-width: 460px;
            border-radius: var(--radius);
            border: 1px solid var(--border);
            background: var(--panel);
            box-shadow: 0 30px 120px rgba(0,0,0,0.5);
            backdrop-filter: blur(24px);
            padding: 28px;
        }
        .title { font-size: 22px; font-weight: 800; text-align: center; }
        .subtitle { font-size: 13px; color: var(--muted); text-align: center; margin-top: 4px; margin-bottom: 22px; }
        label { font-size: 13px; font-weight: 600; color: var(--muted); display: block; margin-bottom: 6px; }
        input[type=text] {
            width: 100%; padding: 12px 14px; border-radius: 12px;
            border: 1px solid var(--border); background: rgba(255,255,255,0.04);
            color: var(--text); font-size: 16px; letter-spacing: 0.5px;
        }
        input[type=text]:focus { outline: none; border-color: var(--blue); }
        .actions { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 18px; }
        button {
            border: none; border-radius: 12px; padding: 14px; font-size: 15px; font-weight: 700;
            cursor: pointer; color: #fff; transition: transform .15s ease;
        }
        button:disabled { opacity: 0.5; cursor: not-allowed; }
        button:active:not(:disabled) { transform: scale(0.97); }
        .btn-in { background: linear-gradient(135deg,#10b981,#059669); }
        .btn-out { background: linear-gradient(135deg,#38bdf8,#2563eb); }
        .camera-wrap { margin-top: 18px; display: none; }
        .camera-wrap.active { display: block; }
        video, canvas { width: 100%; border-radius: 16px; border: 1px solid var(--border); background: #000; }
        canvas { display: none; }
        .btn-capture { margin-top: 12px; width: 100%; background: linear-gradient(135deg,#f59e0b,#d97706); }
        .btn-cancel { margin-top: 8px; width: 100%; background: rgba(255,255,255,0.08); }
        .status {
            margin-top: 18px; padding: 14px; border-radius: 14px; font-size: 14px; text-align: center;
            display: none; white-space: pre-line;
        }
        .status.show { display: block; }
        .status.ok { background: rgba(16,185,129,0.12); border: 1px solid rgba(16,185,129,0.4); color: #6ee7b7; }
        .status.err { background: rgba(248,113,113,0.12); border: 1px solid rgba(248,113,113,0.4); color: #fca5a5; }
        .status.wait { background: rgba(56,189,248,0.12); border: 1px solid rgba(56,189,248,0.4); color: #7dd3fc; }
    </style>
</head>
<body>
    <div class="shell">
        <div class="card">
            <div class="title">Attendance Kiosk</div>
            <div class="subtitle">Ministry of Youth and Sports — Enter your employee number</div>

            <label for="empNo">Employee Number</label>
            <input type="text" id="empNo" placeholder="e.g. MYS-2024-0001" autofocus autocomplete="off">

            <div class="actions">
                <button type="button" class="btn-in" id="btnClockIn">Clock In</button>
                <button type="button" class="btn-out" id="btnClockOut">Clock Out</button>
            </div>

            <div class="camera-wrap" id="cameraWrap">
                <video id="video" autoplay playsinline muted></video>
                <canvas id="canvas"></canvas>
                <button type="button" class="btn-capture" id="btnCapture">Take Photo &amp; Clock In</button>
                <button type="button" class="btn-cancel" id="btnCancel">Cancel</button>
            </div>

            <div class="status" id="status"></div>
        </div>
    </div>

    <script>
        const empNoInput = document.getElementById('empNo');
        const btnClockIn = document.getElementById('btnClockIn');
        const btnClockOut = document.getElementById('btnClockOut');
        const cameraWrap = document.getElementById('cameraWrap');
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const btnCapture = document.getElementById('btnCapture');
        const btnCancel = document.getElementById('btnCancel');
        const statusEl = document.getElementById('status');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        let stream = null;

        function showStatus(type, message) {
            statusEl.className = 'status show ' + type;
            statusEl.textContent = message;
        }

        function setBusy(busy) {
            btnClockIn.disabled = busy;
            btnClockOut.disabled = busy;
            btnCapture.disabled = busy;
        }

        async function openCamera() {
            const empNo = empNoInput.value.trim();
            if (!empNo) {
                showStatus('err', 'Please enter your employee number first.');
                return;
            }
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
                video.srcObject = stream;
                cameraWrap.classList.add('active');
                statusEl.classList.remove('show');
            } catch (e) {
                showStatus('err', 'Camera access is required to clock in. Please allow camera permission.');
            }
        }

        function closeCamera() {
            if (stream) {
                stream.getTracks().forEach(t => t.stop());
                stream = null;
            }
            cameraWrap.classList.remove('active');
        }

        async function submitPunch(action, capturedPhoto) {
            setBusy(true);
            showStatus('wait', 'Submitting…');
            try {
                const res = await fetch("{{ route('attendance.kiosk.punch') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        employee_identifier: empNoInput.value.trim(),
                        action: action,
                        captured_photo: capturedPhoto,
                    }),
                });
                const data = await res.json();

                if (!res.ok) {
                    const firstError = data.errors ? Object.values(data.errors)[0][0] : (data.message || 'Something went wrong.');
                    showStatus('err', firstError);
                } else {
                    showStatus('ok', `${data.message}\n${data.employee.name} (${data.employee.employee_no})`);
                    empNoInput.value = '';
                }
            } catch (e) {
                showStatus('err', 'Network error. Please try again.');
            } finally {
                setBusy(false);
            }
        }

        btnClockIn.addEventListener('click', openCamera);

        btnClockOut.addEventListener('click', () => {
            const empNo = empNoInput.value.trim();
            if (!empNo) {
                showStatus('err', 'Please enter your employee number first.');
                return;
            }
            submitPunch('clock_out', null);
        });

        btnCancel.addEventListener('click', () => {
            closeCamera();
            statusEl.classList.remove('show');
        });

        btnCapture.addEventListener('click', async () => {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            const photo = canvas.toDataURL('image/jpeg', 0.85);
            closeCamera();
            await submitPunch('clock_in', photo);
        });
    </script>
</body>
</html>
