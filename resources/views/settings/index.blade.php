@extends('layouts.app')
@section('title', 'System Settings')
@section('page-title', 'System Settings')
@section('breadcrumb', 'HRMS › System Settings')

@section('content')
<div class="animate-in" style="max-width: 1080px; margin: 0 auto;">
    <div class="page-header mb-4">
        <div>
            <h1>System Configuration &amp; SMTP Email Settings</h1>
            <p>Manage Ministry operational rules, configure Hostinger SMTP email delivery, and test live email sending.</p>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-3">{{ $errors->first() }}</div>
    @endif

    <div class="row g-4">
        <!-- Left Column: Hostinger SMTP Configuration + Operational Rules -->
        <div class="col-12 col-lg-7">
            <!-- Hostinger SMTP Configuration & Test -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 text-navy fw-bold"><i class="bi bi-envelope-check me-2 text-success"></i>Hostinger SMTP Email Configuration</h5>
                    <span class="badge {{ $mailConfig['mailer'] === 'smtp' ? 'bg-success' : 'bg-warning text-dark' }} text-uppercase">Driver: {{ $mailConfig['mailer'] }}</span>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('settings.mail.update') }}" method="POST" class="mb-4 pb-4 border-bottom">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-navy">SMTP Host <span class="text-danger">*</span></label>
                                <input type="text" name="mail_host" class="form-control form-control-sm" value="{{ old('mail_host', $mailConfig['host']) }}" placeholder="smtp.hostinger.com" required>
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-bold text-navy">Port <span class="text-danger">*</span></label>
                                <select name="mail_port" class="form-select form-select-sm" required>
                                    <option value="465" {{ (string)old('mail_port', $mailConfig['port']) === '465' ? 'selected' : '' }}>465 (SSL)</option>
                                    <option value="587" {{ (string)old('mail_port', $mailConfig['port']) === '587' ? 'selected' : '' }}>587 (TLS)</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-bold text-navy">Encryption</label>
                                <select name="mail_scheme" class="form-select form-select-sm">
                                    <option value="smtps" {{ old('mail_scheme', $mailConfig['scheme']) === 'smtps' ? 'selected' : '' }}>SSL (smtps)</option>
                                    <option value="null" {{ in_array(old('mail_scheme', $mailConfig['scheme']), ['', 'null', 'smtp'], true) ? 'selected' : '' }}>STARTTLS (auto)</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-navy">SMTP Email Username <span class="text-danger">*</span></label>
                                <input type="email" name="mail_username" class="form-control form-control-sm" value="{{ old('mail_username', $mailConfig['username']) }}" placeholder="noreply@tecliberia.com" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-navy">SMTP Email Password</label>
                                <input type="password" name="mail_password" class="form-control form-control-sm" placeholder="{{ $mailConfig['has_password'] ? '•••••••• (leave blank to keep current)' : 'Enter Hostinger mailbox password' }}">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-navy">From Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="mail_from_address" class="form-control form-control-sm" value="{{ old('mail_from_address', $mailConfig['from_address']) }}" placeholder="noreply@tecliberia.com" required>
                                <div class="form-text" style="font-size:11px;">On Hostinger, this must match SMTP Username.</div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-navy">Sender Display Name <span class="text-danger">*</span></label>
                                <input type="text" name="mail_from_name" class="form-control form-control-sm" value="{{ old('mail_from_name', $mailConfig['from_name']) }}" required>
                            </div>
                        </div>
                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-navy btn-sm px-4">
                                <i class="bi bi-save me-1"></i>Save SMTP Settings
                            </button>
                        </div>
                    </form>

                    <!-- Live SMTP Delivery Test -->
                    <form action="{{ route('settings.test-email') }}" method="POST" class="row g-2 align-items-end">
                        @csrf
                        <div class="col-sm-8">
                            <label class="form-label small fw-bold text-navy">Send Live SMTP Test Email</label>
                            <input type="email" name="test_email" class="form-control form-control-sm" value="{{ auth()->user()?->email }}" placeholder="recipient@gmail.com" required>
                        </div>
                        <div class="col-sm-4">
                            <button type="submit" class="btn btn-success btn-sm w-100 fw-semibold">
                                <i class="bi bi-send-check me-1"></i>Test Delivery
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Operational Rules Form -->
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
                    <h5 class="card-title mb-0 text-navy fw-bold"><i class="bi bi-shield-check me-2 text-success"></i>Hostinger SMTP Settings Reference</h5>
                </div>
                <div class="card-body p-4 small">
                    <ul class="ps-3 text-muted mb-3">
                        <li><strong>SMTP Host:</strong> <code>smtp.hostinger.com</code></li>
                        <li><strong>Port &amp; Security:</strong> <code>465</code> (SSL / <code>smtps</code>) or <code>587</code> (STARTTLS)</li>
                        <li><strong>Username &amp; From:</strong> Must match a mailbox created in Hostinger hPanel › Emails (e.g. <code>noreply@tecliberia.com</code>)</li>
                    </ul>
                    <div class="alert alert-info py-2 mb-0" style="font-size:11.5px;">
                        <i class="bi bi-info-circle me-1"></i>All system emails (Payslips, Welcome Logins, HR Broadcasts, Password Resets, and Contract Alerts) use this SMTP configuration automatically.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
