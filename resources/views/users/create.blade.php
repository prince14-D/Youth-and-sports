@extends('layouts.app')
@section('title', 'Add System User')
@section('page-title', 'Add System User')

@section('content')
<div class="animate-in" style="max-width:780px;margin:0 auto;">
    <x-ui.page-header title="Create User / Admin Account" subtitle="Provision a System Administrator, HR leadership, HR Officer, HR Data Analyst, Employee, or Contractor account.">
        <x-slot:actions>
            <a href="{{ route('users.index') }}" class="btn btn-outline-navy"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card title="Account Credentials & Role Assignment" icon="bi bi-shield-person">
        <form method="POST" action="{{ route('users.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Mary K. Johnson" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="name@mys.gov.lr" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Role <span class="text-danger">*</span></label>
                    <select name="role" id="roleSelect" class="form-select @error('role') is-invalid @enderror" required>
                        <option value="">Select Role</option>
                        @foreach($roles as $role)
                        <option value="{{ $role->name }}"
                            data-desc="{{ $roleDescriptions[$role->name] ?? '' }}"
                            {{ old('role', $preselectedRole) == $role->name ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div id="roleDescriptionBox" class="form-text text-primary mt-1 fw-medium"></div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Link to Employee / Contractor Profile</label>
                    <select name="employee_id" id="employeeSelect" class="form-select @error('employee_id') is-invalid @enderror">
                        <option value="">Standalone Account (Optional)</option>
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}"
                            data-name="{{ $emp->full_name }}"
                            data-email="{{ $emp->official_email }}"
                            {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->full_name }} ({{ $emp->employee_no }} &bull; {{ $emp->department?->code ?? 'N/A' }} &bull; {{ $emp->employment_type }})
                        </option>
                        @endforeach
                    </select>
                    @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Selecting an employee auto-fills their Full Name &amp; Official Email. Admins can also be linked to an employee profile!</div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="form-label mb-1">Password <span class="text-danger">*</span></label>
                        <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" onclick="generateStrongPassword()">
                            <i class="bi bi-key me-1"></i>Generate Strong Password
                        </button>
                    </div>
                    <div class="input-group">
                        <input type="password" name="password" id="passwordInput" class="form-control @error('password') is-invalid @enderror" required minlength="8">
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('passwordInput', this)" title="Show/Hide Password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" name="password_confirmation" id="passwordConfirmInput" class="form-control" required minlength="8">
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('passwordConfirmInput', this)" title="Show/Hide Password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Account Status</label>
                    <select name="is_active" class="form-select">
                        <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="force_password_change" value="1" id="forcePwdCheck" {{ old('force_password_change') ? 'checked' : '' }}>
                        <label class="form-check-label small" for="forcePwdCheck">
                            Require password change on first login
                        </label>
                    </div>
                </div>
                <div class="col-12 d-flex gap-2 justify-content-end pt-3 border-top">
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-accent"><i class="bi bi-check-circle me-1"></i>Create Account</button>
                </div>
            </div>
        </form>
    </x-ui.card>
</div>
@endsection

@push('scripts')
<script>
const roleSelect = document.getElementById('roleSelect');
const roleDescBox = document.getElementById('roleDescriptionBox');

function updateRoleDescription() {
    const opt = roleSelect.options[roleSelect.selectedIndex];
    roleDescBox.textContent = opt?.dataset?.desc || '';
}
roleSelect?.addEventListener('change', updateRoleDescription);
updateRoleDescription();

const employeeSelect = document.getElementById('employeeSelect');
employeeSelect?.addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    if (!opt || !opt.value) return;
    const nameInput = document.querySelector('input[name="name"]');
    const emailInput = document.querySelector('input[name="email"]');
    if (nameInput && opt.dataset.name) nameInput.value = opt.dataset.name;
    if (emailInput && opt.dataset.email) emailInput.value = opt.dataset.email;
});

function togglePasswordVisibility(id, btn) {
    const input = document.getElementById(id);
    if (!input) return;
    const isPwd = input.type === 'password';
    input.type = isPwd ? 'text' : 'password';
    btn.innerHTML = isPwd ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
}

function generateStrongPassword() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789!@#$%';
    let pwd = 'MYS@';
    for (let i = 0; i < 8; i++) {
        pwd += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    const p1 = document.getElementById('passwordInput');
    const p2 = document.getElementById('passwordConfirmInput');
    p1.value = pwd;
    p2.value = pwd;
    p1.type = 'text';
    p2.type = 'text';
}
</script>
@endpush
