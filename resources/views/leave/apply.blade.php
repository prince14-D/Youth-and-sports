@extends('layouts.app')
@section('title', 'Apply for Leave')
@section('page-title', 'Apply for Leave')
@section('breadcrumb', 'HRMS › Leave Management › Apply')

@section('content')
<div class="animate-in">
    <div class="page-header">
        <div>
            <h1>Apply for Leave</h1>
            <p>Submit a new leave application request for approval.</p>
        </div>
        <a href="{{ route('leave.approvals') }}" class="btn btn-outline-navy">
            <i class="bi bi-card-checklist me-1"></i>View Approvals
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger mb-4">
        <strong><i class="bi bi-exclamation-triangle me-2"></i>Please fix the following errors:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
            <li style="font-size:13px;">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="row">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header"><div class="card-header-title"><i class="bi bi-calendar-plus"></i>Leave Request Form</div></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('leave.store') }}" novalidate>
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Employee <span class="text-danger">*</span></label>
                                <select name="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required {{ auth()->user()->hasRole('Employee') ? 'disabled' : '' }}>
                                    <option value="">Select Employee</option>
                                    @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->full_name }} ({{ $emp->employee_no }})
                                    </option>
                                    @endforeach
                                </select>
                                @if(auth()->user()->hasRole('Employee'))
                                    <input type="hidden" name="employee_id" value="{{ auth()->user()->employee_id }}">
                                @endif
                                @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Leave Type <span class="text-danger">*</span></label>
                                <select name="leave_type_id" id="leave_type" class="form-select @error('leave_type_id') is-invalid @enderror" required>
                                    <option value="">Select Leave Type</option>
                                    @foreach($leaveTypes as $type)
                                    <option value="{{ $type->id }}" {{ old('leave_type_id') == $type->id ? 'selected' : '' }} data-days="{{ $type->days_allowed }}">
                                        {{ $type->name }} (Allowed: {{ $type->days_allowed ? $type->days_allowed . ' days' : 'Flexible' }})
                                    </option>
                                    @endforeach
                                </select>
                                @error('leave_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror"
                                    value="{{ old('start_date') }}" onchange="calculateDays()" required>
                                @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">End Date <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" id="end_date" class="form-control @error('end_date') is-invalid @enderror"
                                    value="{{ old('end_date') }}" onchange="calculateDays()" required>
                                @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <div class="p-3 rounded border text-navy d-flex justify-content-between align-items-center" style="background:#f0f2f7; font-weight:600;">
                                    <span>Calculated Duration:</span>
                                    <span id="calculated-days" style="font-size:18px;">—</span>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Reason / Comments</label>
                                <textarea name="reason" class="form-control @error('reason') is-invalid @enderror" rows="3" placeholder="Provide details or comments supporting your leave request...">{{ old('reason') }}</textarea>
                                @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                                <button type="reset" class="btn btn-outline-secondary">Reset Form</button>
                                <button type="submit" class="btn btn-accent"><i class="bi bi-check-circle me-1"></i>Submit Application</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="col-12 col-lg-4">
            <div class="card h-100">
                <div class="card-header"><div class="card-header-title"><i class="bi bi-info-circle"></i>Leave Guidelines</div></div>
                <div class="card-body">
                    <h6 class="fw-bold text-navy mb-2">Liberian Civil Service Leave Rules:</h6>
                    <p class="text-muted" style="font-size:12.5px; line-height:1.5;">
                        Ministry staff are entitled to paid annual leave, sick leave, and compassionate leave as defined by the Civil Service Agency rules. All requests must be submitted at least <strong>5 days</strong> in advance (except for emergencies/sick leave).
                    </p>
                    <hr>
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success" style="width:28px;height:28px;display:flex;align-items:center;justify-content:center;border-radius:50%;"><i class="bi bi-check"></i></span>
                            <div style="font-size:13px;"><strong>Annual Leave</strong>: 21 business days maximum.</div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-danger" style="width:28px;height:28px;display:flex;align-items:center;justify-content:center;border-radius:50%;"><i class="bi bi-check"></i></span>
                            <div style="font-size:13px;"><strong>Maternity Leave</strong>: 90 calendar days paid.</div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-navy" style="width:28px;height:28px;display:flex;align-items:center;justify-content:center;border-radius:50%;"><i class="bi bi-check"></i></span>
                            <div style="font-size:13px;"><strong>Sick Leave</strong>: 14 days maximum. Medical certificate required for > 3 days.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function calculateDays() {
    const startVal = document.getElementById('start_date').value;
    const endVal = document.getElementById('end_date').value;
    
    if (startVal && endVal) {
        const start = new Date(startVal);
        const end = new Date(endVal);
        
        if (end >= start) {
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1; // inclusive
            document.getElementById('calculated-days').innerText = diffDays + (diffDays === 1 ? ' Day' : ' Days');
        } else {
            document.getElementById('calculated-days').innerText = 'End date cannot be before start date';
        }
    } else {
        document.getElementById('calculated-days').innerText = '—';
    }
}
</script>
@endpush
