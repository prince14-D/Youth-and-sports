@extends('layouts.app')
@section('title', $department->name . ' — Details')
@section('page-title', $department->name)
@section('breadcrumb', 'HRMS › Departments › ' . $department->code)

@section('content')
<div class="animate-in">
    <!-- Header -->
    <div class="page-header">
        <div>
            <h1>{{ $department->name }} ({{ $department->code }})</h1>
            <p>
                Status:
                @if($department->is_active)
                    <x-ui.badge variant="success">Active</x-ui.badge>
                @else
                    <x-ui.badge>Inactive</x-ui.badge>
                @endif
                &bull; Parent Department: 
                @if($department->parent)
                    <a href="{{ route('departments.show', $department->parent) }}" style="color: var(--navy); font-weight: 600;">
                        {{ $department->parent->name }}
                    </a>
                @else
                    <span class="text-muted">None (Top-level)</span>
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            @can('edit departments')
            <a href="{{ route('departments.edit', $department) }}" class="btn btn-navy">
                <i class="bi bi-pencil me-1"></i>Edit Department
            </a>
            @endcan
            <a href="{{ route('departments.index') }}" class="btn btn-outline-navy">
                <i class="bi bi-arrow-left me-1"></i>Back to Departments
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Details Card -->
        <div class="col-12 col-lg-4">
            <x-ui.card title="Department Info" icon="bi bi-info-circle" class="h-100">
                    <div class="mb-4">
                        <label class="form-label text-muted text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Description</label>
                        <div style="font-size: 13.5px; color: var(--text); line-height: 1.5;">
                            {{ $department->description ?? 'No description provided.' }}
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Department Head</label>
                        <div style="font-size: 13.5px; font-weight: 600;">
                            @if($department->head)
                                <a href="{{ route('employees.show', $department->head) }}" style="color: var(--navy);">
                                    {{ $department->head->full_name }}
                                </a>
                            @else
                                <span class="text-muted" style="font-weight: normal;">Not assigned</span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Sub-departments</label>
                        <div class="d-flex flex-column gap-2 mt-1">
                            @forelse($department->children as $child)
                                <a href="{{ route('departments.show', $child) }}" class="p-2 rounded border text-decoration-none d-flex justify-content-between align-items-center" style="background-color: #f8fafc; font-size: 13px; color: var(--text);">
                                    <span>{{ $child->name }}</span>
                                    <i class="bi bi-chevron-right text-muted" style="font-size: 10px;"></i>
                                </a>
                            @empty
                                <span class="text-muted" style="font-size: 13px;">No child departments</span>
                            @endforelse
                        </div>
                    </div>
            </x-ui.card>
        </div>

        <!-- Employees List Card -->
        <div class="col-12 col-lg-8">
            <x-ui.card :title="'Assigned Employees (' . $department->employees->count() . ')'" icon="bi bi-people" class="h-100" bodyClass="p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                    <th class="ps-4 py-3" style="font-size: 12px; font-weight: 700; color: #475569;">Employee</th>
                                    <th class="py-3" style="font-size: 12px; font-weight: 700; color: #475569;">Employee No.</th>
                                    <th class="py-3" style="font-size: 12px; font-weight: 700; color: #475569;">Designation</th>
                                    <th class="pe-4 py-3 text-center" style="font-size: 12px; font-weight: 700; color: #475569; width: 15%;">Profile</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($department->employees as $emp)
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center gap-2">
                                            @if($emp->photo_path)
                                                <img src="{{ $emp->photo_url }}" class="emp-avatar" alt="Avatar">
                                            @else
                                                <div class="emp-avatar-placeholder">
                                                    {{ strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1)) }}
                                                </div>
                                            @endif
                                            <div>
                                                <div style="font-weight: 600; font-size: 13.5px; color: #1e293b;">
                                                    {{ $emp->full_name }}
                                                </div>
                                                <div style="font-size: 11px; color: #64748b;">
                                                    {{ $emp->official_email ?? $emp->personal_email ?? '—' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <code style="font-size: 12px; background-color: #f1f5f9; padding: 2px 6px; border-radius: 4px;">
                                            {{ $emp->employee_no }}
                                        </code>
                                    </td>
                                    <td style="font-size: 13px; color: #334155;">{{ $emp->designation }}</td>
                                    <td class="pe-4 py-3 text-center">
                                        <a href="{{ route('employees.show', $emp) }}" class="btn btn-sm btn-outline-navy" style="font-size: 12px; font-weight: 500;">
                                            View
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="bi bi-people fs-3 d-block mb-2"></i>
                                        No employees currently assigned to this department.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
            </x-ui.card>
        </div>
    </div>
</div>
@endsection
