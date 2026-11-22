@extends('layouts.app')

@section('title', 'HR Data Analyst Dashboard')
@section('page-title', 'HR Data Analyst Workspace')
@section('breadcrumb', 'HRMS › HR Data Analyst')

@section('content')
<div class="animate-in">
    @php
        $hour = now()->hour;
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
        $user = auth()->user();
    @endphp

    <!-- Hero Banner -->
    <div class="card border-0 shadow-sm mb-4 text-white overflow-hidden" style="background: linear-gradient(135deg, #0b1f4b 0%, #123f7a 55%, #1d4ed8 100%); border-radius: 16px;">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <span class="badge rounded-pill px-3 py-1 mb-2" style="background: rgba(255,255,255,0.18); font-size: 11px;">
                        <i class="bi bi-database-fill-gear me-1"></i>HR DATA &amp; RECORDS ANALYST WORKSPACE
                    </span>
                    <h3 class="fw-bold mb-1">{{ $greeting }}, {{ $user->name }}!</h3>
                    <p class="mb-0 opacity-85 small">Register employees, onboard contractors &amp; manage contract records, run bulk CSV imports, and verify HR data completeness.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('employees.create') }}" class="btn btn-light text-navy fw-semibold shadow-sm">
                        <i class="bi bi-person-plus-fill me-1 text-primary"></i>Register Employee
                    </a>
                    <a href="{{ route('employees.create', ['type' => 'Contract']) }}" class="btn btn-warning text-dark fw-semibold shadow-sm">
                        <i class="bi bi-file-earmark-plus-fill me-1"></i>Register Contractor &amp; Contract
                    </a>
                    <a href="{{ route('employees.import') }}" class="btn btn-outline-light fw-semibold">
                        <i class="bi bi-cloud-upload me-1"></i>Bulk CSV Import
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small fw-medium">Total Workforce Records</div>
                            <div class="fs-2 fw-bold text-navy mt-1">{{ number_format($totalEmployees) }}</div>
                            <div class="small text-success fw-semibold mt-1">
                                <i class="bi bi-arrow-up-short"></i>{{ $newThisMonth }} added this month
                            </div>
                        </div>
                        <div class="rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:46px;height:46px;">
                            <i class="bi bi-people-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small fw-medium">Active Contractors</div>
                            <div class="fs-2 fw-bold text-success mt-1">{{ number_format($activeContractors) }}</div>
                            <div class="small text-muted mt-1">{{ number_format($contractorCount) }} total contractor files</div>
                        </div>
                        <div class="rounded-3 bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width:46px;height:46px;">
                            <i class="bi bi-file-earmark-person-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small fw-medium">Contracts Needing Attention</div>
                            <div class="fs-2 fw-bold text-warning mt-1">{{ $expiringContracts->count() + $expiredContracts->count() + $missingContractDates->count() }}</div>
                            <div class="small text-danger mt-1">
                                {{ $expiredContracts->count() }} expired &bull; {{ $expiringContracts->count() }} expiring ≤60d
                            </div>
                        </div>
                        <div class="rounded-3 bg-warning-subtle text-warning d-flex align-items-center justify-content-center" style="width:46px;height:46px;">
                            <i class="bi bi-calendar-event-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small fw-medium">Incomplete Staff Profiles</div>
                            <div class="fs-2 fw-bold text-danger mt-1">{{ number_format($incompleteCount) }}</div>
                            <div class="small text-muted mt-1">Missing phone, TIN, bank or dates</div>
                        </div>
                        <div class="rounded-3 bg-danger-subtle text-danger d-flex align-items-center justify-content-center" style="width:46px;height:46px;">
                            <i class="bi bi-clipboard2-pulse-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Tools Strip -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
        <div class="card-body py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="fw-semibold text-navy small"><i class="bi bi-lightning-charge-fill text-warning me-1"></i>Quick Data Operations:</div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('employees.index') }}" class="btn btn-sm btn-outline-navy"><i class="bi bi-table me-1"></i>All Employees ({{ $totalEmployees }})</a>
                <a href="{{ route('contractors.index') }}" class="btn btn-sm btn-outline-navy"><i class="bi bi-person-lines-fill me-1"></i>Contractor Directory ({{ $contractorCount }})</a>
                <a href="{{ route('contracts.index') }}" class="btn btn-sm btn-outline-navy"><i class="bi bi-file-earmark-text me-1"></i>Contract Register</a>
                <a href="{{ route('contracts.renewals') }}" class="btn btn-sm btn-outline-warning text-dark">
                    <i class="bi bi-arrow-repeat me-1"></i>Contract Renewals
                    @if($pendingRenewalsCount > 0)
                        <span class="badge bg-danger ms-1">{{ $pendingRenewalsCount }}</span>
                    @endif
                </a>
                <a href="{{ route('hr-alerts.index') }}" class="btn btn-sm btn-outline-danger"><i class="bi bi-bell-fill me-1"></i>Expiry Alerts Radar</a>
                <a href="{{ route('reports.workforce') }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-file-earmark-bar-graph me-1"></i>Workforce Report</a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Contractor Contracts Management Table -->
        <div class="col-12 col-xl-7">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="mb-0 fw-bold text-navy"><i class="bi bi-file-earmark-check me-2 text-primary"></i>Contractor Contracts Registry</h5>
                        <small class="text-muted">Register, monitor, and update contractor contract terms &amp; end dates.</small>
                    </div>
                    <a href="{{ route('employees.create', ['type' => 'Contract']) }}" class="btn btn-sm btn-navy">
                        <i class="bi bi-plus-lg me-1"></i>New Contractor
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Contractor</th>
                                    <th>Department</th>
                                    <th>Contract Period</th>
                                    <th>Status</th>
                                    <th class="text-end pe-3">Manage</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($allContractors as $contractor)
                                    @php
                                        $daysLeft = $contractor->contract_days_remaining;
                                    @endphp
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-bold text-navy">{{ $contractor->full_name }}</div>
                                            <small class="text-muted">{{ $contractor->employee_no }} &bull; {{ $contractor->designation }}</small>
                                        </td>
                                        <td><span class="small">{{ $contractor->department?->name ?? 'Unassigned' }}</span></td>
                                        <td>
                                            <div class="small fw-semibold">
                                                {{ $contractor->date_joined?->format('d M Y') ?? '—' }} &rarr; {{ $contractor->contract_end_date?->format('d M Y') ?? 'Not Set' }}
                                            </div>
                                            @if(is_null($daysLeft))
                                                <small class="text-warning fw-semibold">End date missing</small>
                                            @elseif($daysLeft < 0)
                                                <small class="text-danger fw-semibold">Expired {{ abs($daysLeft) }} days ago</small>
                                            @elseif($daysLeft <= 60)
                                                <small class="text-warning fw-semibold">{{ $daysLeft }} days remaining</small>
                                            @else
                                                <small class="text-success">{{ $daysLeft }} days remaining</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $contractor->employment_status === 'Active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                                {{ $contractor->employment_status }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#contractModal{{ $contractor->id }}" title="Update Contract">
                                                <i class="bi bi-pencil-square me-1"></i>Contract
                                            </button>
                                            <a href="{{ route('contractors.show', $contractor) }}" class="btn btn-sm btn-outline-secondary" title="View Profile">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>

                                    <!-- Quick Contract Update Modal -->
                                    <div class="modal fade" id="contractModal{{ $contractor->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                            <div class="modal-content border-0 shadow">
                                                <form method="POST" action="{{ route('hr-analyst.contracts.quick-update', $contractor) }}" enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="modal-header bg-navy text-white">
                                                        <h5 class="modal-title"><i class="bi bi-file-earmark-text me-2"></i>Update Contract: {{ $contractor->full_name }}</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label">Department <span class="text-danger">*</span></label>
                                                                <select name="department_id" class="form-select" required>
                                                                    @foreach($departments as $dept)
                                                                        <option value="{{ $dept->id }}" {{ $contractor->department_id == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Role / Designation <span class="text-danger">*</span></label>
                                                                <input type="text" name="designation" class="form-control" value="{{ $contractor->designation }}" required>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label">Contract Start Date <span class="text-danger">*</span></label>
                                                                <input type="date" name="date_joined" class="form-control" value="{{ $contractor->date_joined?->format('Y-m-d') ?? now()->toDateString() }}" required>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label">Contract End Date <span class="text-danger">*</span></label>
                                                                <input type="date" name="contract_end_date" class="form-control" value="{{ $contractor->contract_end_date?->format('Y-m-d') ?? now()->addYear()->toDateString() }}" required>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label">Contract Status <span class="text-danger">*</span></label>
                                                                <select name="employment_status" class="form-select" required>
                                                                    @foreach(['Active', 'Inactive', 'Suspended', 'Terminated'] as $st)
                                                                        <option value="{{ $st }}" {{ $contractor->employment_status === $st ? 'selected' : '' }}>{{ $st }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Monthly Stipend / Fee (LRD)</label>
                                                                <input type="number" step="0.01" min="0" name="salary_amount_lrd" class="form-control" value="{{ $contractor->salary_amount_lrd }}">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Monthly Stipend / Fee (USD)</label>
                                                                <input type="number" step="0.01" min="0" name="salary_amount_usd" class="form-control" value="{{ $contractor->salary_amount_usd }}">
                                                            </div>
                                                            <div class="col-12">
                                                                <label class="form-label">Attach Signed Contract Document <span class="text-muted">(PDF / DOC / Image, optional)</span></label>
                                                                <input type="file" name="contract_file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-navy"><i class="bi bi-check-circle me-1"></i>Save Contract Update</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="bi bi-file-earmark-person fs-2 d-block mb-2 opacity-50"></i>
                                            No contractor contracts recorded yet.
                                            <div class="mt-2">
                                                <a href="{{ route('employees.create', ['type' => 'Contract']) }}" class="btn btn-sm btn-navy">Register First Contractor</a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Completeness Queue -->
        <div class="col-12 col-xl-5">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fw-bold text-navy"><i class="bi bi-clipboard2-check me-2 text-danger"></i>Data Quality &amp; Completeness</h5>
                        <small class="text-muted">Active records missing phone, TIN, bank account, or contract end date.</small>
                    </div>
                    <span class="badge bg-danger-subtle text-danger">{{ $incompleteCount }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($incompleteProfiles as $emp)
                            @php
                                $missing = [];
                                if (empty($emp->phone)) $missing[] = 'Phone';
                                if (empty($emp->tin_number)) $missing[] = 'TIN';
                                if (empty($emp->bank_account)) $missing[] = 'Bank Acct';
                                if ($emp->employment_type === 'Contract' && empty($emp->contract_end_date)) $missing[] = 'Contract End';
                            @endphp
                            <div class="list-group-item px-3 py-3 d-flex align-items-center justify-content-between gap-2">
                                <div>
                                    <div class="fw-bold text-navy small">{{ $emp->full_name }} <span class="badge bg-light text-dark border ms-1">{{ $emp->employment_type }}</span></div>
                                    <div class="text-muted" style="font-size:11.5px;">{{ $emp->employee_no }} &bull; {{ $emp->department?->name ?? 'No Dept' }}</div>
                                    <div class="mt-1 d-flex flex-wrap gap-1">
                                        @foreach($missing as $field)
                                            <span class="badge bg-danger-subtle text-danger" style="font-size:10px;">Missing {{ $field }}</span>
                                        @endforeach
                                    </div>
                                </div>
                                <a href="{{ route('employees.edit', $emp) }}" class="btn btn-sm btn-outline-navy flex-shrink-0">
                                    <i class="bi bi-pencil me-1"></i>Complete
                                </a>
                            </div>
                        @empty
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-patch-check-fill text-success fs-2 d-block mb-2"></i>
                                All active staff records are complete!
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Registrations Table -->
    <div class="card border-0 shadow-sm mt-4" style="border-radius: 14px;">
        <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-bold text-navy"><i class="bi bi-clock-history me-2 text-primary"></i>Recent Employee &amp; Contractor Registrations</h5>
                <small class="text-muted">Latest personnel profiles entered into the HRMIS database.</small>
            </div>
            <a href="{{ route('employees.index') }}" class="btn btn-sm btn-outline-navy">View Full Registry</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Employee / Contractor</th>
                            <th>Type</th>
                            <th>Department</th>
                            <th>Designation</th>
                            <th>Joined</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentRegistrations as $emp)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-navy">{{ $emp->full_name }}</div>
                                <small class="text-muted">{{ $emp->employee_no }} &bull; {{ $emp->official_email ?? 'No email' }}</small>
                            </td>
                            <td>
                                <span class="badge {{ $emp->employment_type === 'Contract' ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' : 'bg-primary-subtle text-primary' }}">
                                    {{ $emp->employment_type }}
                                </span>
                            </td>
                            <td>{{ $emp->department?->name ?? '—' }}</td>
                            <td>{{ $emp->designation ?? '—' }}</td>
                            <td>{{ $emp->date_joined?->format('d M Y') ?? '—' }}</td>
                            <td>{!! $emp->status_badge !!}</td>
                            <td class="text-end pe-4">
                                <a href="{{ route('employees.show', $emp) }}" class="btn btn-sm btn-outline-navy" title="View"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('employees.edit', $emp) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                                <a href="{{ route('id-cards.show', $emp) }}" class="btn btn-sm btn-outline-success" title="ID Card"><i class="bi bi-person-badge"></i></a>
                                <a href="{{ route('employees.pdf', $emp) }}" class="btn btn-sm btn-outline-danger" title="Download PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-4 text-muted">No employee records registered yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
