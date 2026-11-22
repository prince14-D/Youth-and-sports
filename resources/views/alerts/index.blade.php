@extends('layouts.app')
@section('title', 'HR Alerts & Expiry Radar')
@section('page-title', 'Automated Contract Expiry & Retirement Alerts')
@section('breadcrumb', 'HRMS › Compliance › Alerts & Radar')

@section('content')
<div class="animate-in">
    <div class="page-header flex-wrap gap-3">
        <div>
            <h1>Contract Expiry &amp; Retirement Alerts</h1>
            <p>Automated daily schedule (07:00 AM) monitoring 60 / 30 / 7-day contract windows, statutory retirements (age 58+), and document expiries.</p>
        </div>
        <form method="POST" action="{{ route('hr-alerts.run') }}" class="d-flex align-items-center gap-2 bg-white border rounded-3 px-3 py-2 shadow-xs">
            @csrf
            <div class="form-check mb-0 me-2">
                <input class="form-check-input" type="checkbox" name="send_emails" value="1" id="sendAlertEmails">
                <label class="form-check-label small fw-semibold" for="sendAlertEmails">Include Emails</label>
            </div>
            <button type="submit" class="btn btn-sm btn-navy">
                <i class="bi bi-bell-fill me-1"></i>Dispatch Alerts Now
            </button>
        </form>
    </div>

    <!-- Summary KPI Row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-danger">
                <div class="card-body">
                    <div class="text-muted small">Expired / ≤ 7 Days</div>
                    <div class="fs-2 fw-bold text-danger mt-1">{{ $expiredContracts->count() + $urgent7d->count() }}</div>
                    <div class="small text-muted">{{ $expiredContracts->count() }} expired &bull; {{ $urgent7d->count() }} urgent (≤7d)</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-warning">
                <div class="card-body">
                    <div class="text-muted small">Expiring 8–30 Days</div>
                    <div class="fs-2 fw-bold text-warning mt-1">{{ $warning30d->count() }}</div>
                    <div class="small text-muted">High-priority renewal window</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-primary">
                <div class="card-body">
                    <div class="text-muted small">Expiring 31–60 Days</div>
                    <div class="fs-2 fw-bold text-primary mt-1">{{ $notice60d->count() }}</div>
                    <div class="small text-muted">Advance renewal notice</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-info">
                <div class="card-body">
                    <div class="text-muted small">Retiring Soon (Age 58+)</div>
                    <div class="fs-2 fw-bold text-navy mt-1">{{ $retiringSoon->count() }}</div>
                    <div class="small text-muted">{{ $expiringDocuments->count() }} expiring doc(s) ≤30d</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contract Expiry Radar -->
    @php
        $allFlaggedContracts = $expiredContracts->concat($urgent7d)->concat($warning30d)->concat($notice60d);
    @endphp
    <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
        <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-navy"><i class="bi bi-calendar-x-fill text-danger me-2"></i>Contractor Contract Expiry Radar (≤ 60 Days &amp; Expired)</h5>
            <a href="{{ route('contracts.renewals') }}" class="btn btn-sm btn-outline-navy">Open Renewals</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Contractor</th>
                        <th>Department</th>
                        <th>End Date</th>
                        <th>Alert Tier</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($allFlaggedContracts as $c)
                        @php
                            $days = (int) today()->diffInDays($c->contract_end_date, false);
                            $tier = match (true) {
                                $days < 0   => ['label' => 'Expired (' . abs($days) . 'd ago)', 'badge' => 'bg-danger'],
                                $days <= 7  => ['label' => "Urgent ({$days}d left)", 'badge' => 'bg-danger'],
                                $days <= 30 => ['label' => "Warning ({$days}d left)", 'badge' => 'bg-warning text-dark'],
                                default     => ['label' => "60-Day Notice ({$days}d left)", 'badge' => 'bg-primary'],
                            };
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-navy">{{ $c->full_name }}</div>
                                <small class="text-muted">{{ $c->employee_no }} &bull; {{ $c->designation }}</small>
                            </td>
                            <td>{{ $c->department?->name ?? '—' }}</td>
                            <td class="fw-semibold">{{ $c->contract_end_date?->format('d M Y') }}</td>
                            <td><span class="badge {{ $tier['badge'] }}">{{ $tier['label'] }}</span></td>
                            <td class="text-end pe-4">
                                <a href="{{ route('contractors.show', $c) }}" class="btn btn-sm btn-outline-navy">Profile</a>
                                <a href="{{ route('id-cards.show', $c) }}" class="btn btn-sm btn-outline-success" title="ID Card"><i class="bi bi-person-badge"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-4 text-muted">No contractor agreements are expired or expiring within 60 days.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="row g-4">
        <!-- Statutory Retirement Radar -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="mb-0 fw-bold text-navy"><i class="bi bi-person-check-fill text-info me-2"></i>Statutory Retirement Radar (Age 58+)</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr><th class="ps-3">Staff Member</th><th>Age</th><th>60-Yr Date</th><th class="pe-3 text-end">Profile</th></tr>
                        </thead>
                        <tbody>
                            @forelse($retiringSoon as $s)
                                <tr>
                                    <td class="ps-3 py-2">
                                        <div class="fw-bold text-navy small">{{ $s->full_name }}</div>
                                        <small class="text-muted">{{ $s->employee_no }} &bull; {{ $s->department?->name ?? '—' }}</small>
                                    </td>
                                    <td><span class="badge bg-info-subtle text-info-emphasis">{{ $s->age }} yrs</span></td>
                                    <td class="small">{{ $s->date_of_birth?->copy()->addYears(60)->format('d M Y') }}</td>
                                    <td class="pe-3 text-end"><a href="{{ route('employees.show', $s) }}" class="btn btn-sm btn-outline-navy">View</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center py-4 text-muted small">No active personnel age 58 or older.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Expiring Documents -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="mb-0 fw-bold text-navy"><i class="bi bi-file-earmark-medical text-warning me-2"></i>Expiring Staff Documents (≤ 30 Days)</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr><th class="ps-3">Document</th><th>Staff Member</th><th class="pe-3">Expiry</th></tr>
                        </thead>
                        <tbody>
                            @forelse($expiringDocuments as $d)
                                <tr>
                                    <td class="ps-3 py-2 fw-semibold small text-navy">{{ $d->title }}</td>
                                    <td class="small">{{ $d->employee?->full_name ?? '—' }}</td>
                                    <td class="pe-3 small">
                                        <span class="badge {{ $d->expiry_date->isPast() ? 'bg-danger' : 'bg-warning text-dark' }}">
                                            {{ $d->expiry_date->format('d M Y') }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center py-4 text-muted small">No staff documents expiring within 30 days.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
