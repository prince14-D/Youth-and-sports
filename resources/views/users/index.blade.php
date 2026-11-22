@extends('layouts.app')
@section('title', 'Users & Roles')
@section('page-title', 'Users & Access Control')
@section('breadcrumb', 'HRMS › Users')

@section('content')
<div class="animate-in">
    <x-ui.page-header
        title="System Users & Roles"
        subtitle="Manage Administrator, HR leadership, HR Officer, HR Data Analyst, Employee, and Contractor accounts."
    >
        <x-slot:actions>
            @can('create users')
            @role('System Administrator')
            <a href="{{ route('users.create', ['role' => 'System Administrator']) }}" class="btn btn-navy">
                <i class="bi bi-shield-plus me-1"></i>Create Admin Account
            </a>
            <a href="{{ route('users.create', ['role' => 'Asset Manager']) }}" class="btn btn-outline-success">
                <i class="bi bi-box-seam me-1"></i>Add Asset Manager
            </a>
            <a href="{{ route('users.create', ['role' => 'Finance Officer']) }}" class="btn btn-outline-primary">
                <i class="bi bi-bank2 me-1"></i>Add Finance Officer
            </a>
            <a href="{{ route('users.create', ['role' => 'Auditor']) }}" class="btn btn-outline-danger">
                <i class="bi bi-shield-exclamation me-1"></i>Add Internal Auditor
            </a>
            <a href="{{ route('users.create', ['role' => 'Procurement Officer']) }}" class="btn btn-outline-warning">
                <i class="bi bi-cart-check me-1"></i>Add Procurement Officer
            </a>
            <a href="{{ route('users.create', ['role' => 'Stadium Manager']) }}" class="btn btn-outline-info">
                <i class="bi bi-trophy me-1"></i>Add Stadium Manager
            </a>
            @endrole
            <a href="{{ route('users.create', ['role' => 'HR Officer']) }}" class="btn btn-outline-navy">
                <i class="bi bi-person-badge me-1"></i>Add HR Officer
            </a>
            <x-ui.button variant="accent" icon="bi bi-person-plus" :href="route('users.create')">
                Add System User
            </x-ui.button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    {{-- Role Summary Strip --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small">System Admins</div>
                        <div class="fs-4 fw-bold text-navy">{{ $roleCounts['admins'] ?? 0 }}</div>
                    </div>
                    <div class="rounded-circle bg-danger-subtle text-danger d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                        <i class="bi bi-shield-lock-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small">HR Team &amp; Analyst</div>
                        <div class="fs-4 fw-bold text-primary">{{ $roleCounts['hr_team'] ?? 0 }}</div>
                    </div>
                    <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                        <i class="bi bi-people-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small">Employee Accounts</div>
                        <div class="fs-4 fw-bold text-success">{{ $roleCounts['employees'] ?? 0 }}</div>
                    </div>
                    <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                        <i class="bi bi-person-vcard-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small">Contractor Accounts</div>
                        <div class="fs-4 fw-bold text-warning">{{ $roleCounts['contractors'] ?? 0 }}</div>
                    </div>
                    <div class="rounded-circle bg-warning-subtle text-warning d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                        <i class="bi bi-file-earmark-person-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Search & Filter Card --}}
    <x-ui.card class="mb-4" bodyClass="p-3">
        <form method="GET" action="{{ route('users.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-5">
                    <label class="form-label small fw-bold text-muted mb-1">Search User Accounts</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0 bg-light" placeholder="Search by name, email, employee ID...">
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                    <label class="form-label small fw-bold text-muted mb-1">Filter by Role</label>
                    <select name="role" class="form-select bg-light">
                        <option value="">All User Roles</option>
                        @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-navy flex-fill">
                        <i class="bi bi-search me-1"></i>Search
                    </button>
                    @if(request('search') || request('role'))
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary" title="Reset Search">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </x-ui.card>

    {{-- Users List Table Card --}}
    <x-ui.card title="Users List" icon="bi bi-shield-lock" bodyClass="p-0">
        <x-slot:actions>
            <x-ui.badge variant="navy">{{ $users->total() }} Total Accounts</x-ui.badge>
        </x-slot:actions>

        @if($users->hasPages())
        <x-slot:footer>
            {{ $users->links('pagination::bootstrap-5') }}
        </x-slot:footer>
        @endif

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                        <th class="ps-4 py-3 text-muted">User</th>
                        <th class="py-3 text-muted">Email</th>
                        <th class="py-3 text-muted">Linked Employee</th>
                        <th class="py-3 text-muted">Primary Role</th>
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
                                    <div class="fw-bold text-navy" style="font-size: 14px;">{{ $user->name }}</div>
                                    <small class="text-muted" style="font-size: 11.5px;">ID: #{{ $user->id }}</small>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 text-navy font-semibold" style="font-size: 13.5px;">{{ $user->email }}</td>
                        <td class="py-3" style="font-size: 13.5px;">
                            @if($user->employee)
                            <a href="{{ route('employees.show', $user->employee) }}" class="text-navy text-decoration-none fw-semibold">
                                <i class="bi bi-person-vcard me-1"></i>{{ $user->employee->full_name }} ({{ $user->employee->employee_no }})
                            </a>
                            @else
                            <x-ui.badge>Not linked</x-ui.badge>
                            @endif
                        </td>
                        <td class="py-3">
                            <x-ui.badge variant="role">{{ $user->primary_role }}</x-ui.badge>
                        </td>
                        <td class="text-center">
                            @if($user->is_active)
                            <x-ui.badge variant="success">Active</x-ui.badge>
                            @else
                            <x-ui.badge variant="danger">Deactivated</x-ui.badge>
                            @endif
                        </td>
                        <td class="pe-4 py-3 text-end">
                            <div class="d-flex justify-content-end gap-1">
                                @can('edit users')
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-navy" title="Edit User">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endcan
                                @can('delete users')
                                <form method="POST" action="{{ route('users.destroy', $user) }}" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Delete User"
                                        onclick="confirmDelete(this.closest('form'), '{{ addslashes($user->name) }}')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
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
</div>
@endsection
