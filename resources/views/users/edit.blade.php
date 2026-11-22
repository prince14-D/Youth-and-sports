@extends('layouts.app')
@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')
<div class="animate-in" style="max-width:700px;margin:0 auto;">
    <x-ui.page-header :title="'Edit User: ' . $user->name">
        <x-slot:actions>
            <a href="{{ route('users.index') }}" class="btn btn-outline-navy"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card title="User Details" icon="bi bi-shield-person">
            <form method="POST" action="{{ route('users.update', $user) }}">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Link to Employee Profile</label>
                        <select name="employee_id" class="form-select @error('employee_id') is-invalid @enderror">
                            <option value="">Select Employee (Optional)</option>
                            @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ old('employee_id', $user->employee_id) == $emp->id ? 'selected' : '' }}>
                                {{ $emp->full_name }} ({{ $emp->employee_no }})
                            </option>
                            @endforeach
                        </select>
                        @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" id="editRoleSelect" class="form-select @error('role') is-invalid @enderror" required>
                            <option value="">Select Role</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->name }}" data-desc="{{ $roleDescriptions[$role->name] ?? '' }}" {{ old('role', $user->roles->first()?->name) == $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                            @endforeach
                        </select>
                        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div id="editRoleDesc" class="form-text text-primary mt-1 fw-medium">{{ $roleDescriptions[$user->roles->first()?->name ?? ''] ?? '' }}</div>
                    </div>

                    <div class="col-12 mt-4 pt-3 border-top">
                        <h6 style="font-size:13px;font-weight:700;color:var(--text-muted);margin-bottom:12px;">CHANGE PASSWORD (LEAVE BLANK IF NO CHANGE)</h6>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="is_active" class="form-select">
                            <option value="1" {{ old('is_active', $user->is_active ? '1' : '0') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('is_active', $user->is_active ? '1' : '0') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-12 d-flex gap-2 justify-content-end pt-2">
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-accent"><i class="bi bi-check-circle me-1"></i>Update User</button>
                    </div>
                </div>
            </form>
    </x-ui.card>
</div>
@endsection
