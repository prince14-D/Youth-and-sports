<div>
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-navy text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-white"><i class="bi bi-pencil-square me-2"></i>Edit Employee Profile &mdash; {{ $employee->full_name }}</h5>
            <span class="badge bg-gold">{{ $employee->employee_no }}</span>
        </div>
        <div class="card-body">
            <form wire:submit.prevent="save">
                
                <!-- SECTION 1: Personal Information -->
                <h5 class="text-navy border-bottom pb-2 mb-3"><i class="bi bi-person-fill me-1"></i>Personal Details</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">First Name <span class="text-danger">*</span></label>
                        <input type="text" wire:model.blur="first_name" class="form-control @error('first_name') is-invalid @enderror">
                        @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Middle Name</label>
                        <input type="text" wire:model.blur="middle_name" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Last Name <span class="text-danger">*</span></label>
                        <input type="text" wire:model.blur="last_name" class="form-control @error('last_name') is-invalid @enderror">
                        @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Gender <span class="text-danger">*</span></label>
                        <select wire:model.blur="gender" class="form-select @error('gender') is-invalid @enderror">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                        @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Date of Birth</label>
                        <input type="date" wire:model.blur="date_of_birth" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Nationality</label>
                        <input type="text" wire:model.blur="nationality" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Marital Status</label>
                        <select wire:model.blur="marital_status" class="form-select">
                            <option value="">Select</option>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Divorced">Divorced</option>
                            <option value="Widowed">Widowed</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Official Email <span class="text-danger">*</span></label>
                        <input type="email" wire:model.blur="official_email" class="form-control @error('official_email') is-invalid @enderror">
                        @error('official_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Personal Email</label>
                        <input type="email" wire:model.blur="personal_email" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Phone Number</label>
                        <input type="text" wire:model.blur="phone" class="form-control">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-bold">Residential Address</label>
                        <textarea wire:model.blur="address" rows="2" class="form-control"></textarea>
                    </div>
                </div>

                <!-- SECTION 2: Employment & Designation -->
                <h5 class="text-navy border-bottom pb-2 mb-3"><i class="bi bi-briefcase-fill me-1"></i>Employment & Designation</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">
                            Official Employee ID
                            @if(!$canEditEmployeeNo)
                            <span class="badge bg-secondary ms-1">Read-Only (Protected)</span>
                            @else
                            <span class="badge bg-danger ms-1">Admin Editable</span>
                            @endif
                        </label>
                        <input type="text" wire:model.blur="employee_no" class="form-control @error('employee_no') is-invalid @enderror" {{ !$canEditEmployeeNo ? 'readonly disabled' : '' }}>
                        @error('employee_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Personnel Number (Internal Ref)</label>
                        <input type="text" wire:model.blur="personnel_number" class="form-control" placeholder="e.g. PER-8812">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Department <span class="text-danger">*</span></label>
                        <select wire:model.blur="department_id" class="form-select @error('department_id') is-invalid @enderror">
                            <option value="">Select Department</option>
                            @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                        @error('department_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Position / Job Title <span class="text-danger">*</span></label>
                        <input type="text" wire:model.blur="designation" class="form-control @error('designation') is-invalid @enderror">
                        @error('designation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Employment Status <span class="text-danger">*</span></label>
                        <select wire:model.blur="employment_status" class="form-select @error('employment_status') is-invalid @enderror">
                            <option value="Active">Active</option>
                            <option value="On Leave">On Leave</option>
                            <option value="Suspended">Suspended</option>
                            <option value="Retired">Retired</option>
                            <option value="Resigned">Resigned</option>
                            <option value="Terminated">Terminated</option>
                            <option value="Deceased">Deceased</option>
                        </select>
                        @error('employment_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Employment Type</label>
                        <select wire:model.blur="employment_type" class="form-select">
                            <option value="Permanent">Permanent</option>
                            <option value="Contract">Contract</option>
                            <option value="Probationary">Probationary</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Date Joined</label>
                        <input type="date" wire:model.blur="date_joined" class="form-control">
                    </div>

                    @if($employment_type === 'Contract')
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Contract Duration <span class="text-danger">*</span></label>
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
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Contract End Date <span class="text-danger">*</span></label>
                        <input type="date" wire:model.blur="contract_end_date" min="{{ $date_joined }}" class="form-control @error('contract_end_date') is-invalid @enderror">
                        @error('contract_end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    @else
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Calculated Contract End Date</label>
                        <input type="text" value="{{ $contract_end_date ? \Carbon\Carbon::parse($contract_end_date)->format('d M Y') : '' }}" class="form-control" readonly>
                    </div>
                    @endif
                    @endif
                </div>

                <!-- SECTION 3: Banking & Financial Details -->
                <h5 class="text-navy border-bottom pb-2 mb-3"><i class="bi bi-wallet2 me-1"></i>Banking & Financial Details</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Bank Name</label>
                        <input type="text" wire:model.blur="bank_name" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Bank Account Number</label>
                        <input type="text" wire:model.blur="bank_account" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Basic Salary LRD</label>
                        <input type="number" step="0.01" wire:model.blur="salary_amount_lrd" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Basic Salary USD</label>
                        <input type="number" step="0.01" wire:model.blur="salary_amount_usd" class="form-control">
                    </div>
                </div>

                <!-- SECTION 4: Photo Attachment -->
                <h5 class="text-navy border-bottom pb-2 mb-3"><i class="bi bi-image me-1"></i>Update Profile Photo</h5>
                <div class="row g-3 mb-4 align-items-center">
                    <div class="col-auto">
                        <img src="{{ $employee->photo_url }}" alt="Current Photo" class="rounded-circle border" style="width: 70px; height: 70px; object-fit: cover;">
                    </div>
                    <div class="col">
                        <input type="file" wire:model="photo" class="form-control">
                        <div class="form-text small">Select a new image file to replace the current photo.</div>
                    </div>
                </div>

                <!-- Actions Bar -->
                <div class="d-flex justify-content-end gap-2 border-top pt-3">
                    <a href="{{ route('employees.show', $employee) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-navy" wire:loading.attr="disabled">
                        <span wire:loading.remove><i class="bi bi-check-circle me-1"></i>Save Changes</span>
                        <span wire:loading><span class="spinner-border spinner-border-sm me-1" role="status"></span>Saving...</span>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
