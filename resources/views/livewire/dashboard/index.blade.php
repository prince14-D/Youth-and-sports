<div class="animate-in">
    <!-- Header with Date & Breadcrumb -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3 fw-bold text-navy mb-1">Executive Dashboard</h1>
            <p class="text-muted mb-0 small">Human Resource Management Information System &mdash; Ministry of Youth and Sports</p>
        </div>
        <div class="bg-white border rounded-3 px-3 py-2 text-muted small d-flex align-items-center gap-2 shadow-xs">
            <i class="bi bi-calendar3 text-primary"></i>
            <span class="fw-semibold text-navy">{{ now()->format('l, d F Y') }}</span>
        </div>
    </div>

    <!-- Top Row: Executive KPI Cards (8 Grid Layout) -->
    <div class="row g-3 mb-4">
        <!-- 1. Total Employees -->
        <div class="col-12 col-sm-6 col-md-4 col-xl-3">
            <x-ui.kpi-card 
                title="Total Employees" 
                value="{{ number_format($headcount) }}" 
                subtitle="All personnel records" 
                badgeColor="#2563EB" 
                badgeBg="#EFF6FF" 
                :avatars="$activeAvatars" 
                actionLabel="Read More" 
                actionUrl="{{ route('employees.index') }}" />
        </div>

        <!-- 2. Active Employees -->
        <div class="col-12 col-sm-6 col-md-4 col-xl-3">
            <x-ui.kpi-card 
                title="Active Employees" 
                value="{{ number_format($activeCount) }}" 
                subtitle="Active employment status" 
                badgeColor="#16A34A" 
                badgeBg="#DCFCE7" 
                :avatars="$activeAvatars" 
                actionLabel="View All" 
                actionUrl="{{ route('employees.index', ['status' => 'Active']) }}" />
        </div>

        <!-- 3. Present Today -->
        <div class="col-12 col-sm-6 col-md-4 col-xl-3">
            <x-ui.kpi-card 
                title="Present Today" 
                value="{{ number_format($presentToday) }}" 
                subtitle="Verified present today" 
                badgeColor="#0284C7" 
                badgeBg="#E0F2FE" 
                trend="{{ $attendanceRate }}% Rate" 
                trendType="{{ $attendanceRate >= 75 ? 'up' : 'down' }}" 
                actionLabel="Logs" 
                actionUrl="{{ route('attendance.index') }}" />
        </div>

        <!-- 4. Absent Today -->
        <div class="col-12 col-sm-6 col-md-4 col-xl-3">
            <x-ui.kpi-card 
                title="Absent Today" 
                value="{{ number_format($absentToday) }}" 
                subtitle="Unverified presence today" 
                badgeColor="#EF4444" 
                badgeBg="#FEE2E2" 
                trend="{{ $absentToday }} Staff" 
                trendType="{{ $absentToday == 0 ? 'up' : 'down' }}" 
                actionLabel="Log" 
                actionUrl="{{ route('attendance.index') }}" />
        </div>

        <!-- 4. Employees on Leave -->
        <div class="col-12 col-sm-6 col-md-4 col-xl-3">
            <x-ui.kpi-card 
                title="Employees on Leave" 
                value="{{ number_format($onLeave) }}" 
                subtitle="Active authorized leave" 
                badgeColor="#D97706" 
                badgeBg="#FEF3C7" 
                trend="{{ $onLeave }} Active" 
                trendType="neutral" 
                actionLabel="Approvals" 
                actionUrl="{{ route('leave.approvals') }}" />
        </div>

        <!-- 5. Pending Leave Requests -->
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

        <!-- 6. Departments -->
        <div class="col-12 col-sm-6 col-md-4 col-xl-3">
            <x-ui.kpi-card 
                title="Departments" 
                value="{{ number_format($departmentCount) }}" 
                subtitle="Operational divisions" 
                badgeColor="#7C3AED" 
                badgeBg="#F3E8FF" 
                trend="2 Units" 
                trendType="neutral" 
                actionLabel="Manage" 
                actionUrl="{{ route('departments.index') }}" />
        </div>

        <!-- 7. New Employees -->
        <div class="col-12 col-sm-6 col-md-4 col-xl-3">
            <x-ui.kpi-card 
                title="New Employees" 
                value="{{ number_format($newHiresThisMonth) }}" 
                subtitle="Joined this month" 
                badgeColor="#059669" 
                badgeBg="#D1FAE5" 
                :avatars="$newHireAvatars" 
                actionLabel="Details" 
                actionUrl="{{ route('employees.index') }}" />
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
                actionUrl="{{ route('reports.index') }}" />
        </div>
    </div>

    <!-- Middle Section: Attendance Charts & Analytics -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="card-title mb-0 text-navy fw-bold"><i class="bi bi-graph-up-arrow me-2 text-primary"></i>Attendance Overview & Analytics</h5>
                        <small class="text-muted" style="font-size: 11.5px;">Workforce presence trends and departmental distribution</small>
                    </div>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm w-auto border bg-light text-muted fw-semibold">
                            <option selected>Monthly</option>
                            <option>Weekly</option>
                            <option>Department</option>
                        </select>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4 align-items-center">
                        <div class="col-12 col-md-7">
                            <div class="position-relative" style="height: 280px; width: 100%;">
                                <canvas id="kpiMetricsChart"></canvas>
                            </div>
                        </div>
                        <div class="col-12 col-md-5 border-start-md ps-md-4">
                            <div class="position-relative d-flex align-items-center justify-content-center mx-auto" style="width: 170px; height: 170px;">
                                <canvas id="attendanceSummaryChart"></canvas>
                                <div class="position-absolute text-center">
                                    <div class="fs-3 fw-bold text-navy lh-1">{{ $attendanceRate }}%</div>
                                    <small class="text-muted fw-semibold" style="font-size:10.5px;">{{ $attendanceStatusLabel }}</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-around w-100 mt-3 text-center" style="font-size:11.5px;">
                                <div><span class="d-inline-block rounded-circle me-1" style="width:10px; height:10px; background-color:#2563EB;"></span><span class="text-muted fw-semibold">Present</span></div>
                                <div><span class="d-inline-block rounded-circle me-1" style="width:10px; height:10px; background-color:#F59E0B;"></span><span class="text-muted fw-semibold">Leave</span></div>
                                <div><span class="d-inline-block rounded-circle me-1" style="width:10px; height:10px; background-color:#EF4444;"></span><span class="text-muted fw-semibold">Absent</span></div>
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
            <!-- Recent Employees -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 text-navy fw-bold"><i class="bi bi-people me-2 text-primary"></i>Recent Employees</h5>
                    <a href="{{ route('employees.index') }}" class="btn btn-sm btn-link p-0 text-decoration-none">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($recentEmployees as $emp)
                        <div class="list-group-item px-3 py-3 d-flex align-items-center gap-3 border-bottom">
                            @if($emp->photo_url)
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

            <!-- Upcoming Birthdays -->
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 text-navy fw-bold"><i class="bi bi-gift-fill me-2 text-warning"></i>Upcoming Birthdays</h5>
                    <span class="badge bg-gold text-white px-2 py-1" style="font-size: 11px;">Next 14 Days</span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($birthdays as $emp)
                        <div class="list-group-item px-3 py-3 d-flex align-items-center gap-3 border-bottom">
                            @if($emp->photo_url)
                            <img src="{{ $emp->photo_url }}" alt="{{ $emp->full_name }}" class="rounded-circle border" style="width:38px; height:38px; object-fit:cover;">
                            @else
                            <div class="rounded-circle bg-navy text-white d-flex align-items-center justify-content-center fw-bold border" style="width:38px; height:38px; font-size:13px;">
                                {{ strtoupper(substr($emp->first_name,0,1) . substr($emp->last_name,0,1)) }}
                            </div>
                            @endif
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

        <!-- COLUMN TWO: Audit Logs & Tasks Checklist -->
        <div class="col-12 col-lg-4">
            <!-- Things To Do Checklist -->
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

            <!-- Recent Activity Audit Logs -->
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

        <!-- COLUMN THREE: Leave Calendar, Holidays & Quick Statistics -->
        <div class="col-12 col-lg-4">
            <!-- Staff on Leave Calendar Feed -->
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
                                @if($leave->employee?->photo_url)
                                <img src="{{ $leave->employee->photo_url }}" alt="Photo" class="rounded-circle" style="width:38px; height:38px; object-fit:cover;">
                                @else
                                <div class="bg-warning-subtle text-warning-emphasis rounded-circle d-flex align-items-center justify-content-center fw-bold border" style="width:38px; height:38px; font-size:12px;">
                                    {{ strtoupper(substr($leave->employee?->first_name ?? 'E',0,1) . substr($leave->employee?->last_name ?? 'S',0,1)) }}
                                </div>
                                @endif
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

            <!-- Quick HR Statistics -->
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="card-title mb-0 text-navy fw-bold"><i class="bi bi-pie-chart-fill me-2 text-info"></i>Quick HR Statistics</h5>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small">Active Personnel Rate</span>
                        <span class="fw-bold text-success">100%</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small">Monthly Work Hours</span>
                        <span class="fw-bold text-navy">{{ $totalMonthlyWorkHoursFormatted }} hrs</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small">Avg Hours / Staff</span>
                        <span class="fw-bold text-navy">{{ $avgMonthlyWorkHoursPerEmployee }} hrs</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2">
                        <span class="text-muted small">Pending Approvals</span>
                        <span class="fw-bold text-warning">{{ $pendingLeaveCount }} Requests</span>
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
                            <input type="text" wire:model.blur="newTaskTitle" class="form-control @error('newTaskTitle') is-invalid @enderror" placeholder="e.g. Review Attendance Logs">
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
document.addEventListener('DOMContentLoaded', initCharts);
document.addEventListener('livewire:navigated', initCharts);

