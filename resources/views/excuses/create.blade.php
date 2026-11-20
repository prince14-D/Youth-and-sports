@extends('layouts.app')
@section('title', 'Submit Excuse')
@section('page-title', 'Submit Excuse')
@section('breadcrumb', 'HRMS › Leave Management › Excuse')
@section('content')
<div class="animate-in">
    <div class="page-header"><div><h1>Submit Excuse</h1><p>Record an attendance excuse for HR review.</p></div><a href="{{ route('excuses.index') }}" class="btn btn-outline-navy"><i class="bi bi-arrow-left me-1"></i>Back to Excuses</a></div>
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    <div class="card border-0 shadow-sm" style="max-width:850px;">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('excuses.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Employee</label>
                        <select name="employee_id" class="form-select" required {{ auth()->user()->hasRole('Employee') ? 'disabled' : '' }}>
                            <option value="">Select employee</option>
                            @foreach($employees as $employee)<option value="{{ $employee->id }}" {{ old('employee_id', auth()->user()->employee_id) == $employee->id ? 'selected' : '' }}>{{ $employee->employee_no }} - {{ $employee->full_name }}</option>@endforeach
                        </select>
                        @if(auth()->user()->hasRole('Employee'))<input type="hidden" name="employee_id" value="{{ auth()->user()->employee_id }}">@endif
                    </div>
                    <div class="col-md-6"><label class="form-label fw-semibold">Excuse Date</label><input type="date" name="excuse_date" class="form-control" value="{{ old('excuse_date', request('date')) }}" max="{{ today()->toDateString() }}" required></div>
                    <div class="col-md-6"><label class="form-label fw-semibold">Excuse Type</label><select name="excuse_type" class="form-select" required><option value="">Select type</option><option>Medical</option><option>Family emergency</option><option>Official assignment</option><option>Transport disruption</option><option>Other</option></select></div>
                    <div class="col-md-6"><label class="form-label fw-semibold">Attendance Record <span class="text-muted">(optional)</span></label><select name="attendance_id" class="form-select"><option value="">No linked attendance record</option>@foreach($attendanceDates as $attendance)<option value="{{ $attendance->id }}">{{ $attendance->attendance_date->format('d M Y') }}</option>@endforeach</select></div>
                    <div class="col-12"><label class="form-label fw-semibold">Reason</label><textarea name="reason" class="form-control" rows="4" maxlength="1000" required placeholder="Explain why the absence or attendance issue should be excused.">{{ old('reason') }}</textarea></div>
                    <div class="col-12"><label class="form-label fw-semibold">Supporting Document <span class="text-muted">(optional)</span></label><input type="file" name="attachment" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"><div class="form-text">Maximum 5 MB.</div></div>
                </div>
                <button class="btn btn-navy mt-4"><i class="bi bi-send me-1"></i>Submit Excuse</button>
            </form>
        </div>
    </div>
</div>
@endsection
