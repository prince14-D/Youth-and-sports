<div class="animate-in">
    @php
        $authUser = auth()->user();
        $roleName = $authUser?->primary_role ?? 'Administrator';
        $dashboardHeading = match ($roleName) {
            'Human Resource Director' => 'HR Director Executive Dashboard',
            'Assistant HR Director'   => 'Assistant HR Director Dashboard',
            'HR Officer'              => 'HR Officer Operations Dashboard',
            'System Administrator'    => 'System Administrator Command Center',
            default                   => 'Executive Dashboard',
        };
    @endphp

    <!-- Header with Role Badge & Date -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h1 class="h3 fw-bold text-navy mb-0">{{ $dashboardHeading }}</h1>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1" style="font-size:11px;">{{ $roleName }}</span>
            </div>
            <p class="text-muted mb-0 small">Human Resource Management Information System &mdash; Ministry of Youth and Sports</p>
        </div>
        <div class="bg-white border rounded-3 px-3 py-2 text-muted small d-flex align-items-center gap-2 shadow-xs">
            <i class="bi bi-calendar3 text-primary"></i>
            <span class="fw-semibold text-navy">{{ now()->format('l, d F Y') }}</span>
        </div>
    </div>

    <!-- Role-Aware Quick Operations Strip -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
        <div class="card-body py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="fw-semibold text-navy small"><i class="bi bi-lightning-charge-fill text-warning me-1"></i>Quick Runs &amp; Actions:</div>
            <div class="d-flex flex-wrap gap-2">
                @can('view leave')
                    <a href="{{ route('approvals.index') }}" class="btn btn-sm btn-primary fw-semibold">
                        <i class="bi bi-diagram-3-fill me-1"></i>Approval Center
                    </a>
                @endcan
                @can('create employees')
                    <a href="{{ route('employees.create') }}" class="btn btn-sm btn-navy"><i class="bi bi-person-plus me-1"></i>Register Employee</a>
                    <a href="{{ route('employees.create', ['type' => 'Contract']) }}" class="btn btn-sm btn-outline-navy"><i class="bi bi-file-earmark-plus me-1"></i>Register Contractor</a>
                @endcan
                @can('approve leave')
                    <a href="{{ route('leave.approvals') }}" class="btn btn-sm btn-outline-warning text-dark">
                        <i class="bi bi-calendar2-check me-1"></i>Leave Approvals
                        @if($pendingLeaveCount > 0)<span class="badge bg-danger ms-1">{{ $pendingLeaveCount }}</span>@endif
                    </a>
                @endcan
                @can('approve excuses')
                    <a href="{{ route('excuses.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-shield-exclamation me-1"></i>Excuse Reviews
                        @if($pendingExcusesCount > 0)<span class="badge bg-danger ms-1">{{ $pendingExcusesCount }}</span>@endif
                    </a>
                @endcan
                @can('manage contract renewals')
                    <a href="{{ route('contracts.renewals') }}" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-arrow-repeat me-1"></i>Renewals
                        @if($pendingRenewalsCount > 0)<span class="badge bg-warning text-dark ms-1">{{ $pendingRenewalsCount }}</span>@endif
                    </a>
                @endcan
                @can('view payroll')
                    <a href="{{ route('payroll.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-cash-stack me-1"></i>Payroll Runs
                        @if($pendingPayrollCount > 0)<span class="badge bg-primary ms-1">{{ $pendingPayrollCount }}</span>@endif
                    </a>
                @endcan
                @can('create users')
                    <a href="{{ route('users.create', ['role' => $authUser->hasRole('System Administrator') ? 'System Administrator' : 'HR Officer']) }}" class="btn btn-sm btn-accent">
                        <i class="bi bi-shield-plus me-1"></i>{{ $authUser->hasRole('System Administrator') ? 'Create Admin / User' : 'Add HR Account' }}
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <!-- Top Row: Executive KPI Cards (8 Grid Layout - Attendance Removed) -->
    <div class="row g-3 mb-4">
        <!-- 1. Total Employees -->
        <div class="col-12 col-sm-6 col-md-4 col-xl-3">
            <x-ui.kpi-card 
                title="Total Workforce" 
                value="{{ number_format($headcount) }}" 
                subtitle="All personnel records" 
                badgeColor="#2563EB" 
                badgeBg="#EFF6FF" 
                :avatars="$activeAvatars" 
                actionLabel="View All" 
                actionUrl="{{ route('employees.index') }}" />
        </div>

        <!-- 2. Active Employees -->
        <div class="col-12 col-sm-6 col-md-4 col-xl-3">
            <x-ui.kpi-card 
                title="Active Staff" 
                value="{{ number_format($activeCount) }}" 
                subtitle="Active employment status" 
                badgeColor="#16A34A" 
                badgeBg="#DCFCE7" 
                trend="{{ $activeRate }}% Active"
                trendType="up"
                actionLabel="Directory" 
                actionUrl="{{ route('employees.index', ['status' => 'Active']) }}" />
        </div>

        <!-- 3. Active Contractors -->
        <div class="col-12 col-sm-6 col-md-4 col-xl-3">
            <x-ui.kpi-card 
                title="Active Contractors" 
                value="{{ number_format($activeContractorsCount) }}" 
                subtitle="{{ $expiringContractsCount }} expiring within 60 days" 
                badgeColor="#0284C7" 
                badgeBg="#E0F2FE" 
                trend="{{ $expiringContractsCount }} Due" 
                trendType="{{ $expiringContractsCount == 0 ? 'up' : 'down' }}" 
                actionLabel="Contracts" 
                actionUrl="{{ route('contracts.index') }}" />
        </div>

        <!-- 4. Pending Excuses -->
        <div class="col-12 col-sm-6 col-md-4 col-xl-3">
            <x-ui.kpi-card 
                title="Pending Excuses" 
                value="{{ number_format($pendingExcusesCount) }}" 
                subtitle="Submitted for HR review" 
                badgeColor="#EF4444" 
                badgeBg="#FEE2E2" 
                trend="{{ $pendingExcusesCount }} Pending" 
                trendType="{{ $pendingExcusesCount == 0 ? 'up' : 'down' }}" 
                actionLabel="Review" 
                actionUrl="{{ route('excuses.index') }}" />
        </div>

        <!-- 5. Employees on Leave -->
        <div class="col-12 col-sm-6 col-md-4 col-xl-3">
            <x-ui.kpi-card 
                title="Staff on Leave" 
                value="{{ number_format($onLeave) }}" 
                subtitle="Active authorized leave" 
                badgeColor="#D97706" 
                badgeBg="#FEF3C7" 
                trend="{{ $onLeave }} Active" 
                trendType="neutral" 
                actionLabel="Approvals" 
                actionUrl="{{ route('leave.approvals') }}" />
        </div>

        <!-- 6. Pending Leave Requests -->
        <div class="col-12 col-sm-6 col-md-4 col-xl-3">
            <x-ui.kpi-card 
                title="Pending Leave" 
                value="{{ number_format($pendingLeaveCount) }}" 
                subtitle="Awaiting authorization" 
                badgeColor="#EA580C" 
                badgeBg="#FFEDD5" 
                trend="{{ $pendingLeaveCount }} Requests" 
                trendType="neutral" 
                actionLabel="Review" 
                actionUrl="{{ route('leave.approvals') }}" />
        </div>

        <!-- 7. Departments -->
        <div class="col-12 col-sm-6 col-md-4 col-xl-3">
            <x-ui.kpi-card 
                title="Departments" 
                value="{{ number_format($departmentCount) }}" 
                subtitle="Operational divisions" 
                badgeColor="#7C3AED" 
                badgeBg="#F3E8FF" 
                trend="Active Units" 
                trendType="neutral" 
                actionLabel="Manage" 
                actionUrl="{{ route('departments.index') }}" />
        </div>

        <!-- 8. Retiring Soon -->
        <div class="col-12 col-sm-6 col-md-4 col-xl-3">
            <x-ui.kpi-card 
                title="Retiring Soon" 
                value="{{ number_format($retiringSoonCount) }}" 
                subtitle="Staff age 58 and above" 
                badgeColor="#DB2777" 
                badgeBg="#FCE7F3" 
                :avatars="$retiringAvatars" 
                actionLabel="Report" 
                actionUrl="{{ route('reports.workforce') }}" />
        </div>
    </div>

    <!-- Middle Section: Workforce & Departmental Analytics (Live Data) -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="card-title mb-0 text-navy fw-bold"><i class="bi bi-bar-chart-line-fill me-2 text-primary"></i>Workforce Composition &amp; Departmental Distribution</h5>
                        <small class="text-muted" style="font-size: 11.5px;">Live Ministry headcount across departments and employment categories</small>
                    </div>
                    <a href="{{ route('reports.workforce') }}" class="btn btn-sm btn-outline-navy rounded-pill px-3">
                        <i class="bi bi-file-earmark-bar-graph me-1"></i>Full Workforce Report
                    </a>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4 align-items-center">
                        <div class="col-12 col-md-7">
                            <div class="position-relative" style="height: 260px; width: 100%;">
                                <canvas id="deptHeadcountChart"></canvas>
                            </div>
                        </div>
                        <div class="col-12 col-md-5 border-start-md ps-md-4">
                            <div class="position-relative d-flex align-items-center justify-content-center mx-auto" style="width: 175px; height: 175px;">
                                <canvas id="employmentMixChart"></canvas>
                                <div class="position-absolute text-center">
                                    <div class="fs-3 fw-bold text-navy lh-1">{{ $activeCount }}</div>
                                    <small class="text-muted fw-semibold" style="font-size:10.5px;">Active Staff</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-around w-100 mt-3 text-center" style="font-size:11.5px;">
                                <div><span class="d-inline-block rounded-circle me-1" style="width:10px; height:10px; background-color:#2563EB;"></span><span class="text-muted fw-semibold">Permanent ({{ $permanentCount }})</span></div>
                                <div><span class="d-inline-block rounded-circle me-1" style="width:10px; height:10px; background-color:#10B981;"></span><span class="text-muted fw-semibold">Contract ({{ $activeContractorsCount }})</span></div>
                                <div><span class="d-inline-block rounded-circle me-1" style="width:10px; height:10px; background-color:#F59E0B;"></span><span class="text-muted fw-semibold">Other ({{ $otherTypesCount }})</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Section: Three-Column Grid Layout -->
    <div class="row g-4">
        <!-- COLUMN ONE: Recent Employees & Upcoming Birthdays -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 text-navy fw-bold"><i class="bi bi-people me-2 text-primary"></i>Recent Registrations</h5>
                    <a href="{{ route('employees.index') }}" class="btn btn-sm btn-link p-0 text-decoration-none">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($recentEmployees as $emp)
                        <div class="list-group-item px-3 py-3 d-flex align-items-center gap-3 border-bottom">
                            @if($emp->photo_path && file_exists(public_path("storage/{$emp->photo_path}")))
                            <img src="{{ $emp->photo_url }}" alt="{{ $emp->full_name }}" class="rounded-circle border" style="width:38px; height:38px; object-fit:cover;">
                            @else
                            <div class="rounded-circle bg-navy text-white d-flex align-items-center justify-content-center fw-bold border" style="width:38px; height:38px; font-size:13px;">
                                {{ strtoupper(substr($emp->first_name,0,1) . substr($emp->last_name,0,1)) }}
                            </div>
                            @endif
                            <div style="flex:1;">
                                <a href="{{ route('employees.show', $emp) }}" class="fw-bold text-navy text-decoration-none" style="font-size:13.5px;">{{ $emp->full_name }}</a>
                                <div style="font-size:11.5px; color: #dc2626 !important;" class="fw-bold">{{ $emp->designation }}</div>
                            </div>
                            <span class="badge font-monospace" style="font-size:11px; color: #dc2626 !important; background: #fee2e2; border: 1px solid #fca5a5;">
                                {{ $emp->employee_no }}
                            </span>
                        </div>
                        @empty
                        <x-ui.empty-state title="No employees" message="No recent employee records." icon="bi bi-people" />
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 text-navy fw-bold"><i class="bi bi-gift-fill me-2 text-warning"></i>Upcoming Birthdays</h5>
                    <span class="badge bg-gold text-white px-2 py-1" style="font-size: 11px;">Next 14 Days</span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($birthdays as $emp)
                        <div class="list-group-item px-3 py-3 d-flex align-items-center gap-3 border-bottom">
                            <div class="rounded-circle bg-navy text-white d-flex align-items-center justify-content-center fw-bold border" style="width:38px; height:38px; font-size:13px;">
                                {{ strtoupper(substr($emp->first_name,0,1) . substr($emp->last_name,0,1)) }}
                            </div>
                            <div style="flex:1;">
                                <div class="fw-bold text-navy" style="font-size:13.5px;">{{ $emp->full_name }}</div>
                                <div style="font-size:11.5px; color: #dc2626 !important;" class="fw-bold">{{ $emp->designation }}</div>
                            </div>
                            <span class="badge bg-gold px-2.5 py-1.5" style="font-size:11px;">{{ $emp->date_of_birth?->format('d M') }}</span>
                        </div>
                        @empty
                        <x-ui.empty-state title="No upcoming birthdays" message="No employee birthdays in the next 14 days." icon="bi bi-gift" />
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- COLUMN TWO: Things To Do & Audit Logs -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 text-navy fw-bold"><i class="bi bi-card-checklist me-2 text-primary"></i>Things To Do</h5>
                    <button type="button" wire:click="openTaskModal" class="btn btn-sm btn-outline-navy rounded-pill px-3">
                        <i class="bi bi-plus-lg me-1"></i>New task
                    </button>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex flex-column gap-2">
                        @forelse($tasks as $task)
                        <div class="p-3 border rounded-3 bg-white d-flex align-items-center justify-content-between shadow-xs transition-all {{ $task->is_completed ? 'bg-light text-muted opacity-75' : '' }}">
                            <div class="d-flex align-items-center gap-3">
                                <input type="checkbox" class="form-check-input mt-0" style="width: 18px; height: 18px; cursor: pointer;" wire:click="toggleTask({{ $task->id }})" {{ $task->is_completed ? 'checked' : '' }}>
                                <div>
                                    <div class="fw-bold {{ $task->is_completed ? 'text-decoration-line-through text-muted' : 'text-navy' }}" style="font-size:13.5px;">
                                        {{ $task->title }}
                                    </div>
                                    @if($task->description)
                                    <small class="text-muted d-block" style="font-size:11.5px;">{{ $task->description }}</small>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                @if($task->due_date_badge)
                                <span class="badge {{ $task->due_date_badge == 'Overdue' ? 'bg-danger-subtle text-danger' : 'bg-light text-dark border' }} rounded-pill px-2.5 py-1.5" style="font-size:10.5px;">
                                    {{ $task->due_date_badge }}
                                </span>
                                @endif
                                <button type="button" wire:click="deleteTask({{ $task->id }})" class="btn btn-sm text-danger opacity-50 hover-opacity-100 p-0 border-0 ms-1">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        @empty
                        <x-ui.empty-state title="No pending tasks" message="All HR checklist tasks completed." icon="bi bi-check-all" />
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 text-navy fw-bold"><i class="bi bi-journal-text me-2 text-primary"></i>Recent Audit Activity</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($activities as $act)
                        <div class="list-group-item px-3 py-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong class="text-navy" style="font-size:13px;">{{ $act->user_name ?? 'System' }}</strong>
                                <small class="text-muted badge bg-light text-dark" style="font-size:10.5px;">{{ $act->created_at->diffForHumans() }}</small>
                            </div>
                            <div class="text-muted mt-1" style="font-size:12px;">{{ $act->description }}</div>
                        </div>
                        @empty
                        <x-ui.empty-state title="No audit logs" message="No audit activity recorded." icon="bi bi-journal-x" />
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- COLUMN THREE: Staff on Leave & Quick HR Statistics -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0 text-navy fw-bold"><i class="bi bi-calendar2-x me-2 text-warning"></i>Staff on Leave</h5>
                        <small class="text-muted" style="font-size:11px;">Active authorized leave applications</small>
                    </div>
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1.5" style="font-size: 10.5px;">Live</span>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex flex-column gap-2">
                        @forelse($onLeaveStaff as $leave)
                        <div class="p-3 border rounded-3 bg-light bg-opacity-50 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-warning-subtle text-warning-emphasis rounded-circle d-flex align-items-center justify-content-center fw-bold border" style="width:38px; height:38px; font-size:12px;">
                                    {{ strtoupper(substr($leave->employee?->first_name ?? 'E',0,1) . substr($leave->employee?->last_name ?? 'S',0,1)) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-navy" style="font-size:13px;">{{ $leave->employee?->full_name }}</div>
                                    <small class="text-muted" style="font-size:11px;">
                                        <i class="bi bi-calendar-event me-1 text-warning"></i>{{ $leave->start_date?->format('d M') }} &ndash; {{ $leave->end_date?->format('d M Y') }}
                                    </small>
                                </div>
                            </div>
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1.5" style="font-size:10.5px;">Leave</span>
                        </div>
                        @empty
                        <x-ui.empty-state title="No staff on leave" message="All ministry personnel active." icon="bi bi-person-check" />
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="card-title mb-0 text-navy fw-bold"><i class="bi bi-pie-chart-fill me-2 text-info"></i>Quick HR Statistics</h5>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small">Active Personnel Rate</span>
                        <span class="fw-bold text-success">{{ $activeRate }}%</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small">New Hires This Month</span>
                        <span class="fw-bold text-navy">{{ $newHiresThisMonth }} Staff</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small">Expiring Contracts (≤60d)</span>
                        <span class="fw-bold {{ $expiringContractsCount > 0 ? 'text-danger' : 'text-success' }}">{{ $expiringContractsCount }} Contracts</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small">Pending Contract Renewals</span>
                        <span class="fw-bold text-primary">{{ $pendingRenewalsCount }} Requests</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2">
                        <span class="text-muted small">Pending Leave &amp; Excuses</span>
                        <span class="fw-bold text-warning">{{ $pendingLeaveCount + $pendingExcusesCount }} Pending</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- NEW TASK MODAL DIALOG -->
    @if($showTaskModal)
    <div class="modal fade show d-block tab-modal-backdrop" style="background: rgba(0,0,0,0.5);" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <div class="modal-header bg-navy text-white">
                    <h5 class="modal-header-title mb-0 text-white"><i class="bi bi-plus-circle me-2"></i>Create New HR Task</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeTaskModal"></button>
                </div>
                <div class="modal-body p-4">
                    <form wire:submit.prevent="createTask">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Task Title <span class="text-danger">*</span></label>
                            <input type="text" wire:model.blur="newTaskTitle" class="form-control @error('newTaskTitle') is-invalid @enderror" placeholder="e.g. Review Contractor Renewals">
                            @error('newTaskTitle') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Description / Instructions</label>
                            <textarea wire:model.blur="newTaskDescription" rows="3" class="form-control" placeholder="Describe the details of this task..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Due Date</label>
                            <input type="date" wire:model.blur="newTaskDueDate" class="form-control">
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="button" wire:click="closeTaskModal" class="btn btn-outline-secondary">Cancel</button>
                            <button type="submit" class="btn btn-navy"><i class="bi bi-check-circle me-1"></i>Add Task</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', initWorkforceCharts);
document.addEventListener('livewire:navigated', initWorkforceCharts);

function initWorkforceCharts() {
    initEmploymentMixChart();
    initDeptHeadcountChart();
}

function initEmploymentMixChart() {
    const ctx = document.getElementById('employmentMixChart');
    if (!ctx) return;
    if (window.employmentMixChartInstance) {
        window.employmentMixChartInstance.destroy();
    }
    window.employmentMixChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Permanent', 'Contract', 'Other'],
            datasets: [{
                data: [{{ $permanentCount }}, {{ $activeContractorsCount }}, {{ $otherTypesCount }}],
                backgroundColor: ['#2563EB', '#10B981', '#F59E0B'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            cutout: '76%',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { enabled: true } }
        }
    });
}

function initDeptHeadcountChart() {
    const ctx = document.getElementById('deptHeadcountChart');
    if (!ctx) return;
    if (window.deptHeadcountChartInstance) {
        window.deptHeadcountChartInstance.destroy();
    }
    window.deptHeadcountChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($deptLabels),
            datasets: [{
                label: 'Active Staff',
                data: @json($deptCounts),
                backgroundColor: '#123F7A',
                borderRadius: 6,
                barPercentage: 0.65
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
                x: { grid: { display: false } }
            }
        }
    });
}
</script>
@endpush
