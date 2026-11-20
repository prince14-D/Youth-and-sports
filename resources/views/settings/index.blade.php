@extends('layouts.app')
@section('title', 'System Settings')
@section('page-title', 'System Settings')
@section('breadcrumb', 'HRMS › System Settings')

@section('content')
<div class="animate-in" style="max-width: 960px; margin: 0 auto;">
    <div class="page-header mb-4">
        <div>
            <h1>System Configuration &amp; Settings</h1>
            <p>Manage Ministry operational rules, work schedules, currencies, and system defaults.</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Settings Form -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="card-title mb-0 text-navy fw-bold"><i class="bi bi-sliders me-2 text-primary"></i>Operational Rules &amp; Work Hours</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('settings.update') }}" method="POST">
                        @csrf
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <label class="form-label font-weight-600 text-navy" style="font-size: 13px;">Official Workday Start Time</label>
                                <input type="time" name="work_start_time" class="form-control" value="{{ $settings['work_start_time'] }}" required>
                                <div class="form-text" style="font-size: 11.5px;">Check-ins post this time are logged as Late Arrivals</div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label font-weight-600 text-navy" style="font-size: 13px;">Official Workday End Time</label>
                                <input type="time" name="work_end_time" class="form-control" value="{{ $settings['work_end_time'] }}" required>
                                <div class="form-text" style="font-size: 11.5px;">Standard office closing hour for attendance logs</div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <label class="form-label font-weight-600 text-navy" style="font-size: 13px;">Daily Standard Hours</label>
                                <input type="number" step="0.5" name="standard_hours" class="form-control" value="{{ $settings['standard_hours'] }}" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label font-weight-600 text-navy" style="font-size: 13px;">Statutory Retirement Age (Years)</label>
                                <input type="number" name="retirement_age" class="form-control" value="{{ $settings['retirement_age'] }}" required>
                                <div class="form-text" style="font-size: 11.5px;">Official Republic of Liberia civil service retirement threshold</div>
                            </div>
                        </div>

                        <div class="border-top pt-3 text-end">
                            <button type="submit" class="btn btn-navy px-4 rounded-pill">
                                <i class="bi bi-check2-circle me-1"></i>Save Configuration Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Quick System Status Sidebar -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="card-title mb-0 text-navy fw-bold"><i class="bi bi-bank me-2 text-warning"></i>Ministry Information</h5>
                </div>
                <div class="card-body p-4" style="font-size: 13px;">
                    <div class="mb-3">
                        <small class="text-muted text-uppercase d-block fw-semibold" style="font-size: 10.5px;">Ministry Title</small>
                        <div class="fw-bold text-navy fs-6">{{ $settings['ministry_name'] }}</div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted text-uppercase d-block fw-semibold" style="font-size: 10.5px;">Jurisdiction</small>
                        <div class="fw-bold text-navy">{{ $settings['republic_label'] }}</div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted text-uppercase d-block fw-semibold" style="font-size: 10.5px;">Dual Payroll Currencies</small>
                        <div class="d-flex gap-2 mt-1">
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1">LRD (Liberian Dollar)</span>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1">USD (US Dollar)</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="card-title mb-0 text-navy fw-bold"><i class="bi bi-shield-check me-2 text-success"></i>System Security &amp; Audit</h5>
                </div>
                <div class="card-body p-4" style="font-size: 13px;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Biometric Verification:</span>
                        <span class="badge bg-success">Active</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Audit Logging:</span>
                        <span class="badge bg-success">Enabled</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>User Access Control:</span>
                        <span class="badge bg-primary">Spatie RBAC</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
