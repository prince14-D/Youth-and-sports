@extends('layouts.app')

@section('title', 'Attendance Enrollments')
@section('page-title', 'Attendance Enrollments')
@section('breadcrumb', 'HRMIS › Attendance › Enrollments')

@section('content')
<style>
    /* ── Camera Modal Z-Index Fix ── */
    #enrollmentWebcamModal {
        z-index: 1085 !important;
    }
    .modal-backdrop.show {
        z-index: 1075 !important;
        opacity: 0.5 !important;
    }
    /* Ensure nothing blocks pointer events inside modal */
    #enrollmentWebcamModal .modal-dialog,
    #enrollmentWebcamModal .modal-content,
    #enrollmentWebcamModal .modal-body,
    #enrollmentWebcamModal button {
        pointer-events: auto !important;
    }
</style>

<div class="animate-in">
    <x-ui.page-header
        title="Attendance Enrollments"
        subtitle="Enroll already-registered employees so they can be used by the attendance terminal."
    />

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-4">{{ session('error') }}</div>
    @endif

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4">{{ session('success') }}</div>
    @endif

    @if(session('generated_pin') || session('generated_qr_token'))
        <div class="alert alert-warning border-0 shadow-sm mb-4">
            <div class="fw-semibold mb-2">Enrollment credentials generated</div>
            @if(session('generated_pin'))
                <div><strong>PIN:</strong> <code>{{ session('generated_pin') }}</code></div>
            @endif
            @if(session('generated_qr_token'))
                <div><strong>QR Token:</strong> <code>{{ session('generated_qr_token') }}</code></div>
            @endif
        </div>
    @endif

    <div class="row g-4">
        {{-- ── LEFT: Enroll Form ── --}}
        <div class="col-12 col-lg-5">
            <x-ui.card title="Enroll Employee" icon="bi bi-person-badge" class="h-100">

                    {{-- Duplicate Enrollment Warning --}}
                    <div id="duplicateEnrollmentAlert" class="alert alert-danger d-none border-danger p-3 mb-3" style="background-color:#fef2f2;">
                        <div class="d-flex gap-2 align-items-start">
                            <i class="bi bi-shield-exclamation fs-4 text-danger flex-shrink-0"></i>
                            <div class="flex-grow-1">
                                <div class="fw-bold text-danger fs-6 mb-1">DUPLICATE ENROLLMENT WARNING</div>
                                <div class="small" id="duplicateAlertText">This employee is already enrolled. Admin cannot register one person twice.</div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" value="1" id="forceReenrollCheck" onchange="toggleForceReenroll(this)">
                                    <label class="form-check-label small fw-semibold" for="forceReenrollCheck">
                                        Force re-enroll — recapture this employee's face profile
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('attendance.enrollments.store') }}" enctype="multipart/form-data" class="vstack gap-3" id="enrollmentForm">
                        @csrf
                        <input type="hidden" name="face_photo_base64" id="facePhotoBase64">
                        <input type="hidden" name="face_descriptor_json" id="faceDescriptorJson">
                        <input type="hidden" name="force_update" id="forceUpdateInput" value="">

                        {{-- Employee Select --}}
                        <div>
                            <label class="form-label fw-bold small">Employee <span class="text-danger">*</span></label>
                            <select name="employee_id" id="employeeSelect" class="form-select" required onchange="checkEnrollmentDuplicate(this)">
                                <option value="">Select employee…</option>
                                @foreach($employees as $employee)
                                    @php $isEnrolled = in_array($employee->id, $enrolledIds ?? []); @endphp
                                    <option
                                        value="{{ $employee->id }}"
                                        data-enrolled="{{ $isEnrolled ? '1' : '0' }}"
                                        data-name="{{ $employee->full_name }}"
                                        data-no="{{ $employee->employee_no }}"
                                        style="{{ $isEnrolled ? 'color:#dc2626;font-weight:600;' : '' }}"
                                    >
                                        {{ $employee->full_name }} ({{ $employee->employee_no }}){{ $isEnrolled ? ' — [ALREADY ENROLLED]' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Face Photo --}}
                        <div>
                            <label class="form-label fw-bold small">Face Photo <span class="text-muted fw-normal">(live camera capture recommended)</span></label>

                            {{-- Preview after capture --}}
                            <div id="cameraPreviewBox" class="d-none mb-2 text-center p-2 bg-light rounded border position-relative" style="max-width:140px;">
                                <img id="cameraPreviewImg" src="" class="rounded border img-fluid" style="height:110px;object-fit:cover;">
                                <button type="button" class="btn btn-danger btn-sm rounded-circle position-absolute top-0 end-0 p-1 m-1" onclick="clearCameraSnapshot()" title="Remove">
                                    <i class="bi bi-x-circle-fill"></i>
                                </button>
                            </div>

                            <div id="descriptorWarning" class="alert alert-warning d-none py-2 px-3 small mb-2">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Face profile could not be captured — this employee will need the ID keypad at the terminal until re-enrolled with a clear frontal photo.
                            </div>

                            <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                                <button type="button" class="btn btn-navy btn-sm rounded-pill px-3" onclick="openEnrollmentWebcam()">
                                    <i class="bi bi-camera text-accent me-1"></i> Take Photo via Camera
                                </button>
                                <span class="text-muted small">or upload file:</span>
                            </div>
                            <input type="file" name="face_photo" id="faceFileInput" class="form-control form-control-sm" accept="image/*">
                        </div>

                        {{-- Notes --}}
                        <div>
                            <label class="form-label fw-bold small">Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Biometric enrollment notes"></textarea>
                        </div>

                        <button class="btn btn-navy w-100 fw-semibold" type="submit" id="enrollSubmitBtn">
                            <i class="bi bi-check-circle me-1"></i> Enroll Employee
                        </button>
                    </form>

            </x-ui.card>
        </div>

        {{-- ── RIGHT: Enrollments Table ── --}}
        <div class="col-12 col-lg-7">
            <x-ui.card title="Current Enrollments" icon="bi bi-people" class="h-100" bodyClass="p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th class="ps-4">Employee</th>
                                    <th>Methods</th>
                                    <th>Status</th>
                                    <th>Enrolled At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($enrollments as $enrollment)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-semibold">{{ $enrollment->employee?->full_name }}</div>
                                            <div class="text-muted small">{{ $enrollment->employee?->employee_no }} · {{ $enrollment->employee?->department?->name }}</div>
                                        </td>
                                        <td class="small">{{ implode(', ', $enrollment->allowed_methods ?? []) }}</td>
                                        <td>
                                            <x-ui.badge :variant="$enrollment->status === 'active' ? 'success' : 'default'">{{ ucfirst($enrollment->status) }}</x-ui.badge>
                                        </td>
                                        <td class="text-muted">{{ $enrollment->enrolled_at?->diffForHumans() ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4">
                                            <x-ui.empty-state
                                                title="No enrollments yet"
                                                message="No attendance enrollments yet."
                                                icon="bi bi-person-badge"
                                            />
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     WEBCAM MODAL — placed at blade @section root so JS can move it to <body>
     ══════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="enrollmentWebcamModal" tabindex="-1" aria-hidden="true" aria-labelledby="webcamModalLabel">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius:20px;overflow:hidden;background:#fff;">

            {{-- Header --}}
            <div class="modal-header text-white py-2 px-3" style="background:linear-gradient(135deg,#064e3b,#022c22);">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:28px;height:28px;background:rgba(255,255,255,.2);">
                        <i class="bi bi-camera-video-fill text-warning" style="font-size:13px;"></i>
                    </div>
                    <h6 class="modal-title fw-bold text-white mb-0" id="webcamModalLabel" style="font-size:14px;">HD Biometric Face Photo Capture</h6>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" onclick="stopEnrollmentWebcam()"></button>
            </div>

            {{-- Body --}}
            <div class="modal-body text-center p-3" style="background:#f8fafc;">
                {{-- Video Container --}}
                <div class="position-relative overflow-hidden bg-black rounded-4 shadow mx-auto mb-2" style="max-width:400px;width:100%;height:300px;">
                    <video id="enrollmentWebcamVideo" autoplay playsinline muted style="width:100%;height:100%;object-fit:cover;transform:scaleX(-1);"></video>
                    <canvas id="enrollmentWebcamCanvas" width="1280" height="960" class="d-none"></canvas>

                    {{-- Face Status Badge --}}
                    <div id="faceDetectStatusBadge"
                         class="position-absolute top-0 start-50 translate-middle-x mt-2 px-3 py-1 rounded-pill bg-warning text-dark fw-bold shadow d-flex align-items-center gap-2"
                         style="font-size:11.5px;z-index:10;">
                        <span class="spinner-grow spinner-grow-sm" role="status" style="width:10px;height:10px;"></span>
                        <i class="bi bi-person-bounding-box"></i>
                        <span>CENTER FACE IN FRAME</span>
                    </div>

                    {{-- Face Reticle --}}
                    <div id="faceTargetBox"
                         class="position-absolute top-50 start-50 translate-middle border border-3 rounded-circle"
                         style="width:190px;height:190px;border-color:#f59e0b;border-style:solid !important;pointer-events:none;margin-top:-15px;transition:border-color .3s,box-shadow .3s;">
                        <div class="position-absolute top-0 start-0 border-top border-start border-3 border-warning" style="width:16px;height:16px;margin:-2px;"></div>
                        <div class="position-absolute top-0 end-0 border-top border-end border-3 border-warning" style="width:16px;height:16px;margin:-2px;"></div>
                        <div class="position-absolute bottom-0 start-0 border-bottom border-start border-3 border-warning" style="width:16px;height:16px;margin:-2px;"></div>
                        <div class="position-absolute bottom-0 end-0 border-bottom border-end border-3 border-warning" style="width:16px;height:16px;margin:-2px;"></div>
                    </div>

                    {{-- Floating Capture Button --}}
                    <button type="button"
                            class="position-absolute bottom-0 start-50 translate-middle-x mb-3 btn rounded-pill px-4 py-2 fw-bold shadow d-inline-flex align-items-center gap-2"
                            style="z-index:20;background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;font-size:13.5px;border:2px solid #fff;pointer-events:auto;"
                            onclick="captureEnrollmentPhoto()">
                        <i class="bi bi-camera-fill fs-6"></i>
                        <span>CAPTURE &amp; SAVE PHOTO</span>
                    </button>
                </div>

                <div id="webcamErrorMsg" class="alert alert-danger d-none py-2 px-3 small mb-2">
                    Unable to access camera. Please check browser permissions.
                </div>
                <p class="small mb-0 text-muted" id="faceStatusText">
                    <i class="bi bi-info-circle me-1"></i> Position your face inside the circle, then click <strong>CAPTURE &amp; SAVE PHOTO</strong>.
                </p>
            </div>

            {{-- Footer --}}
            <div class="modal-footer bg-white border-top py-2 px-3 justify-content-between">
                <button type="button" class="btn btn-light border rounded-pill px-3 py-1 btn-sm fw-semibold text-secondary" data-bs-dismiss="modal" onclick="stopEnrollmentWebcam()">
                    Cancel
                </button>
                <button type="button" class="btn btn-navy rounded-pill px-4 py-1 btn-sm fw-bold d-inline-flex align-items-center gap-1" onclick="captureEnrollmentPhoto()">
                    <i class="bi bi-camera-fill text-warning"></i>
                    <span>Capture &amp; Save</span>
                </button>
            </div>

        </div>
    </div>
</div>

<script src="{{ asset('js/faceapi/face-api.min.js') }}"></script>
<script>
(function () {
    // ── State ─────────────────────────────────────────────────────────────
    let enrollmentStream    = null;
    let faceDetectInterval  = null;
    const WEIGHTS_URL       = '{{ asset("js/faceapi/weights") }}';
    let faceApiModelsReady  = false;
    let faceApiLoadPromise  = null;

    // ── Duplicate Check ───────────────────────────────────────────────────
    window.checkEnrollmentDuplicate = function (select) {
        const opt        = select.options[select.selectedIndex];
        const alertBox   = document.getElementById('duplicateEnrollmentAlert');
        const alertText  = document.getElementById('duplicateAlertText');
        const submitBtn  = document.getElementById('enrollSubmitBtn');
        const forceCheck = document.getElementById('forceReenrollCheck');
        const forceInput = document.getElementById('forceUpdateInput');

        // Reset the force-reenroll toggle whenever the selected employee changes
        forceCheck.checked = false;
        forceInput.value   = '';

        if (opt && opt.getAttribute('data-enrolled') === '1') {
            const name = opt.getAttribute('data-name');
            const no   = opt.getAttribute('data-no');
            alertText.innerText = `${name} (${no}) is already enrolled. Duplicate registration is blocked unless you choose to re-enroll below.`;
            alertBox.classList.remove('d-none');
            submitBtn.setAttribute('disabled', 'disabled');
            submitBtn.classList.add('disabled');
        } else {
            alertBox.classList.add('d-none');
            submitBtn.removeAttribute('disabled');
            submitBtn.classList.remove('disabled');
        }
    };

    // ── Force Re-enroll Toggle ────────────────────────────────────────────
    window.toggleForceReenroll = function (checkbox) {
        const submitBtn  = document.getElementById('enrollSubmitBtn');
        const forceInput = document.getElementById('forceUpdateInput');

        if (checkbox.checked) {
            forceInput.value = '1';
            submitBtn.removeAttribute('disabled');
            submitBtn.classList.remove('disabled');
        } else {
            forceInput.value = '';
            submitBtn.setAttribute('disabled', 'disabled');
            submitBtn.classList.add('disabled');
        }
    };

    // ── Lazy-load face-api.js recognition models ─────────────────────────
    // Loaded once, on first webcam open, alongside (not instead of) the
    // native FaceDetector UI badge below — recognition needs a real face
    // descriptor (128-d embedding), which FaceDetector cannot produce.
    async function ensureFaceApiModels() {
        if (faceApiModelsReady) return true;
        if (!faceApiLoadPromise) {
            faceApiLoadPromise = (async () => {
                try {
                    await Promise.all([
                        faceapi.nets.tinyFaceDetector.loadFromUri(WEIGHTS_URL),
                        faceapi.nets.faceLandmark68Net.loadFromUri(WEIGHTS_URL),
                        faceapi.nets.faceRecognitionNet.loadFromUri(WEIGHTS_URL),
                    ]);
                    faceApiModelsReady = true;
                } catch (e) {
                    console.warn('Face descriptor models unavailable — enrollment will fall back to photo-only:', e);
                    faceApiModelsReady = false;
                }
            })();
        }
        await faceApiLoadPromise;
        return faceApiModelsReady;
    }

    // ── Open Webcam Modal ─────────────────────────────────────────────────
    window.openEnrollmentWebcam = function () {
        // Move modal to <body> so backdrop never sits above it
        const modalEl = document.getElementById('enrollmentWebcamModal');
        if (modalEl && modalEl.parentNode !== document.body) {
            document.body.appendChild(modalEl);
        }

        // Reset badge to "waiting"
        const badge = document.getElementById('faceDetectStatusBadge');
        if (badge) {
            badge.className = 'position-absolute top-0 start-50 translate-middle-x mt-2 px-3 py-1 rounded-pill bg-warning text-dark fw-bold shadow d-flex align-items-center gap-2';
            badge.style.fontSize = '11.5px';
            badge.style.zIndex   = '10';
            badge.innerHTML = '<span class="spinner-grow spinner-grow-sm" role="status" style="width:10px;height:10px;"></span><i class="bi bi-camera-video me-1"></i><span>Starting camera…</span>';
        }
        const box = document.getElementById('faceTargetBox');
        if (box) { box.style.borderColor = '#f59e0b'; box.style.boxShadow = 'none'; }

        const modal = new bootstrap.Modal(modalEl, { backdrop: true, keyboard: true });
        modal.show();

        // Kick off model loading in the background so it's ready by capture time
        ensureFaceApiModels();

        const video  = document.getElementById('enrollmentWebcamVideo');
        const errDiv = document.getElementById('webcamErrorMsg');
        errDiv.classList.add('d-none');

        navigator.mediaDevices.getUserMedia({
            video: { width: { ideal: 1280 }, height: { ideal: 720 }, facingMode: 'user' },
            audio: false
        })
        .then(function (stream) {
            enrollmentStream = stream;
            video.srcObject  = stream;
            video.onloadedmetadata = function () { startFaceDetectionLoop(video); };
        })
        .catch(function (err) {
            console.error('Camera error:', err);
            errDiv.classList.remove('d-none');
        });
    };

    // ── Face Detection Loop ───────────────────────────────────────────────
    function startFaceDetectionLoop(video) {
        const badge      = document.getElementById('faceDetectStatusBadge');
        const box        = document.getElementById('faceTargetBox');
        const statusText = document.getElementById('faceStatusText');

        if ('FaceDetector' in window) {
            try {
                const fd = new FaceDetector({ maxDetectedFaces: 1, fastMode: true });
                faceDetectInterval = setInterval(async function () {
                    if (!video.paused && !video.ended && video.readyState === 4) {
                        try {
                            const faces = await fd.detect(video);
                            if (faces && faces.length > 0) {
                                badge.className = 'position-absolute top-0 start-50 translate-middle-x mt-2 px-3 py-1 rounded-pill bg-success text-white fw-bold shadow d-flex align-items-center gap-2';
                                badge.style.fontSize = '11.5px'; badge.style.zIndex = '10';
                                badge.innerHTML = '<i class="bi bi-check-circle-fill text-warning"></i><span>✓ FACE DETECTED &amp; LOCKED</span>';
                                box.style.borderColor = '#10b981';
                                box.style.boxShadow   = '0 0 25px rgba(16,185,129,.8)';
                                if (statusText) statusText.innerHTML = '<i class="bi bi-check-circle-fill me-1 text-success"></i><strong>Face locked!</strong> Click CAPTURE &amp; SAVE PHOTO.';
                            } else {
                                badge.className = 'position-absolute top-0 start-50 translate-middle-x mt-2 px-3 py-1 rounded-pill bg-warning text-dark fw-bold shadow d-flex align-items-center gap-2';
                                badge.style.fontSize = '11.5px'; badge.style.zIndex = '10';
                                badge.innerHTML = '<i class="bi bi-person-bounding-box"></i><span>CENTER FACE IN FRAME</span>';
                                box.style.borderColor = '#f59e0b';
                                box.style.boxShadow   = 'none';
                                if (statusText) statusText.innerHTML = '<i class="bi bi-exclamation-circle me-1 text-warning"></i>Position your face directly inside the circle.';
                            }
                        } catch (e) { /* ignore per-frame errors */ }
                    }
                }, 300);
                return;
            } catch (e) { /* FaceDetector not available */ }
        }

        // Fallback: camera is live, no native face detection
        badge.className = 'position-absolute top-0 start-50 translate-middle-x mt-2 px-3 py-1 rounded-pill bg-success text-white fw-bold shadow d-flex align-items-center gap-2';
        badge.style.fontSize = '11.5px'; badge.style.zIndex = '10';
        badge.innerHTML = '<i class="bi bi-check-circle-fill text-warning"></i><span>LIVE CAMERA ACTIVE</span>';
        box.style.borderColor = '#10b981';
        box.style.boxShadow   = '0 0 20px rgba(16,185,129,.7)';
    }

    // ── Stop Webcam ───────────────────────────────────────────────────────
    window.stopEnrollmentWebcam = function () {
        if (faceDetectInterval) { clearInterval(faceDetectInterval); faceDetectInterval = null; }
        if (enrollmentStream)   { enrollmentStream.getTracks().forEach(function (t) { t.stop(); }); enrollmentStream = null; }
    };

    // ── Capture Photo ─────────────────────────────────────────────────────
    window.captureEnrollmentPhoto = async function () {
        const video   = document.getElementById('enrollmentWebcamVideo');
        const canvas  = document.getElementById('enrollmentWebcamCanvas');
        const ctx     = canvas.getContext('2d');

        canvas.width  = video.videoWidth  || 1280;
        canvas.height = video.videoHeight || 720;

        // Mirror the frame to match mirrored preview
        ctx.translate(canvas.width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        const dataUrl = canvas.toDataURL('image/jpeg', 0.92);

        document.getElementById('facePhotoBase64').value = dataUrl;
        document.getElementById('cameraPreviewImg').src  = dataUrl;
        document.getElementById('cameraPreviewBox').classList.remove('d-none');

        const descriptorInput = document.getElementById('faceDescriptorJson');
        const warningBox      = document.getElementById('descriptorWarning');
        descriptorInput.value = '';
        warningBox.classList.add('d-none');

        // Extract a real face descriptor (128-d embedding) for recognition
        // matching — averaged over a few rapid frames for stability. Runs
        // against the live (unmirrored) video element, not the canvas.
        if (await ensureFaceApiModels()) {
            try {
                const samples = [];
                const opts = new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 });
                for (let i = 0; i < 3; i++) {
                    const result = await faceapi.detectSingleFace(video, opts)
                        .withFaceLandmarks()
                        .withFaceDescriptor();
                    if (result && result.descriptor) samples.push(Array.from(result.descriptor));
                    await new Promise(function (r) { setTimeout(r, 150); });
                }

                if (samples.length) {
                    const dims = samples[0].length;
                    const avg  = new Array(dims).fill(0);
                    samples.forEach(function (s) { s.forEach(function (v, i) { avg[i] += v; }); });
                    for (let i = 0; i < dims; i++) avg[i] /= samples.length;
                    descriptorInput.value = JSON.stringify(avg);
                } else {
                    warningBox.classList.remove('d-none');
                }
            } catch (e) {
                console.warn('Face descriptor extraction failed:', e);
                warningBox.classList.remove('d-none');
            }
        } else {
            warningBox.classList.remove('d-none');
        }

        stopEnrollmentWebcam();
        const modalEl = document.getElementById('enrollmentWebcamModal');
        const modal   = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
    };

    // ── Clear Snapshot ────────────────────────────────────────────────────
    window.clearCameraSnapshot = function () {
        document.getElementById('facePhotoBase64').value = '';
        document.getElementById('faceDescriptorJson').value = '';
        document.getElementById('cameraPreviewImg').src  = '';
        document.getElementById('cameraPreviewBox').classList.add('d-none');
        document.getElementById('descriptorWarning').classList.add('d-none');
    };
})();
</script>

@endsection
