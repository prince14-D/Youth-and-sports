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
                        @elseif($step == 2) Employment &amp; Official Identifiers
                        @elseif($step == 3) Financials &amp; Compensation
                        @elseif($step == 4) Academic Qualifications &amp; Certifications
                        @elseif($step == 5) Next of Kin &amp; Emergency Contacts
                        @else Document Attachments &amp; Final Review
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
                                    <p class="text-muted small mb-3" style="font-size: 12px;">Take a live photo using your webcam/camera or upload an image file for biometric attendance verification and official personnel ID cards.</p>
                                    
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
                                        <button type="button" wire:click="$set('photo_base64', ''); $set('photo', null)" class="btn btn-outline-danger btn-sm rounded-pill px-2.5 py-1" title="Clear Photo">
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
                            <label class="form-label fw-bold small">Marital Status</label>
                            <select wire:model.blur="marital_status" class="form-select">
                                <option value="">Select Status</option>
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Divorced">Divorced</option>
                                <option value="Widowed">Widowed</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <label class="form-label fw-bold small">Nationality</label>
                            <input type="text" wire:model.blur="nationality" class="form-control" placeholder="e.g. Liberian">
                        </div>

                        <div class="col-12 col-sm-6 col-md-4">
                            <label class="form-label fw-bold small">County of Birth</label>
                            <input type="text" wire:model.blur="county_of_birth" class="form-control" placeholder="e.g. Montserrado">
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <label class="form-label fw-bold small">Official Email <span class="text-danger">*</span></label>
                            <input type="email" wire:model.blur="official_email" class="form-control @error('official_email') is-invalid @enderror" placeholder="aaron.jones@mys.gov.lr">
                            @error('official_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <label class="form-label fw-bold small">Personal Email</label>
                            <input type="email" wire:model.blur="personal_email" class="form-control" placeholder="aaron@gmail.com">
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold small">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" wire:model.blur="phone" class="form-control @error('phone') is-invalid @enderror" placeholder="+231 88 123 4567">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label fw-bold small">Residential Address</label>
                            <input type="text" wire:model.blur="address" class="form-control" placeholder="Enter physical home address...">
                        </div>
                    </div>
                </div>
                @endif

                <!-- STEP 2: Employment Information -->
                @if($step == 2)
                <div>
                    <h5 class="fw-bold text-navy border-bottom pb-3 mb-4"><i class="bi bi-briefcase-fill me-2 text-primary"></i>Step 2: Employment &amp; Official Identifiers</h5>
                    
                    <div class="p-3 mb-4 rounded-3" style="background-color: #fee2e2; border: 1px solid #fca5a5;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-shield-lock-fill fs-5" style="color: #dc2626;"></i>
                            <div style="color: #dc2626;" class="fw-bold small">
                                Official Employee ID Directives: If the employee already has an official Ministry ID, enter it below. Once saved, it becomes their permanent official record identifier.
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold small">Registration Status Type</label>
                            <select wire:model.live="is_existing_employee" class="form-select">
                                <option value="1">Existing Employee (Enter Official ID)</option>
                                <option value="0">New Hire (Auto-generate ID)</option>
                            </select>
                        </div>
                        @if($is_existing_employee)
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold small" style="color: #dc2626 !important;">Official Employee ID <span class="text-danger">*</span></label>
                            <input type="text" wire:model.blur="employee_no" class="form-control fw-bold font-monospace @error('employee_no') is-invalid @enderror" placeholder="e.g. MYS-2022-0412" style="color: #dc2626 !important;">
                            @error('employee_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        @endif
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold small">Personnel / CSA Number</label>
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

                        <div class="col-12 col-sm-6 col-md-3">
                            <label class="form-label fw-bold small">Employment Type</label>
                            <select wire:model.live="employment_type" class="form-select">
                                <option value="Permanent">Permanent</option>
                                <option value="Contract">Contract</option>
                                <option value="Probationary">Probationary</option>
                                <option value="Temporary">Temporary</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <label class="form-label fw-bold small">Employment Status</label>
                            <select wire:model.blur="employment_status" class="form-select">
                                <option value="Active">Active</option>
                                <option value="On Leave">On Leave</option>
                                <option value="Suspended">Suspended</option>
                                <option value="Probationary">Probationary</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <label class="form-label fw-bold small">Salary Grade</label>
                            <input type="text" wire:model.blur="salary_grade" class="form-control" placeholder="e.g. Grade 9">
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <label class="form-label fw-bold small">Date Joined <span class="text-danger">*</span></label>
                            <input type="date" wire:model.live="date_joined" class="form-control @error('date_joined') is-invalid @enderror">
                            @error('date_joined') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        @if($employment_type === 'Contract')
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small">Contract Duration <span class="text-danger">*</span></label>
                            <select wire:model.live="contract_duration" class="form-select @error('contract_duration') is-invalid @enderror">
                                <option value="3">3 Months</option>
                                <option value="6">6 Months</option>
                                <option value="12">1 Year</option>
                                <option value="24">2 Years</option>
                                <option value="36">3 Years</option>
                                <option value="60">5 Years</option>
                                <option value="custom">Custom End Date</option>
                            </select>
                            @error('contract_duration') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        @if($contract_duration === 'custom')
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small">Contract End Date <span class="text-danger">*</span></label>
                            <input type="date" wire:model.blur="contract_end_date" min="{{ $date_joined }}" class="form-control @error('contract_end_date') is-invalid @enderror">
                            @error('contract_end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        @elseif($contract_end_date)
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small">Calculated Contract End Date</label>
                            <input type="text" value="{{ \Carbon\Carbon::parse($contract_end_date)->format('d M Y') }}" class="form-control" readonly>
                        </div>
                        @endif
                        @endif

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
                    <h5 class="fw-bold text-navy border-bottom pb-3 mb-4"><i class="bi bi-cash-coin me-2 text-primary"></i>Step 3: Financials &amp; Payroll Account</h5>
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

                <!-- STEP 4: Academic Qualifications & Professional Certifications -->
                @if($step == 4)
                <div>
                    <h5 class="fw-bold text-navy border-bottom pb-3 mb-4"><i class="bi bi-mortarboard-fill me-2 text-primary"></i>Step 4: Academic Qualifications &amp; Professional Certifications</h5>

                    <!-- Academic Qualifications Card -->
                    <div class="p-3.5 bg-light rounded-3 border mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-navy mb-0"><i class="bi bi-book-half me-2 text-primary"></i>Academic Qualifications (Optional)</h6>
                            <span class="badge bg-white text-navy border">{{ count($educations) }} Added</span>
                        </div>
                        <div class="row g-2.5 align-items-end">
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small">Institution</label>
                                <input type="text" wire:model="new_institution" class="form-control @error('new_institution') is-invalid @enderror" placeholder="e.g. University of Liberia">
                                @error('new_institution') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label fw-bold small">Qualification / Degree</label>
                                <input type="text" wire:model="new_qualification" class="form-control @error('new_qualification') is-invalid @enderror" placeholder="e.g. B.Sc., MPA, Diploma">
                                @error('new_qualification') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label fw-bold small">Field of Study</label>
                                <input type="text" wire:model="new_field_of_study" class="form-control" placeholder="e.g. Public Administration">
                            </div>
                            <div class="col-12 col-md-2">
                                <label class="form-label fw-bold small">Year Obtained</label>
                                <input type="number" wire:model="new_year_obtained" class="form-control @error('new_year_obtained') is-invalid @enderror" placeholder="e.g. 2020">
                                @error('new_year_obtained') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 text-end mt-2">
                                <button type="button" wire:click="addEducation" class="btn btn-navy btn-sm px-3">
                                    <i class="bi bi-plus-circle me-1"></i>Add Qualification
                                </button>
                            </div>
                        </div>

                        @if(count($educations))
                        <div class="table-responsive mt-3">
                            <table class="table table-sm table-bordered bg-white mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Institution</th>
                                        <th>Qualification</th>
                                        <th>Field of Study</th>
                                        <th>Year</th>
                                        <th class="text-end" style="width: 70px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($educations as $idx => $edu)
                                    <tr>
                                        <td class="fw-semibold">{{ $edu['institution'] }}</td>
                                        <td>{{ $edu['qualification'] }}</td>
                                        <td>{{ $edu['field_of_study'] ?: '—' }}</td>
                                        <td>{{ $edu['year_obtained'] ?: '—' }}</td>
                                        <td class="text-end">
                                            <button type="button" wire:click="removeEducation({{ $idx }})" class="btn btn-outline-danger btn-sm py-0 px-2">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </div>

                    <!-- Professional Certifications Card -->
                    <div class="p-3.5 bg-light rounded-3 border">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-navy mb-0"><i class="bi bi-award-fill me-2 text-primary"></i>Professional Certifications &amp; Licenses (Optional)</h6>
                            <span class="badge bg-white text-navy border">{{ count($certifications) }} Added</span>
                        </div>
                        <div class="row g-2.5 align-items-end">
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small">Certification Name</label>
                                <input type="text" wire:model="new_certification_name" class="form-control @error('new_certification_name') is-invalid @enderror" placeholder="e.g. Certified HR Professional">
                                @error('new_certification_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small">Issuing Body / Organization</label>
                                <input type="text" wire:model="new_issuing_body" class="form-control" placeholder="e.g. LIPA / SHRM">
                            </div>
                            <div class="col-12 col-md-2">
                                <label class="form-label fw-bold small">Issue Date</label>
                                <input type="date" wire:model="new_issue_date" class="form-control @error('new_issue_date') is-invalid @enderror">
                                @error('new_issue_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-2">
                                <label class="form-label fw-bold small">Expiry Date</label>
                                <input type="date" wire:model="new_expiry_date" class="form-control @error('new_expiry_date') is-invalid @enderror">
                                @error('new_expiry_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 text-end mt-2">
                                <button type="button" wire:click="addCertification" class="btn btn-navy btn-sm px-3">
                                    <i class="bi bi-plus-circle me-1"></i>Add Certification
                                </button>
                            </div>
                        </div>

                        @if(count($certifications))
                        <div class="table-responsive mt-3">
                            <table class="table table-sm table-bordered bg-white mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Certification</th>
                                        <th>Issuing Body</th>
                                        <th>Issue Date</th>
                                        <th>Expiry Date</th>
                                        <th class="text-end" style="width: 70px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($certifications as $idx => $cert)
                                    <tr>
                                        <td class="fw-semibold">{{ $cert['name'] }}</td>
                                        <td>{{ $cert['issuing_body'] ?: '—' }}</td>
                                        <td>{{ $cert['issue_date'] ?: '—' }}</td>
                                        <td>{{ $cert['expiry_date'] ?: '—' }}</td>
                                        <td class="text-end">
                                            <button type="button" wire:click="removeCertification({{ $idx }})" class="btn btn-outline-danger btn-sm py-0 px-2">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- STEP 5: Next of Kin & Emergency Contacts -->
                @if($step == 5)
                <div>
                    <h5 class="fw-bold text-navy border-bottom pb-3 mb-4"><i class="bi bi-people-fill me-2 text-primary"></i>Step 5: Next of Kin &amp; Emergency Contacts</h5>

                    <!-- Next of Kin Section -->
                    <div class="p-3.5 bg-light rounded-3 border mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-navy mb-0"><i class="bi bi-person-heart me-2 text-danger"></i>Next of Kin Details</h6>
                            <span class="badge bg-white text-navy border">{{ count($next_of_kin) }} Added</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small">Full Name</label>
                                <input type="text" wire:model="kin_name" class="form-control @error('kin_name') is-invalid @enderror" placeholder="e.g. Mary Jones">
                                @error('kin_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small">Relationship</label>
                                <input type="text" wire:model="kin_relationship" class="form-control @error('kin_relationship') is-invalid @enderror" placeholder="e.g. Spouse, Mother, Sibling">
                                @error('kin_relationship') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small">Phone Number</label>
                                <input type="text" wire:model="kin_phone" class="form-control @error('kin_phone') is-invalid @enderror" placeholder="e.g. +231 77 123 4567">
                                @error('kin_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small">Email Address</label>
                                <input type="email" wire:model="kin_email" class="form-control @error('kin_email') is-invalid @enderror" placeholder="e.g. mary@example.com">
                                @error('kin_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-5">
                                <label class="form-label fw-bold small">Residential Address</label>
                                <input type="text" wire:model="kin_address" class="form-control" placeholder="e.g. Sinkor, Monrovia">
                            </div>
                            <div class="col-12 col-md-3 d-flex align-items-end justify-content-between gap-2">
                                <div class="form-check mb-2">
                                    <input type="checkbox" wire:model="kin_is_primary" class="form-check-input" id="kinIsPrimary">
                                    <label class="form-check-label small fw-semibold" for="kinIsPrimary">Primary</label>
                                </div>
                                <button type="button" wire:click="addNextOfKin" class="btn btn-navy btn-sm px-3">
                                    <i class="bi bi-plus-circle me-1"></i>Add Next of Kin
                                </button>
                            </div>
                        </div>

                        @if(count($next_of_kin))
                        <div class="table-responsive mt-3">
                            <table class="table table-sm table-bordered bg-white mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Full Name</th>
                                        <th>Relationship</th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                        <th>Address</th>
                                        <th class="text-end" style="width: 70px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($next_of_kin as $idx => $kin)
                                    <tr>
                                        <td class="fw-semibold">
                                            {{ $kin['name'] }}
                                            @if(!empty($kin['is_primary']))
                                                <span class="badge bg-navy ms-1">Primary</span>
                                            @endif
                                        </td>
                                        <td>{{ $kin['relationship'] }}</td>
                                        <td>{{ $kin['phone'] ?: '—' }}</td>
                                        <td>{{ $kin['email'] ?: '—' }}</td>
                                        <td>{{ $kin['address'] ?: '—' }}</td>
                                        <td class="text-end">
                                            <button type="button" wire:click="removeNextOfKin({{ $idx }})" class="btn btn-outline-danger btn-sm py-0 px-2">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </div>

                    <!-- Emergency Contacts Section -->
                    <div class="p-3.5 bg-light rounded-3 border">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-navy mb-0"><i class="bi bi-telephone-plus-fill me-2 text-warning"></i>Emergency Contacts (Optional)</h6>
                            <span class="badge bg-white text-navy border">{{ count($emergency_contacts) }} Added</span>
                        </div>
                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-md-3">
                                <label class="form-label fw-bold small">Contact Name</label>
                                <input type="text" wire:model="ec_name" class="form-control @error('ec_name') is-invalid @enderror" placeholder="e.g. Samuel Doe">
                                @error('ec_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label fw-bold small">Relationship</label>
                                <input type="text" wire:model="ec_relationship" class="form-control @error('ec_relationship') is-invalid @enderror" placeholder="e.g. Brother, Friend">
                                @error('ec_relationship') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label fw-bold small">Phone Number</label>
                                <input type="text" wire:model="ec_phone" class="form-control @error('ec_phone') is-invalid @enderror" placeholder="e.g. +231 88 999 0000">
                                @error('ec_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label fw-bold small">Email Address</label>
                                <input type="email" wire:model="ec_email" class="form-control @error('ec_email') is-invalid @enderror" placeholder="Optional email">
                                @error('ec_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 text-end">
                                <button type="button" wire:click="addEmergencyContact" class="btn btn-navy btn-sm px-3">
                                    <i class="bi bi-plus-circle me-1"></i>Add Emergency Contact
                                </button>
                            </div>
                        </div>

                        @if(count($emergency_contacts))
                        <div class="table-responsive mt-3">
                            <table class="table table-sm table-bordered bg-white mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Contact Name</th>
                                        <th>Relationship</th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                        <th class="text-end" style="width: 70px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($emergency_contacts as $idx => $ec)
                                    <tr>
                                        <td class="fw-semibold">{{ $ec['name'] }}</td>
                                        <td>{{ $ec['relationship'] }}</td>
                                        <td>{{ $ec['phone'] }}</td>
                                        <td>{{ $ec['email'] ?: '—' }}</td>
                                        <td class="text-end">
                                            <button type="button" wire:click="removeEmergencyContact({{ $idx }})" class="btn btn-outline-danger btn-sm py-0 px-2">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- STEP 6: Document Attachments & Final Review -->
                @if($step == 6)
                <div>
                    <h5 class="fw-bold text-navy border-bottom pb-3 mb-4"><i class="bi bi-file-earmark-arrow-up-fill me-2 text-primary"></i>Step 6: Supporting Document Attachments &amp; Final Submission</h5>

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <div class="p-3 bg-light rounded-3 border h-100">
                                <label class="form-label fw-bold small">Employment Contract Attachment (PDF / Image)</label>
                                <input type="file" wire:model="contract_file" class="form-control @error('contract_file') is-invalid @enderror" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                @error('contract_file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @if($contract_file)
                                    <small class="text-success d-block mt-2"><i class="bi bi-check-circle me-1"></i>{{ $contract_file->getClientOriginalName() }}</small>
                                @endif
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-3 bg-light rounded-3 border h-100">
                                <label class="form-label fw-bold small">National ID / Biometric Document</label>
                                <input type="file" wire:model="id_file" class="form-control @error('id_file') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png">
                                @error('id_file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @if($id_file)
                                    <small class="text-success d-block mt-2"><i class="bi bi-check-circle me-1"></i>{{ $id_file->getClientOriginalName() }}</small>
                                @endif
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-3 bg-light rounded-3 border h-100">
                                <label class="form-label fw-bold small">Academic Certificate / Transcript</label>
                                <input type="file" wire:model="academic_attachment" class="form-control @error('academic_attachment') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                @error('academic_attachment') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @if($academic_attachment)
                                    <small class="text-success d-block mt-2"><i class="bi bi-check-circle me-1"></i>{{ $academic_attachment->getClientOriginalName() }}</small>
                                @endif
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-3 bg-light rounded-3 border h-100">
                                <label class="form-label fw-bold small">Other Supporting Documents</label>
                                <input type="file" wire:model="supporting_documents" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" multiple>
                                <small class="text-muted d-block mt-2">Attach CV, appointment letters, or other HR files (optional).</small>
                                @if(count($supporting_documents))
                                    <div class="mt-2">
                                        @foreach($supporting_documents as $document)
                                            <span class="badge bg-white text-navy border me-1 mb-1">{{ $document->getClientOriginalName() }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Registration Summary Preview -->
                    <div class="p-3 rounded-3 border" style="background: #f8fafc;">
                        <div class="fw-bold text-navy small text-uppercase mb-2"><i class="bi bi-clipboard-check me-1 text-primary"></i>Registration Summary</div>
                        <div class="row g-2 small">
                            <div class="col-6 col-md-3"><span class="text-muted">Employee:</span> <strong class="text-navy">{{ trim($first_name . ' ' . $middle_name . ' ' . $last_name) ?: '—' }}</strong></div>
                            <div class="col-6 col-md-3"><span class="text-muted">Official Email:</span> <strong>{{ $official_email ?: '—' }}</strong></div>
                            <div class="col-6 col-md-3"><span class="text-muted">Designation:</span> <strong>{{ $designation ?: '—' }}</strong></div>
                            <div class="col-6 col-md-3"><span class="text-muted">Records Added:</span> <strong>{{ count($educations) }} Edu · {{ count($next_of_kin) }} Kin · {{ count($emergency_contacts) }} EC</strong></div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Navigation Action Buttons -->
                <div class="d-flex justify-content-between align-items-center mt-5 pt-3 border-top">
                    @if($step > 1)
                    <button type="button" wire:click="prevStep" class="btn btn-outline-secondary" wire:loading.attr="disabled">
                        <i class="bi bi-arrow-left me-1"></i>Previous Step
                    </button>
                    @else
                    <div></div>
                    @endif

                    @if($step < $totalSteps)
                    <button type="button" wire:click="nextStep" class="btn btn-navy" wire:loading.attr="disabled">
                        Next Step <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                    @else
                    <button type="submit" class="btn btn-accent px-4" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">
                            <i class="bi bi-check-circle-fill me-1"></i>Complete Employee Registration
                        </span>
                        <span wire:loading wire:target="save">
                            <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Registering Employee...
                        </span>
                    </button>
                    @endif
                </div>

            </form>
        </div>
    </div>
</div>
