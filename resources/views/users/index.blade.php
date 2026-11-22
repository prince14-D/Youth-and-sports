@extends('layouts.app')
@section('title', 'Users & Roles')
@section('page-title', 'Users & Access Control')
@section('breadcrumb', 'HRMS › Users & Roles')

@section('content')
<div class="animate-in">
    {{-- Executive Access Control Header --}}
    <div class="card border-0 shadow-sm mb-4 text-white" style="background: linear-gradient(135deg, #000a3d 0%, #0b1f4b 55%, #123f7a 100%); border-radius: 16px;">
        <div class="card-body p-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <div class="text-uppercase small fw-semibold opacity-75 mb-1" style="letter-spacing:1.1px;">
                    <i class="bi bi-shield-lock-fill text-warning me-1"></i> Role-Based Access Control (RBAC)
                </div>
                <h4 class="fw-bold mb-1">Users, Roles &amp; Governance Center</h4>
                <p class="mb-0 small opacity-75">
                    Manage accounts, link System Administrators &amp; division heads to employee profiles, and review role permissions across all {{ $roleCounts['total_roles'] }} Ministry roles.
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @can('create employees')
                <a href="{{ route('employees.create') }}" class="btn btn-outline-light btn-sm fw-semibold">
                    <i class="bi bi-person-vcard me-1"></i>Register Employee + Role
                </a>
                @endcan
                @can('create users')
                <div class="dropdown">
                    <button class="btn btn-warning btn-sm text-dark fw-semibold dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-lightning-charge-fill me-1"></i>Quick Provision Role
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="min-width:240px; font-size:13px;">
                        @role('System Administrator')
                        <li><a class="dropdown-item py-2" href="{{ route('users.create', ['role' => 'System Administrator']) }}"><i class="bi bi-shield-plus text-danger me-2"></i>System Administrator</a></li>
                        <li><a class="dropdown-item py-2" href="{{ route('users.create', ['role' => 'Minister']) }}"><i class="bi bi-building-check text-navy me-2"></i>Minister</a></li>
                        <li><a class="dropdown-item py-2" href="{{ route('users.create', ['role' => 'Deputy Minister for Administration']) }}"><i class="bi bi-shield-check text-primary me-2"></i>Deputy Minister (DMA)</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2" href="{{ route('users.create', ['role' => 'Finance Officer']) }}"><i class="bi bi-bank2 text-primary me-2"></i>Finance Officer</a></li>
                        <li><a class="dropdown-item py-2" href="{{ route('users.create', ['role' => 'Auditor']) }}"><i class="bi bi-shield-exclamation text-danger me-2"></i>Internal Auditor</a></li>
                        <li><a class="dropdown-item py-2" href="{{ route('users.create', ['role' => 'Procurement Officer']) }}"><i class="bi bi-cart-check text-warning me-2"></i>Procurement Officer</a></li>
                        <li><a class="dropdown-item py-2" href="{{ route('users.create', ['role' => 'Asset Manager']) }}"><i class="bi bi-box-seam text-success me-2"></i>Asset Manager</a></li>
                        <li><a class="dropdown-item py-2" href="{{ route('users.create', ['role' => 'Stadium Manager']) }}"><i class="bi bi-trophy text-info me-2"></i>Stadium Manager</a></li>
                        <li><hr class="dropdown-divider"></li>
                        @endrole
                        <li><a class="dropdown-item py-2" href="{{ route('users.create', ['role' => 'Human Resource Director']) }}"><i class="bi bi-person-badge text-navy me-2"></i>HR Director</a></li>
                        <li><a class="dropdown-item py-2" href="{{ route('users.create', ['role' => 'Assistant HR Director']) }}"><i class="bi bi-person-badge text-primary me-2"></i>Assistant HR Director</a></li>
                        <li><a class="dropdown-item py-2" href="{{ route('users.create', ['role' => 'HR Officer']) }}"><i class="bi bi-person-check text-success me-2"></i>HR Officer</a></li>
                        <li><a class="dropdown-item py-2" href="{{ route('users.create', ['role' => 'HR Data Analyst']) }}"><i class="bi bi-database-gear text-info me-2"></i>HR Data Analyst</a></li>
                    </ul>
                </div>
                <a href="{{ route('users.create') }}" class="btn btn-light btn-sm fw-semibold text-navy">
                    <i class="bi bi-person-plus-fill me-1"></i>Add User
                </a>
                @endcan
            </div>
        </div>
    </div>

    {{-- Executive KPI Strip --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small">Active Accounts</div>
                        <div class="fs-3 fw-bold text-navy">{{ $roleCounts['active'] }}<span class="fs-6 text-muted fw-normal">/{{ $roleCounts['total_users'] }}</span></div>
                    </div>
                    <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                        <i class="bi bi-people-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small">Admins &amp; Division Heads</div>
                        <div class="fs-3 fw-bold text-danger">{{ $roleCounts['admins'] + $roleCounts['executives'] }}</div>
                        <div class="small text-muted">{{ $roleCounts['admins'] }} Admin(s) &bull; {{ $roleCounts['executives'] }} Division</div>
                    </div>
                    <div class="rounded-circle bg-danger-subtle text-danger d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                        <i class="bi bi-shield-lock-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small">HR Team &amp; Analysts</div>
                        <div class="fs-3 fw-bold text-info">{{ $roleCounts['hr_team'] }}</div>
                        <div class="small text-muted">3-Tier Approval Pipeline</div>
                    </div>
                    <div class="rounded-circle bg-info-subtle text-info d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                        <i class="bi bi-diagram-3-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small">Staff &amp; Contractors</div>
                        <div class="fs-3 fw-bold text-success">{{ $roleCounts['staff'] }}</div>
                        <div class="small text-muted">{{ $roleCounts['total_roles'] }} Defined System Roles</div>
                    </div>
                    <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                        <i class="bi bi-person-vcard-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Navigation Tabs: User Accounts vs Roles & Permissions Governance Matrix --}}
    <ul class="nav nav-pills mb-3 gap-2">
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'users' ? 'active' : 'bg-white border text-dark' }}" href="{{ route('users.index', ['tab' => 'users']) }}">
                <i class="bi bi-people me-1"></i>User Accounts Directory ({{ $roleCounts['total_users'] }})
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'roles' ? 'active' : 'bg-white border text-dark' }}" href="{{ route('users.index', ['tab' => 'roles']) }}">
                <i class="bi bi-shield-check me-1"></i>Roles, Dashboards &amp; Permissions Matrix ({{ $roleCounts['total_roles'] }})
            </a>
        </li>
    </ul>

    @if($tab === 'users')
    {{-- Search & Filter Card --}}
    <x-ui.card class="mb-4" bodyClass="p-3">
        <form method="GET" action="{{ route('users.index') }}">
            <input type="hidden" name="tab" value="users">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold text-muted mb-1">Search User Accounts</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0 bg-light" placeholder="Name, email, employee ID, position...">
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Filter by Role</label>
                    <select name="role" class="form-select form-select-sm bg-light">
                        <option value="">All User Roles</option>
                        @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>{{ $role->name }} ({{ $role->users_count }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Account Status</label>
                    <select name="status" class="form-select form-select-sm bg-light">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Deactivated</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-navy btn-sm flex-fill">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                    @if(request('search') || request('role') || request('status'))
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </x-ui.card>

    {{-- Users List Table Card --}}
    <x-ui.card title="System User Accounts" icon="bi bi-shield-lock" bodyClass="p-0">
        <x-slot:actions>
            <x-ui.badge variant="navy">{{ $users->total() }} Accounts</x-ui.badge>
        </x-slot:actions>

        @if($users->hasPages())
        <x-slot:footer>
            {{ $users->links('pagination::bootstrap-5') }}
        </x-slot:footer>
        @endif

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr style="font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.5px;">
                        <th class="ps-4 py-3 text-muted">User &amp; Email</th>
                        <th class="py-3 text-muted">Employee Profile &amp; Department</th>
                        <th class="py-3 text-muted">System Role &amp; Dashboard</th>
                        <th class="py-3 text-center text-muted">Status</th>
                        <th class="pe-4 py-3 text-end text-muted">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td class="ps-4 py-3">
                            <div class="d-flex align-items-center gap-3">
                                @if($user->employee?->photo_url)
                                <img src="{{ $user->employee->photo_url }}" alt="{{ $user->name }}" class="emp-avatar">
                                @else
                                <div class="emp-avatar-placeholder">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                @endif
                                <div>
                                    <div class="fw-bold text-navy" style="font-size: 13.5px;">{{ $user->name }}</div>
                                    <div class="text-muted small" style="font-size: 12px;">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 small">
                            @if($user->employee)
                            <a href="{{ route('employees.show', $user->employee) }}" class="text-navy text-decoration-none fw-semibold d-block">
                                <i class="bi bi-person-vcard text-primary me-1"></i>{{ $user->employee->employee_no }} &bull; {{ $user->employee->designation }}
                            </a>
                            <span class="text-muted" style="font-size:11.5px;">{{ $user->employee->department?->name ?? 'Unassigned' }} ({{ $user->employee->employment_type }})</span>
                            @if($user->employee->responsibilities)
                                <div class="text-muted fst-italic mt-0.5" style="font-size:11px; max-width:260px;" title="{{ $user->employee->responsibilities }}">
                                    {{ \Illuminate\Support\Str::limit($user->employee->responsibilities, 65) }}
                                </div>
                            @endif
                            @else
                            <span class="badge bg-light text-muted border">Standalone Account</span>
                            @endif
                        </td>
                        <td class="py-3 small">
                            <x-ui.badge variant="role">{{ $user->primary_role }}</x-ui.badge>
                            <div class="text-muted mt-1" style="font-size:11px;">
                                <i class="bi bi-window-sidebar me-1"></i>{{ $roleDashboards[$user->primary_role] ?? 'Standard Dashboard' }}
                            </div>
                        </td>
                        <td class="text-center">
                            @if($user->is_active)
                            <x-ui.badge variant="success">Active</x-ui.badge>
                            @else
                            <x-ui.badge variant="danger">Deactivated</x-ui.badge>
                            @endif
                        </td>
                        <td class="pe-4 py-3 text-end text-nowrap">
                            <div class="d-flex justify-content-end gap-1">
                                @can('edit users')
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-navy" title="Edit Role / Account">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('users.toggle-status', $user) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $user->is_active ? 'btn-outline-warning text-dark' : 'btn-outline-success' }}" title="{{ $user->is_active ? 'Deactivate Account' : 'Activate Account' }}">
                                        <i class="bi {{ $user->is_active ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                    </button>
                                </form>
                                @endif
                                @endcan
                                @can('delete users')
                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('users.destroy', $user) }}" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Delete User"
                                        onclick="confirmDelete(this.closest('form'), '{{ addslashes($user->name) }}')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5">
                            <x-ui.empty-state
                                title="No user accounts found"
                                message="No user accounts matched your search criteria."
                                icon="bi bi-shield-slash"
                            />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
    @endif

    {{-- TAB 2: Roles, Dashboards & Permissions Governance Matrix --}}
    @if($tab === 'roles')
    @foreach($roleTiers as $tierName => $tierRoleNames)
    <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-bold text-navy mb-0"><i class="bi bi-layers-fill text-primary me-2"></i>{{ $tierName }}</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light text-uppercase">
                    <tr>
                        <th class="ps-4" style="width:22%;">Role Title</th>
                        <th style="width:18%;">Dedicated Dashboard</th>
                        <th style="width:36%;">Official Responsibilities &amp; Access Scope</th>
                        <th class="text-center" style="width:10%;">Users</th>
                        <th class="text-end pe-4" style="width:14%;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tierRoleNames as $rName)
                        @php $roleObj = $rolesByName->get($rName); @endphp
                        @if($roleObj)
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="fw-bold text-navy">{{ $roleObj->name }}</div>
                                <span class="badge bg-light text-muted border mt-1">{{ $roleObj->permissions->count() }} permissions</span>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                    {{ $roleDashboards[$roleObj->name] ?? 'Main Dashboard' }}
                                </span>
                            </td>
                            <td>
                                <div class="text-dark">{{ $roleDescriptions[$roleObj->name] ?? 'Ministry role' }}</div>
                                <details class="mt-1">
                                    <summary class="text-primary small cursor-pointer" style="font-size:11px;">View assigned permissions</summary>
                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                        @foreach($roleObj->permissions->sortBy('name') as $perm)
                                            <span class="badge bg-light text-secondary border" style="font-size:10px;">{{ $perm->name }}</span>
                                        @endforeach
                                    </div>
                                </details>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('users.index', ['tab' => 'users', 'role' => $roleObj->name]) }}" class="badge bg-navy text-white text-decoration-none px-2.5 py-1.5">
                                    {{ $roleObj->users_count }} user(s)
                                </a>
                            </td>
                            <td class="text-end pe-4 text-nowrap">
                                @can('create users')
                                @if($roleObj->name !== 'System Administrator' || auth()->user()->hasRole('System Administrator'))
                                <a href="{{ route('users.create', ['role' => $roleObj->name]) }}" class="btn btn-sm btn-outline-navy">
                                    <i class="bi bi-person-plus me-1"></i>Assign
                                </a>
                                @endif
                                @endcan
                            </td>
                        </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
    @endif
</div>
@endsection
