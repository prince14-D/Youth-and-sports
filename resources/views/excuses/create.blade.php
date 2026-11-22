@extends('layouts.app')
@section('title', 'Submit Excuse')
@section('page-title', 'Submit Excuse')
@section('breadcrumb', 'HRMS › Leave & Excuses › Submit Excuse')
@section('content')
<div class="animate-in">
    <div class="page-header">
        <div>
            <h1>Submit Excuse Request</h1>
            <p>Submit an official absence, tardiness, or field-assignment excuse for HR review.</p>
        </div>
        <a href="{{ route('excuses.index') }}" class="btn btn-outline-navy">
            <i class="bi bi-arrow-left me-1"></i>Back to Excuses
        </a>
    </div>

    <div class="card border-0 shadow-sm" style="max-width:850px;">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('excuses.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Staff / Contractor <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required {{ !empty($isSelfOnly) ? 'disabled' : '' }}>
                            <option value="">Select staff member</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ old('employee_id', auth()->user()->employee_id) == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->employee_no }} - {{ $employee->full_name }} ({{ $employee->employment_type }})
                                </option>
                            @endforeach
                        </select>
                        @if(!empty($isSelfOnly))
                            <input type="hidden" name="employee_id" value="{{ auth()->user()->employee_id }}">
                        @endif
                        @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Excuse Date <span class="text-danger">*</span></label>
                        <input type="date" name="excuse_date" class="form-control @error('excuse_date') is-invalid @enderror" value="{{ old('excuse_date', request('date', today()->toDateString())) }}" max="{{ today()->toDateString() }}" required>
                        @error('excuse_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Excuse Category <span class="text-danger">*</span></label>
                        <select name="excuse_type" class="form-select @error('excuse_type') is-invalid @enderror" required>
                            <option value="">Select category</option>
                            @foreach(['Medical', 'Family emergency', 'Official assignment', 'Transport disruption', 'Court / Civic duty', 'Other'] as $type)
                                <option value="{{ $type }}" {{ old('excuse_type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                        @error('excuse_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Supporting Document <span class="text-muted">(optional)</span></label>
                        <input type="file" name="attachment" class="form-control @error('attachment') is-invalid @enderror" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        <div class="form-text">Medical note, official memo, or supporting proof (Max 5 MB).</div>
                        @error('attachment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Reason &amp; Details <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control @error('reason') is-invalid @enderror" rows="4" maxlength="1000" required placeholder="Clearly explain the reason for the excuse...">{{ old('reason') }}</textarea>
                        @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-navy"><i class="bi bi-send me-1"></i>Submit Excuse for Review</button>
                    <a href="{{ route('excuses.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
