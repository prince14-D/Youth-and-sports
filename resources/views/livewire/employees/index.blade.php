<div class="animate-in">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3 fw-bold text-navy mb-1">Employee Registry</h1>
            <p class="text-muted mb-0 small">Search, filter, and manage all Ministry personnel records.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('employees.import') }}" class="btn btn-outline-navy">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i>Bulk Import (CSV)
            </a>
            <a href="{{ route('employees.create') }}" class="btn btn-accent">
                <i class="bi bi-person-plus-fill me-1"></i>Register Employee
            </a>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="row g-3 align-items-center">
                <div class="col-12 col-md-5">
                    <label class="form-label small fw-bold text-muted mb-1">Search Registry</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 bg-light" placeholder="Search by name, ID, or position...">
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Department</label>
                    <select wire:model.live="departmentId" class="form-select bg-light">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Employment Status</label>
                    <select wire:model.live="status" class="form-select bg-light">
                        <option value="">All Statuses</option>
                        <option value="Active">Active</option>
                        <option value="On Leave">On Leave</option>
                        <option value="Suspended">Suspended</option>
                        <option value="Retired">Retired</option>
                        <option value="Resigned">Resigned</option>
                        <option value="Terminated">Terminated</option>
                    </select>
                </div>
                <div class="col-12 col-md-1 d-flex align-items-end">
                    <button type="button" wire:click="$set('search', ''); $set('departmentId', ''); $set('status', '');" class="btn btn-light w-100 border text-muted" title="Reset Filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee Registry Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 text-navy fw-bold"><i class="bi bi-people-fill me-2 text-primary"></i>Personnel Records</h5>
            <span class="badge bg-navy px-3 py-2" style="font-size: 12px;">{{ $employees->total() }} Employees</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                            <th class="ps-4 py-3 text-muted" style="cursor:pointer;" wire:click="sortBy('employee_no')">
                                Employee ID @if($sortField === 'employee_no') <i class="bi bi-sort-{{ $sortDirection }}"></i> @endif
                            </th>
                            <th class="py-3 text-muted" style="cursor:pointer;" wire:click="sortBy('first_name')">
                                Full Name @if($sortField === 'first_name') <i class="bi bi-sort-{{ $sortDirection }}"></i> @endif
                            </th>
                            <th class="py-3 text-muted">Department</th>
                            <th class="py-3 text-muted">Position / Job Title</th>
                            <th class="py-3 text-center text-muted">Status</th>
                            <th class="pe-4 py-3 text-end text-muted">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $emp)
                        <tr>
                            <td class="ps-4 py-3">
                                <span class="badge font-monospace fw-bold px-2 py-1" style="font-size: 12.5px; color: #dc2626 !important; background-color: #fee2e2 !important; border: 1px solid #fca5a5 !important;">
                                    {{ $emp->employee_no }}
                                </span>
                                @if($emp->personnel_number)
                                <div class="small font-monospace mt-1 fw-bold" style="font-size: 11px; color: #dc2626 !important;">Ref: {{ $emp->personnel_number }}</div>
                                @endif
                            </td>
                            <td class="py-3">
                                <div class="d-flex align-items-center gap-3">
                                    @if($emp->photo_url)
                                    <img src="{{ $emp->photo_url }}" alt="{{ $emp->full_name }}" class="rounded-circle border" style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                    <div class="bg-navy text-white rounded-circle d-flex align-items-center justify-content-center fw-bold border" style="width: 40px; height: 40px; font-size: 13px;">
                                        {{ strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1)) }}
                                    </div>
                                    @endif
                                    <div>
                                        <div class="fw-bold text-navy" style="font-size: 14px;">{{ $emp->full_name }}</div>
                                        <small class="text-muted" style="font-size: 12px;">{{ $emp->official_email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td style="font-size: 13.5px;" class="text-navy font-semibold">
                                {{ $emp->department?->name ?? '—' }}
                            </td>
                            <td style="font-size: 13.5px; color: #dc2626 !important; font-weight: 600;">
                                {{ $emp->designation }}
                            </td>
                            <td class="text-center">
                                @if($emp->employment_status == 'Active')
                                <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle rounded-pill px-3 py-2" style="font-size: 11.5px;">Active</span>
                                @elseif($emp->employment_status == 'On Leave')
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-2" style="font-size: 11.5px;">On Leave</span>
                                @elseif($emp->employment_status == 'Suspended')
                                <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle rounded-pill px-3 py-2" style="font-size: 11.5px;">Suspended</span>
                                @else
                                <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle rounded-pill px-3 py-2" style="font-size: 11.5px;">{{ $emp->employment_status }}</span>
                                @endif
                            </td>
                            <td class="pe-4 py-3 text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('employees.show', $emp) }}" class="btn btn-sm btn-outline-navy" title="View Profile">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('employees.edit', $emp) }}" class="btn btn-sm btn-outline-navy" title="Edit Profile">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" wire:click="deleteEmployee({{ $emp->id }})" wire:confirm="Are you sure you want to delete this employee record?" class="btn btn-sm btn-outline-danger" title="Delete Employee">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-people fs-1 d-block mb-2 text-muted opacity-50"></i>
                                <span class="fw-semibold">No employee records found matching your filters.</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($employees->hasPages())
        <div class="card-footer bg-transparent border-top p-3">
            {{ $employees->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>
