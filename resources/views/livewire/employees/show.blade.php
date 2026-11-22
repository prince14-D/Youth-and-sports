<div class="animate-in">
    <!-- Page Header & Action Toolbar -->
    <x-ui.page-header 
        title="Employee Profile & Profiler" 
        subtitle="Detailed record summary for {{ $employee->full_name }}" 
        breadcrumb="HRMS › Employees › {{ $employee->employee_no }}">
        <x-slot:actions>
            <a href="{{ route('employees.index') }}" class="btn btn-white border shadow-xs rounded-pill px-3.5 py-2 text-navy font-weight-600 d-inline-flex align-items-center gap-2 hover-shadow transition-all" style="font-size: 13px; background: #ffffff; border-color: #CBD5E1 !important;">
                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">
                    <i class="bi bi-arrow-left text-primary" style="font-size: 13px;"></i>
                </div>
                <span>Back to Registry</span>
            </a>
            @can('edit employees')
            <a href="{{ route('employees.edit', $employee) }}" class="btn btn-accent">
                <i class="bi bi-pencil-square me-1"></i>Edit Profile
            </a>
            @endcan
            <a href="{{ route('id-cards.show', $employee) }}" class="btn btn-outline-success" title="View & Print Official ID Card">
                <i class="bi bi-person-badge me-1"></i>ID Card
            </a>
            <a href="{{ route('employees.pdf', $employee) }}" class="btn btn-outline-danger" title="Download employee profile PDF">
                <i class="bi bi-file-earmark-pdf me-1"></i>Download PDF
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="row g-4">
        <!-- Sidebar Profile Highlight Card -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm text-center mb-4">
                <div class="card-body p-4">
                    <div class="position-relative d-inline-block mb-3">
                        @if($employee->photo_url)
                        <img src="{{ $employee->photo_url }}" alt="Profile Photo" class="rounded-circle img-fluid border" style="width: 140px; height: 140px; object-fit: cover;">
                        @else
                        <div class="bg-navy text-white rounded-circle d-flex align-items-center justify-content-center fw-bold border mx-auto" style="width: 140px; height: 140px; font-size: 38px;">
                            {{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}
                        </div>
                        @endif
                        <span class="position-absolute bottom-0 end-0 p-2 bg-success border border-light rounded-circle" title="Active Account"></span>
                    </div>

                    <h4 class="fw-bold text-navy mb-1">{{ $employee->full_name }}</h4>
                    <div class="fw-bold mb-2" style="font-size: 15px; color: #dc2626 !important;">
                        <i class="bi bi-person-badge me-1"></i>{{ $employee->designation }}
                    </div>
                    <p class="text-muted small mb-3">
                        <i class="bi bi-building me-1"></i>{{ $employee->department?->name ?? 'Unassigned Department' }}
                    </p>

                    <div class="d-flex justify-content-center gap-2 mb-4">
                        <span class="badge font-monospace fw-bold px-3 py-2" style="font-size: 12px; color: #dc2626 !important; background-color: #fee2e2 !important; border: 1px solid #fca5a5 !important;">
                            ID: {{ $employee->employee_no }}
                        </span>
                        <x-ui.badge variant="{{ $employee->employment_status == 'Active' ? 'success' : 'warning' }}">
                            {{ $employee->employment_status }}
                        </x-ui.badge>
                    </div>

                    <div class="border-top pt-3 text-start small">
                        <div class="d-flex justify-content-between py-1 text-muted">
                            <span>Official Email:</span>
                            <span class="fw-semibold text-navy">{{ $employee->official_email }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1 text-muted">
                            <span>Years of Service:</span>
                            <span class="fw-semibold text-navy">{{ $employee->years_of_service }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1 text-muted">
                            <span>Date Joined:</span>
                            <span class="fw-semibold text-navy">{{ $employee->date_joined?->format('d M Y') ?? '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Buttons Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="list-group list-group-flush rounded-3">
                    <button type="button" wire:click="selectTab('overview')" class="list-group-item list-group-item-action py-3 px-4 fw-semibold d-flex align-items-center {{ $activeTab == 'overview' ? 'active bg-navy text-white' : 'text-muted' }}">
                        <i class="bi bi-grid-fill me-3 fs-5"></i>Overview & Key Info
                    </button>
                    <button type="button" wire:click="selectTab('personal')" class="list-group-item list-group-item-action py-3 px-4 fw-semibold d-flex align-items-center {{ $activeTab == 'personal' ? 'active bg-navy text-white' : 'text-muted' }}">
                        <i class="bi bi-person-vcard-fill me-3 fs-5"></i>Personal Details
                    </button>
                    <button type="button" wire:click="selectTab('employment')" class="list-group-item list-group-item-action py-3 px-4 fw-semibold d-flex align-items-center {{ $activeTab == 'employment' ? 'active bg-navy text-white' : 'text-muted' }}">
                        <i class="bi bi-cash-coin me-3 fs-5"></i>Employment & Financials
                    </button>
                    <button type="button" wire:click="selectTab('education')" class="list-group-item list-group-item-action py-3 px-4 fw-semibold d-flex align-items-center {{ $activeTab == 'education' ? 'active bg-navy text-white' : 'text-muted' }}">
                        <i class="bi bi-mortarboard-fill me-3 fs-5"></i>Education & Certs
                    </button>
                    <button type="button" wire:click="selectTab('documents')" class="list-group-item list-group-item-action py-3 px-4 fw-semibold d-flex align-items-center {{ $activeTab == 'documents' ? 'active bg-navy text-white' : 'text-muted' }}">
                        <i class="bi bi-folder-fill me-3 fs-5"></i>Documents & Attachments
                    </button>
                    <button type="button" wire:click="selectTab('timeline')" class="list-group-item list-group-item-action py-3 px-4 fw-semibold d-flex align-items-center {{ $activeTab == 'timeline' ? 'active bg-navy text-white' : 'text-muted' }}">
                        <i class="bi bi-clock-history me-3 fs-5"></i>Audit History Log
                    </button>
                </div>
            </div>
        </div>

        <!-- Tab Content Card -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">

                    <!-- OVERVIEW TAB -->
                    @if($activeTab == 'overview')
                    <div>
                        <h5 class="fw-bold text-navy border-bottom pb-3 mb-4"><i class="bi bi-grid-fill me-2 text-primary"></i>Profile Summary</h5>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="text-muted d-block small mb-1">Official Employee ID</label>
                                <span class="badge font-monospace fw-bold px-3 py-2" style="font-size: 13px; color: #dc2626 !important; background-color: #fee2e2 !important; border: 1px solid #fca5a5 !important;">
                                    {{ $employee->employee_no }}
                                </span>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted d-block small mb-1">Personnel Ref Number</label>
                                <span class="fw-bold text-navy" style="font-size:14px;">{{ $employee->personnel_number ?? '—' }}</span>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted d-block small mb-1">Position / Job Title</label>
                                <span class="fw-bold" style="font-size:14px; color: #dc2626 !important;">{{ $employee->designation }}</span>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted d-block small mb-1">Department</label>
                                <span class="fw-bold text-navy" style="font-size:14px;">{{ $employee->department?->name ?? 'Unassigned' }}</span>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted d-block small mb-1">Years of Service</label>
                                <span class="fw-bold text-navy" style="font-size:14px;">{{ $employee->years_of_service }}</span>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted d-block small mb-1">Date Joined Ministry</label>
                                <span class="fw-bold text-navy" style="font-size:14px;">{{ $employee->date_joined?->format('d M Y') ?? '—' }}</span>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted d-block small mb-1">Official Email Address</label>
                                <span class="fw-bold text-navy" style="font-size:14px;">{{ $employee->official_email }}</span>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted d-block small mb-1">Reporting Supervisor</label>
                                <span class="fw-bold text-navy" style="font-size:14px;">{{ $employee->supervisor?->full_name ?? '—' }}</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- PERSONAL DETAILS TAB -->
                    @if($activeTab == 'personal')
                    <div>
                        <h5 class="fw-bold text-navy border-bottom pb-3 mb-4"><i class="bi bi-person-vcard-fill me-2 text-primary"></i>Personal Details</h5>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="text-muted d-block small mb-1">Gender</label>
                                <span class="fw-bold text-navy" style="font-size:14px;">{{ $employee->gender }}</span>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted d-block small mb-1">Date of Birth (Age)</label>
                                <span class="fw-bold text-navy" style="font-size:14px;">{{ $employee->date_of_birth?->format('d M Y') ?? '—' }} ({{ $employee->age }} yrs)</span>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted d-block small mb-1">County of Birth</label>
                                <span class="fw-bold text-navy" style="font-size:14px;">{{ $employee->county_of_birth ?? '—' }}</span>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted d-block small mb-1">Marital Status</label>
                                <span class="fw-bold text-navy" style="font-size:14px;">{{ $employee->marital_status ?? '—' }}</span>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted d-block small mb-1">Phone Number</label>
                                <span class="fw-bold text-navy" style="font-size:14px;">{{ $employee->phone ?? '—' }}</span>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted d-block small mb-1">Personal Email</label>
                                <span class="fw-bold text-navy" style="font-size:14px;">{{ $employee->personal_email ?? '—' }}</span>
                            </div>
                            <div class="col-12">
                                <label class="text-muted d-block small mb-1">Residential Address</label>
                                <span class="fw-bold text-navy" style="font-size:14px;">{{ $employee->address ?? '—' }}</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- EMPLOYMENT & FINANCIALS TAB -->
                    @if($activeTab == 'employment')
                    <div>
                        <h5 class="fw-bold text-navy border-bottom pb-3 mb-4"><i class="bi bi-cash-coin me-2 text-primary"></i>Financials & Payroll Info</h5>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="text-muted d-block small mb-1">Basic Salary (LRD)</label>
                                <span class="fw-bold text-success fs-5">LRD ${{ number_format($employee->salary_amount_lrd, 2) }}</span>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted d-block small mb-1">Basic Salary (USD)</label>
                                <span class="fw-bold text-success fs-5">USD ${{ number_format($employee->salary_amount_usd, 2) }}</span>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted d-block small mb-1">Bank Name</label>
                                <span class="fw-bold text-navy" style="font-size:14px;">{{ $employee->bank_name ?? '—' }}</span>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted d-block small mb-1">Bank Account Number</label>
                                <span class="fw-bold font-monospace text-navy" style="font-size:14px;">{{ $employee->bank_account ?? '—' }}</span>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted d-block small mb-1">TIN Number</label>
                                <span class="fw-bold font-monospace text-navy" style="font-size:14px;">{{ $employee->tin_number ?? '—' }}</span>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted d-block small mb-1">Social Security Number</label>
                                <span class="fw-bold font-monospace text-navy" style="font-size:14px;">{{ $employee->social_security_no ?? '—' }}</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- EDUCATION & CERTS TAB -->
                    @if($activeTab == 'education')
                    <div>
                        <h5 class="fw-bold text-navy border-bottom pb-3 mb-3"><i class="bi bi-mortarboard-fill me-2 text-primary"></i>Academic Qualifications</h5>
                        <div class="table-responsive mb-5">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr style="font-size: 11.5px; text-transform: uppercase;">
                                        <th>Institution</th>
                                        <th>Qualification</th>
                                        <th class="text-center">Graduation Year</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($employee->education as $edu)
                                    <tr>
                                        <td class="fw-bold text-navy">{{ $edu->institution }}</td>
                                        <td class="text-muted">{{ $edu->qualification }}</td>
                                        <td class="text-center font-monospace">{{ $edu->graduation_year }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">No academic history records logged.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <h5 class="fw-bold text-navy border-bottom pb-3 mb-3"><i class="bi bi-award-fill me-2 text-primary"></i>Professional Certifications</h5>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr style="font-size: 11.5px; text-transform: uppercase;">
                                        <th>Certification</th>
                                        <th>Issuing Organization</th>
                                        <th class="text-center">Date Awarded</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($employee->certifications as $cert)
                                    <tr>
                                        <td class="fw-bold text-navy">{{ $cert->certification }}</td>
                                        <td class="text-muted">{{ $cert->organization }}</td>
                                        <td class="text-center">{{ $cert->date_awarded?->format('d M Y') ?? '—' }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">No certifications logged.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    <!-- ATTACHMENTS TAB -->
                    @if($activeTab == 'documents')
                    <div>
                        <h5 class="fw-bold text-navy border-bottom pb-3 mb-4"><i class="bi bi-folder-fill me-2 text-primary"></i>Document Attachments</h5>

                        <form action="{{ route('employees.documents.store', $employee) }}" method="POST" enctype="multipart/form-data" class="card border-0 bg-light mb-4">
                            @csrf
                            <div class="card-body p-4">
                                <div class="row g-3 align-items-end">
                                    <div class="col-12 col-md-3">
                                        <label for="document_type" class="form-label fw-semibold">Document Type</label>
                                        <select id="document_type" name="document_type" class="form-select" required>
                                            <option value="">Select type</option>
                                            <option value="appointment_letter">Appointment Letter</option>
                                            <option value="contract">Employment Contract</option>
                                            <option value="id_card">National ID Card</option>
                                            <option value="certificate">Certificate</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label for="document_title" class="form-label fw-semibold">Document Name</label>
                                        <input id="document_title" type="text" name="title" class="form-control" maxlength="100" placeholder="e.g. 2025 Contract" required>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label for="expiry_date" class="form-label fw-semibold">Expiry Date</label>
                                        <input id="expiry_date" type="date" name="expiry_date" class="form-control">
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label for="employee_document" class="form-label fw-semibold">Attachment</label>
                                        <input id="employee_document" type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xls,.xlsx" required>
                                        <div class="form-text">PDF, Word, image, or spreadsheet. Maximum 5 MB.</div>
                                    </div>
                                    <div class="col-12 col-md-1 d-grid">
                                        <button type="submit" class="btn btn-navy" title="Upload document">
                                            <i class="bi bi-upload"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>

                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if($errors->any())
                            <div class="alert alert-danger">{{ $errors->first() }}</div>
                        @endif

                        <div class="list-group">
                            @forelse($employee->documents as $doc)
                            <div class="list-group-item p-3 d-flex justify-content-between align-items-center bg-white border rounded-3 mb-2 shadow-xs">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="bi bi-file-earmark fs-3 text-primary"></i>
                                    <div>
                                        <div class="fw-bold text-navy" style="font-size:14px;">{{ $doc->title }}</div>
                                        <small class="text-muted" style="font-size:11.5px;">{{ ucfirst(str_replace('_', ' ', $doc->document_type)) }} &bull; {{ $doc->file_size ?? 'Attached file' }}</small>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                <a href="{{ $doc->file_url }}" target="_blank" class="btn btn-sm btn-outline-navy rounded-pill px-3">
                                    <i class="bi bi-download me-1"></i>Download
                                </a>
                                <form action="{{ route('employees.documents.destroy', [$employee, $doc->id]) }}" method="POST" onsubmit="return confirm('Delete this document?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete document"><i class="bi bi-trash"></i></button>
                                </form>
                                </div>
                            </div>
                            @empty
                            <x-ui.empty-state 
                                title="No supporting documents" 
                                message="No employment contracts or ID cards uploaded for this employee." 
                                icon="bi bi-folder-x" />
                            @endforelse
                        </div>
                    </div>
                    @endif

                    <!-- AUDIT HISTORY LOG TAB -->
                    @if($activeTab == 'timeline')
                    <div>
                        <h5 class="fw-bold text-navy border-bottom pb-3 mb-4"><i class="bi bi-clock-history me-2 text-primary"></i>Audit Activity Trail</h5>
                        <div class="timeline">
                            @forelse($audits as $audit)
                            <div class="p-3 border rounded-3 bg-light bg-opacity-50 mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-navy" style="font-size:13.5px;">{{ $audit->user?->name ?? 'System' }}</span>
                                    <span class="badge bg-light text-dark border" style="font-size:11px;">{{ $audit->created_at->format('d M Y H:i') }}</span>
                                </div>
                                <p class="mb-0 text-muted" style="font-size:12.5px;">{{ $audit->description }}</p>
                            </div>
                            @empty
                            <x-ui.empty-state 
                                title="No audit logs recorded" 
                                message="No historical changes or updates logged for this record." 
                                icon="bi bi-journal-x" />
                            @endforelse
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
