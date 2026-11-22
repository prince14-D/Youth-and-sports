@extends('layouts.app')
@section('title', 'System Settings')
@section('page-title', 'System Settings')
@section('breadcrumb', 'HRMS › System Settings')

@section('content')
<div class="animate-in" style="max-width: 1040px; margin: 0 auto;">
    <div class="page-header mb-4">
        <div>
            <h1>System Configuration &amp; Email Settings</h1>
            <p>Manage Ministry operational rules, Hostinger SMTP / email diagnostics, and system defaults.</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Settings Form -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
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
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label font-weight-600 text-navy" style="font-size: 13px;">Official Workday End Time</label>
                                <input type="time" name="work_end_time" class="form-control" value="{{ $settings['work_end_time'] }}" required>
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

            <!-- Email / Hostinger SMTP Diagnostics & Test -->
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 text-navy fw-bold"><i class="bi bi-envelope-check me-2 text-success"></i>Email &amp; Hostinger SMTP Delivery</h5>
                    <span class="badge bg-primary text-uppercase">{{ $mailConfig['mailer'] }}</span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-2 small mb-3">
                        <div class="col-sm-6"><div class="p-2 rounded bg-light"><span class="text-muted d-block">Active Driver</span><strong>{{ $mailConfig['mailer'] }}</strong></div></div>
                        <div class="col-sm-6"><div class="p-2 rounded bg-light"><span class="text-muted d-block">SMTP Host / Port</span><strong>{{ $mailConfig['host'] }}:{{ $mailConfig['port'] }} ({{ $mailConfig['scheme'] }})</strong></div></div>
                        <div class="col-sm-6"><div class="p-2 rounded bg-light"><span class="text-muted d-block">SMTP Username</span><strong class="text-break">{{ $mailConfig['username'] }}</strong></div></div>
                        <div class="col-sm-6"><div class="p-2 rounded bg-light"><span class="text-muted d-block">From Address</span><strong class="text-break">{{ $mailConfig['from_address'] }}</strong></div></div>
                    </div>

                    @if($mailConfig['from_address'] === 'onboarding@resend.dev')
                    <div class="alert alert-warning small py-2 mb-3">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        <strong>Heads up:</strong> Currently using Resend sandbox address (<code>onboarding@resend.dev</code>), which only delivers to your Resend owner email. Switch <code>.env</code> to <strong>Hostinger SMTP</strong> (<code>smtp.hostinger.com</code>) before emailing all Ministry staff!
                    </div>
                    @endif

                    <form action="{{ route('settings.test-email') }}" method="POST" class="row g-2 align-items-end">
                        @csrf
                        <div class="col-sm-8">
                            <label class="form-label small fw-bold text-navy">Send Live Test Email</label>
                            <input type="email" name="test_email" class="form-control form-control-sm" value="{{ auth()->user()?->email }}" placeholder="recipient@domain.com" required>
                        </div>
                        <div class="col-sm-4">
                            <button type="submit" class="btn btn-success btn-sm w-100 fw-semibold">
                                <i class="bi bi-send-check me-1"></i>Test Delivery
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Quick System Status Sidebar -->
        <div class="col-12 col-lg-5">
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
                    <div class="mb-0">
                        <small class="text-muted text-uppercase d-block fw-semibold" style="font-size: 10.5px;">Dual Payroll Currencies</small>
                        <div class="d-flex flex-wrap gap-2 mt-1">
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1">LRD (Liberian Dollar)</span>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1">USD (US Dollar)</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="card-title mb-0 text-navy fw-bold"><i class="bi bi-shield-check me-2 text-success"></i>Hostinger SMTP Quick Reference</h5>
                </div>
                <div class="card-body p-4 small">
                    <p class="text-muted mb-2">To send payslips, welcome logins, and HR alerts via your Hostinger domain email, update these lines in <code>.env</code>:</p>
                    <pre class="p-2 rounded bg-light border mb-2" style="font-size:11px; white-space:pre-wrap;">MAIL_MAILER=smtp
MAIL_SCHEME=smtps
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=YourMailboxPassword
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Ministry of Youth &amp; Sports HRMS"</pre>
                    <div class="text-muted" style="font-size:11.5px;">After editing <code>.env</code>, run <code>php artisan config:clear</code> and click <strong>Test Delivery</strong>.</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
