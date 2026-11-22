@extends('layouts.app')

@section('title', $employee->full_name . ' — Contractor Profile')
@section('page-title', $employee->full_name)
@section('breadcrumb', 'HRMS › Contractors › ' . $employee->employee_no)

@section('content')
<div class="animate-in">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold text-navy mb-1">Contractor Profile</h1>
            <p class="text-muted mb-0 small">Detailed contractor record and assignment information.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('contractors.index') }}" class="btn btn-white border rounded-pill px-3.5 py-2 text-navy fw-semibold">
                <i class="bi bi-arrow-left me-1"></i>Back to Contractors
            </a>
            <a href="{{ route('id-cards.show', $employee) }}" class="btn btn-outline-success rounded-pill px-3.5 py-2">
                <i class="bi bi-person-badge me-1"></i>Contractor ID Card
            </a>
            <a href="{{ route('employees.pdf', $employee) }}" class="btn btn-outline-danger rounded-pill px-3.5 py-2">
                <i class="bi bi-file-earmark-pdf me-1"></i>Print / Download PDF
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 text-center">
                    @if($employee->photo_url)
                        <img src="{{ $employee->photo_url }}" alt="{{ $employee->full_name }}" class="rounded-circle border mb-3" style="width:140px;height:140px;object-fit:cover;">
                    @else
                        <div class="bg-navy text-white rounded-circle d-flex align-items-center justify-content-center fw-bold border mx-auto mb-3" style="width:140px;height:140px;font-size:38px;">
                            {{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}
                        </div>
                    @endif

                    <h4 class="fw-bold text-navy mb-1">{{ $employee->full_name }}</h4>
                    <div class="fw-bold mb-2" style="color: #dc2626;">{{ $employee->designation }}</div>
                    <div class="text-muted small mb-3">{{ $employee->department?->name ?? 'Unassigned department' }}</div>

                    <div class="d-flex justify-content-center gap-2 mb-3">
                        <span class="badge text-danger bg-danger-subtle border border-danger-subtle">ID: {{ $employee->employee_no }}</span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">{{ $employee->employment_status }}</span>
                    </div>

                    <div class="text-start small border-top pt-3">
                        <div class="d-flex justify-content-between py-1 text-muted">
                            <span>Official Email</span>
                            <span class="fw-semibold text-navy">{{ $employee->official_email ?? '—' }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1 text-muted">
                            <span>Phone</span>
                            <span class="fw-semibold text-navy">{{ $employee->phone ?? '—' }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1 text-muted">
                            <span>Start Date</span>
                            <span class="fw-semibold text-navy">{{ $employee->date_joined?->format('d M Y') ?? '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-navy border-bottom pb-3 mb-4"><i class="bi bi-grid-fill me-2 text-primary"></i>Contractor Overview</h5>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="text-muted d-block small mb-1">Employment Type</label>
                            <span class="fw-bold text-navy">{{ $employee->employment_type }}</span>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted d-block small mb-1">Department</label>
                            <span class="fw-bold text-navy">{{ $employee->department?->name ?? '—' }}</span>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted d-block small mb-1">Contract End Date</label>
                            <span class="fw-bold text-navy">{{ $employee->contract_end_date?->format('d M Y') ?? '—' }}</span>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted d-block small mb-1">Supervisor</label>
                            <span class="fw-bold text-navy">{{ $employee->supervisor?->full_name ?? '—' }}</span>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted d-block small mb-1">Gender</label>
                            <span class="fw-bold text-navy">{{ $employee->gender ?? '—' }}</span>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted d-block small mb-1">Nationality</label>
                            <span class="fw-bold text-navy">{{ $employee->nationality ?? '—' }}</span>
                        </div>
                        <div class="col-12">
                            <label class="text-muted d-block small mb-1">Residential Address</label>
                            <span class="fw-bold text-navy">{{ $employee->address ?? '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