function initCharts() {
    initAttendanceChart();
    initKpiChart();
}

function initAttendanceChart() {
    const ctx = document.getElementById('attendanceSummaryChart');
    if (!ctx) return;

    if (window.attendanceChartInstance) {
        window.attendanceChartInstance.destroy();
    }

    window.attendanceChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Present', 'On Leave', 'Absent'],
            datasets: [{
                data: [{{ $presentToday }}, {{ $onLeave }}, {{ $absentToday }}],
                backgroundColor: ['#2563EB', '#F59E0B', '#EF4444'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            cutout: '78%',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: true }
            }
        }
    });
}

function initKpiChart() {
    const ctx = document.getElementById('kpiMetricsChart');
    if (!ctx) return;

    if (window.kpiChartInstance) {
        window.kpiChartInstance.destroy();
    }

    window.kpiChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['July', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [
                {
                    label: 'Good',
                    data: [75, 50, 86, 25, 75, 90],
                    backgroundColor: '#22C55E',
                    borderRadius: 4,
                    barPercentage: 0.7,
                    categoryPercentage: 0.6
                },
                {
                    label: 'Moderate',
                    data: [50, 25, 60, 72, 64, 75],
                    backgroundColor: '#2563EB',
                    borderRadius: 4,
                    barPercentage: 0.7,
                    categoryPercentage: 0.6
                },
                {
                    label: 'Critical',
                    data: [25, 35, 50, 5, 35, 50],
                    backgroundColor: '#F97316',
                    borderRadius: 4,
                    barPercentage: 0.7,
                    categoryPercentage: 0.6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y + '%';
                        }
                    }
                }
            },
            scales: {
                y: {
                    min: 0,
                    max: 100,
                    ticks: {
                        stepSize: 25,
                        callback: function(value) { return value + '%'; }
                    },
                    grid: { color: '#f1f5f9' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
}
</script>
@endpush
