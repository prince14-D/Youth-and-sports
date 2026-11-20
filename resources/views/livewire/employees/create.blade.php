<div class="animate-in">
    <!-- Page Header -->
    <x-ui.page-header 
        title="Personnel Registration Wizard" 
        subtitle="Register new ministry employees or import official records" 
        breadcrumb="HRMS › Employees › Registration">
        <x-slot:actions>
            <a href="{{ route('employees.index') }}" class="btn btn-white border shadow-xs rounded-pill px-3.5 py-2 text-navy font-weight-600 d-inline-flex align-items-center gap-2 hover-shadow transition-all" style="font-size: 13px; background: #ffffff; border-color: #CBD5E1 !important;">
                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">
                    <i class="bi bi-arrow-left text-primary" style="font-size: 13px;"></i>
                </div>
                <span>Back to Registry</span>
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <!-- Wizard Progress Bar Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <h5 class="fw-bold text-navy mb-0">Step {{ $step }} of {{ $totalSteps }}: 
                        @if($step == 1) Personal Information
                        @elseif($step == 2) Employment & Official Identifiers
                        @elseif($step == 3) Financials & Compensation
                        @else Academic History & Attachments
                        @endif
                    </h5>
                    <small class="text-muted" style="font-size:11.5px;">Complete all required fields marked with an asterisk (*)</small>
                </div>
                <span class="badge bg-navy px-3 py-2" style="font-size: 12.5px;">{{ round(($step / $totalSteps) * 100) }}% Completed</span>
            </div>
            
            <div class="progress mt-3" style="height: 8px; background-color: #e2e8f0; border-radius: 999px;">
                <div class="progress-bar bg-navy transition-all" role="progressbar" style="width: {{ ($step / $totalSteps) * 100 }}%"></div>
            </div>
        </div>
    </div>

    <!-- Form Body Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form wire:submit.prevent="save">
                
                <!-- STEP 1: Personal Details -->
                @if($step == 1)
                <div>
                    <h5 class="fw-bold text-navy border-bottom pb-3 mb-4 d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-person-vcard-fill me-2 text-primary"></i>Step 1: Personal Information &amp; Attendance Photo Verification</span>
                    </h5>

                    <!-- DUPLICATE REGISTRATION WARNING BANNER -->
                    @if($duplicateWarning)
                    <div class="alert alert-danger d-flex align-items-start gap-3 p-3 mb-4 rounded-3 border-danger shadow-sm animate-in" style="background-color: #fef2f2; border: 1.5px solid #ef4444;">
                        <i class="bi bi-shield-exclamation fs-3 flex-shrink-0 text-danger mt-1"></i>
                        <div>
                            <div class="fw-bold text-danger fs-6 mb-1"><i class="bi bi-slash-circle me-1"></i>DUPLICATE REGISTRATION BLOCKED</div>
                            <div class="text-navy fw-semibold" style="font-size: 13px;">{{ $duplicateWarning }}</div>
                            <div class="small text-danger mt-1 font-weight-500">System policy strictly prohibits enrolling the same employee multiple times. Search existing records to view or update this profile.</div>
                        </div>
                    </div>
                    @endif

                    <!-- EMPLOYEE PHOTO & ATTENDANCE BIOMETRIC ENROLLMENT BOX -->
                    <div class="card border mb-4 shadow-2xs" style="border-radius: 14px; background: #f8fafc;">
                        <div class="card-body p-3.5">
                            <div class="row align-items-center g-3">
                                <div class="col-12 col-md-auto text-center">
                                    <div class="position-relative d-inline-block">
                                        @if($photo)
                                            <img src="{{ $photo->temporaryUrl() }}" class="rounded-circle border shadow-sm" style="width: 90px; height: 90px; object-fit: cover;">
                                        @elseif($photo_base64)
                                            <img src="{{ $photo_base64 }}" class="rounded-circle border shadow-sm" style="width: 90px; height: 90px; object-fit: cover;">
                                        @else
                                            <img src="{{ asset('images/default-avatar.png') }}" class="rounded-circle border shadow-sm" style="width: 90px; height: 90px; object-fit: cover;">
                                        @endif
                                        <span class="position-absolute bottom-0 end-0 p-1.5 bg-success border border-white rounded-circle" title="Verification Ready"></span>
                                    </div>
                                </div>
                                <div class="col-12 col-md">
                                    <h6 class="fw-bold text-navy mb-1"><i class="bi bi-camera-fill me-1.5 text-primary"></i>Official Verification Photo (For Attendance Tracking &amp; ID Badges)</h6>
                                    <p class="text-muted small mb-3" style="font-size: 12px;">Take a live photo using your webcam/camera or upload a image file for biometric attendance verification and official personnel ID cards.</p>
                                    
                                    <div class="d-flex flex-wrap gap-2 align-items-center">
                                        <!-- Live Camera Snapshot Button -->
                                        <button type="button" class="btn btn-navy btn-sm rounded-pill px-3 py-1.5 font-weight-600 d-inline-flex align-items-center gap-1.5" onclick="openEnrollmentWebcam()">
                                            <i class="bi bi-camera text-accent"></i>
                                            <span>Take Photo via Camera</span>
                                        </button>
                                        
                                        <!-- File Upload Button -->
                                        <label class="btn btn-white border border-slate shadow-2xs btn-sm rounded-pill px-3 py-1.5 font-weight-600 text-navy mb-0 cursor-pointer d-inline-flex align-items-center gap-1.5">
                                            <i class="bi bi-upload text-primary"></i>
                                            <span>Upload Image File</span>
                                            <input type="file" wire:model="photo" class="d-none" accept="image/*">
                                        </label>

                                        @if($photo || $photo_base64)
                                        <button type="button" wire:click="$set('photo_base64', '')" class="btn btn-outline-danger btn-sm rounded-pill px-2.5 py-1" title="Clear Photo">
                                            <i class="bi bi-x-circle me-1"></i>Clear Photo
                                        </button>
                                        @endif
                                    </div>

                                    @error('photo') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- WEBCAM MODAL FOR LIVE ENROLLMENT PHOTO -->
                    <div class="modal fade" id="enrollmentWebcamModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                                <div class="modal-header bg-navy text-white py-3">
                                    <h6 class="modal-title fw-bold text-white"><i class="bi bi-camera-video-fill me-2 text-accent"></i>Live Employee Camera Photo Capture</h6>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" onclick="stopEnrollmentWebcam()"></button>
                                </div>
                                <div class="modal-body text-center p-4">
                                    <div class="position-relative overflow-hidden bg-black rounded-3 mx-auto mb-3" style="width: 320px; height: 240px;">
                                        <video id="enrollmentWebcamVideo" width="320" height="240" autoplay playsinline style="object-fit: cover;"></video>
                                        <canvas id="enrollmentWebcamCanvas" width="320" height="240" class="d-none"></canvas>
                                    </div>
                                    <div id="webcamErrorMsg" class="text-danger small mb-2 d-none">Unable to access camera. Please check browser permissions or upload an image file.</div>
                                    <p class="text-muted small mb-0">Align employee face inside frame and click <strong>Capture Photo</strong></p>
                                </div>
                                <div class="modal-footer bg-light border-top py-2.5">
                                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal" onclick="stopEnrollmentWebcam()">Cancel</button>
                                    <button type="button" class="btn btn-accent btn-sm rounded-pill px-4 font-weight-600" onclick="captureEnrollmentPhoto()">
                                        <i class="bi bi-camera-fill me-1.5"></i>Capture &amp; Use Photo
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        let enrollmentStream = null;
                        function openEnrollmentWebcam() {
                            const modal = new bootstrap.Modal(document.getElementById('enrollmentWebcamModal'));
                            modal.show();
                            const video = document.getElementById('enrollmentWebcamVideo');
                            const errDiv = document.getElementById('webcamErrorMsg');
                            errDiv.classList.add('d-none');
                            navigator.mediaDevices.getUserMedia({ video: true, audio: false })
                                .then(stream => {
                                    enrollmentStream = stream;
                                    video.srcObject = stream;
                                })
                                .catch(err => {
                                    console.error("Camera access error:", err);
                                    errDiv.classList.remove('d-none');
                                });
                        }
                        function stopEnrollmentWebcam() {
                            if (enrollmentStream) {
                                enrollmentStream.getTracks().forEach(track => track.stop());
                                enrollmentStream = null;
                            }
                        }
                        function captureEnrollmentPhoto() {
                            const video = document.getElementById('enrollmentWebcamVideo');
                            const canvas = document.getElementById('enrollmentWebcamCanvas');
                            const context = canvas.getContext('2d');
                            context.drawImage(video, 0, 0, 320, 240);
                            const dataUrl = canvas.toDataURL('image/jpeg');
                            @this.set('photo_base64', dataUrl);
                            stopEnrollmentWebcam();
                            const modalEl = document.getElementById('enrollmentWebcamModal');
                            const modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) modal.hide();
                        }
                    </script>
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold small">First Name <span class="text-danger">*</span></label>
                            <input type="text" wire:model.blur="first_name" class="form-control @error('first_name') is-invalid @enderror" placeholder="e.g. Aaron">
                            @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold small">Middle Name</label>
                            <input type="text" wire:model.blur="middle_name" class="form-control" placeholder="e.g. K.">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold small">Last Name <span class="text-danger">*</span></label>
                            <input type="text" wire:model.blur="last_name" class="form-control @error('last_name') is-invalid @enderror" placeholder="e.g. Jones">
                            @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-sm-6 col-md-3">
                            <label class="form-label fw-bold small">Gender <span class="text-danger">*</span></label>
                            <select wire:model.blur="gender" class="form-select @error('gender') is-invalid @enderror">
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                            @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <label class="form-label fw-bold small">Date of Birth <span class="text-danger">*</span></label>
                            <input type="date" wire:model.blur="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror">
                            @error('date_of_birth') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <label class="form-label fw-bold small">Nationality</label>
                            <input type="text" wire:model.blur="nationality" class="form-control" placeholder="e.g. Liberian">
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <label class="form-label fw-bold small">County of Birth</label>
                            <input type="text" wire:model.blur="county_of_birth" class="form-control" placeholder="e.g. Montserrado">
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold small">Official Email <span class="text-danger">*</span></label>
                            <input type="email" wire:model.blur="official_email" class="form-control @error('official_email') is-invalid @enderror" placeholder="aaron.jones@mys.gov.lr">
                            @error('official_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold small">Personal Email</label>
                            <input type="email" wire:model.blur="personal_email" class="form-control" placeholder="aaron@gmail.com">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold small">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" wire:model.blur="phone" class="form-control @error('phone') is-invalid @enderror" placeholder="+231 88 123 4567">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold small">Residential Address</label>
                            <textarea wire:model.blur="address" rows="2" class="form-control" placeholder="Enter physical home address..."></textarea>
                        </div>
                    </div>
                </div>
                @endif

                <!-- STEP 2: Employment Information -->
                @if($step == 2)
                <div>
                    <h5 class="fw-bold text-navy border-bottom pb-3 mb-4"><i class="bi bi-briefcase-fill me-2 text-primary"></i>Step 2: Employment & Official Identifiers</h5>
                    
                    <div class="p-3 mb-4 rounded-3" style="background-color: #fee2e2; border: 1px solid #fca5a5;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-shield-lock-fill fs-5" style="color: #dc2626;"></i>
                            <div style="color: #dc2626;" class="fw-bold small">
                                Official Employee ID Directives: If the employee already has an official Ministry ID, enter it below. Once saved, it becomes their permanent official record identifier.
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small">Registration Status Type</label>
                            <select wire:model.live="is_existing_employee" class="form-select">
                                <option value="1">Existing Employee (Enter Official ID)</option>
                                <option value="0">New Hire (Auto-generate ID)</option>
                            </select>
                        </div>
                        @if($is_existing_employee)
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small" style="color: #dc2626 !important;">Official Employee ID <span class="text-danger">*</span></label>
                            <input type="text" wire:model.blur="employee_no" class="form-control fw-bold font-monospace @error('employee_no') is-invalid @enderror" placeholder="e.g. MYS-2022-0412" style="color: #dc2626 !important;">
                            @error('employee_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        @endif
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small">Personnel Number (Optional Internal Reference)</label>
                            <input type="text" wire:model.blur="personnel_number" class="form-control font-monospace" placeholder="e.g. PER-8721">
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small">Department <span class="text-danger">*</span></label>
                            <select wire:model.blur="department_id" class="form-select @error('department_id') is-invalid @enderror">
                                <option value="">Select Department</option>
                                @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                            @error('department_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small" style="color: #dc2626 !important;">Position / Job Title <span class="text-danger">*</span></label>
                            <input type="text" wire:model.blur="designation" class="form-control fw-bold @error('designation') is-invalid @enderror" placeholder="e.g. Director, IT Specialist, Officer" style="color: #dc2626 !important;">
                            @error('designation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-sm-6 col-md-4">
                            <label class="form-label fw-bold small">Employment Type</label>
                            <select wire:model.blur="employment_type" class="form-select">
                                <option value="Permanent">Permanent</option>
                                <option value="Contract">Contract</option>
                                <option value="Probationary">Probationary</option>
                                <option value="Temporary">Temporary</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <label class="form-label fw-bold small">Employment Status</label>
                            <select wire:model.blur="employment_status" class="form-select">
                                <option value="Active">Active</option>
                                <option value="On Leave">On Leave</option>
                                <option value="Suspended">Suspended</option>
                                <option value="Probationary">Probationary</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <label class="form-label fw-bold small">Date Joined <span class="text-danger">*</span></label>
                            <input type="date" wire:model.blur="date_joined" class="form-control @error('date_joined') is-invalid @enderror">
                            @error('date_joined') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small">Reporting Supervisor</label>
                            <select wire:model.blur="supervisor_id" class="form-select">
                                <option value="">Select Reporting Supervisor</option>
                                @foreach($supervisors as $sup)
                                <option value="{{ $sup->id }}">{{ $sup->full_name }} ({{ $sup->designation }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                @endif

                <!-- STEP 3: Financials -->
                @if($step == 3)
                <div>
                    <h5 class="fw-bold text-navy border-bottom pb-3 mb-4"><i class="bi bi-cash-coin me-2 text-primary"></i>Step 3: Financials & Payroll Account</h5>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small">Basic Salary (LRD)</label>
                            <div class="input-group">
                                <span class="input-group-text">LRD $</span>
                                <input type="number" step="0.01" wire:model.blur="salary_amount_lrd" class="form-control" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small">Basic Salary (USD)</label>
                            <div class="input-group">
                                <span class="input-group-text">USD $</span>
                                <input type="number" step="0.01" wire:model.blur="salary_amount_usd" class="form-control" placeholder="0.00">
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small">Bank Name</label>
                            <input type="text" wire:model.blur="bank_name" class="form-control" placeholder="e.g. Ecobank Liberia">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small">Bank Account Number</label>
                            <input type="text" wire:model.blur="bank_account" class="form-control font-monospace" placeholder="e.g. 100293849201">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small">TIN Number</label>
                            <input type="text" wire:model.blur="tin_number" class="form-control font-monospace" placeholder="e.g. 500294821">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small">Social Security (NASSCORP No.)</label>
                            <input type="text" wire:model.blur="social_security_no" class="form-control font-monospace" placeholder="e.g. SSN-882910">
                        </div>
                    </div>
                </div>
                @endif

                <!-- STEP 4: Academic History & Attachments -->
                @if($step == 4)
                <div>
                    <h5 class="fw-bold text-navy border-bottom pb-3 mb-4"><i class="bi bi-mortarboard-fill me-2 text-primary"></i>Step 4: Academic History & Document Attachments</h5>
                    
                    <!-- Profile Photo Upload -->
                    <div class="mb-4 p-3 bg-light rounded-3 border">
                        <label class="form-label fw-bold small">Employee Profile Photo</label>
                        <input type="file" wire:model="photo" class="form-control">
                        @if ($photo)
                        <div class="mt-2 text-center">
                            <img src="{{ $photo->temporaryUrl() }}" class="rounded-circle border" style="width: 80px; height: 80px; object-fit: cover;">
                        </div>
                        @endif
                    </div>

                    <!-- Contract Document Upload -->
                    <div class="mb-4 p-3 bg-light rounded-3 border">
                        <label class="form-label fw-bold small">Employment Contract Attachment (PDF / Image)</label>
                        <input type="file" wire:model="contract_file" class="form-control">
                    </div>
                </div>
                @endif

                <!-- Navigation Action Buttons -->
                <div class="d-flex justify-content-between align-items-center mt-5 pt-3 border-top">
                    @if($step > 1)
                    <button type="button" wire:click="prevStep" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Previous Step
                    </button>
                    @else
                    <div></div>
                    @endif

                    @if($step < $totalSteps)
                    <button type="button" wire:click="nextStep" class="btn btn-navy">
                        Next Step <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                    @else
                    <button type="submit" class="btn btn-accent px-4">
                        <i class="bi bi-check-circle-fill me-1"></i>Complete Employee Registration
                    </button>
                    @endif
                </div>

            </form>
        </div>
    </div>
</div>
