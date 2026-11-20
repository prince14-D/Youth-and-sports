@extends('layouts.app')
@section('title', 'Edit ' . $department->name)
@section('page-title', 'Edit Department')

@section('content')
<div class="animate-in" style="max-width:700px;margin:0 auto;">
    <x-ui.page-header :title="'Edit: ' . $department->name">
        <x-slot:actions>
            <a href="{{ route('departments.index') }}" class="btn btn-outline-navy"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </x-slot:actions>
    </x-ui.page-header>
    <x-ui.card title="Department Details" icon="bi bi-diagram-3">
            <form method="POST" action="{{ route('departments.update', $department) }}">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Department Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $department->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $department->code) }}" style="text-transform:uppercase;" maxlength="20" required>
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $department->description) }}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Parent Department</label>
                        <select name="parent_id" class="form-select">
                            <option value="">None (Top-level)</option>
                            @foreach($parents as $parent)
                            <option value="{{ $parent->id }}" {{ old('parent_id', $department->parent_id) == $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Department Head</label>
                        <select name="head_employee_id" class="form-select @error('head_employee_id') is-invalid @enderror">
                            <option value="">Select Department Head (Optional)</option>
                            @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ old('head_employee_id', $department->head_employee_id) == $emp->id ? 'selected' : '' }}>
                                {{ $emp->full_name }} ({{ $emp->employee_no }})
                            </option>
                            @endforeach
                        </select>
                        @error('head_employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="is_active" class="form-select">
                            <option value="1" {{ old('is_active', $department->is_active ? '1' : '0') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('is_active', $department->is_active ? '1' : '0') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-12 d-flex gap-2 justify-content-end pt-2">
                        <a href="{{ route('departments.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-accent"><i class="bi bi-check-circle me-1"></i>Update Department</button>
                    </div>
                </div>
            </form>
    </x-ui.card>
</div>
@endsection
