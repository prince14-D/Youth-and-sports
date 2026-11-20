@extends('layouts.app')
@section('title', 'Departments')
@section('page-title', 'Department Management')
@section('breadcrumb', 'HRMS › Departments')

@section('content')
<div class="animate-in">
    <x-ui.page-header
        title="Department Management"
        :subtitle="$departments->count() . ' departments in the ministry'"
    >
        <x-slot:actions>
            @can('create departments')
            <x-ui.button variant="accent" icon="bi bi-plus-lg" :href="route('departments.create')">
                Add Department
            </x-ui.button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card title="Ministry Departments" icon="bi bi-diagram-3" bodyClass="p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Department</th>
                        <th>Code</th>
                        <th>Parent</th>
                        <th class="text-center">Employees</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departments as $dept)
                    <tr>
                        <td>
                            <div style="font-weight:600;font-size:13.5px;">{{ $dept->name }}</div>
                            @if($dept->description)
                            <div style="font-size:11px;color:var(--text-muted);">{{ Str::limit($dept->description, 60) }}</div>
                            @endif
                        </td>
                        <td><code style="font-size:12px;background:#f0f2f7;padding:2px 6px;border-radius:4px;">{{ $dept->code }}</code></td>
                        <td style="font-size:13px;">{!! $departmentName = $dept->parent?->name ?? '<span class="text-muted">—</span>' !!}</td>
                        <td class="text-center">
                            <x-ui.badge variant="navy">{{ $dept->active_employee_count }}</x-ui.badge>
                        </td>
                        <td class="text-center">
                            @if($dept->is_active)
                            <x-ui.badge variant="success">Active</x-ui.badge>
                            @else
                            <x-ui.badge>Inactive</x-ui.badge>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <a href="{{ route('departments.show', $dept) }}" class="btn btn-sm" style="background:#f0f2f7;color:var(--navy);padding:4px 8px;" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @can('edit departments')
                                <a href="{{ route('departments.edit', $dept) }}" class="btn btn-sm btn-warning" style="padding:4px 8px;" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endcan
                                @can('delete departments')
                                <form method="POST" action="{{ route('departments.destroy', $dept) }}" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger" style="padding:4px 8px;"
                                        onclick="confirmDelete(this.closest('form'), '{{ addslashes($dept->name) }}')">
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
                                title="No departments found"
                                message="Departments you create will appear here."
                                icon="bi bi-diagram-3"
                                :actionLabel="\Illuminate\Support\Facades\Gate::allows('create departments') ? 'Add Department' : null"
                                actionRoute="{{ route('departments.create') }}"
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
