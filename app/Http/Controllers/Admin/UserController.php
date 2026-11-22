<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public const ROLE_DESCRIPTIONS = [
        'System Administrator'               => 'Full system administration: user/role provisioning, system settings, SMTP, audit trail & security.',
        'Minister'                           => 'Office of the Minister: executive oversight of all departments, live document approvals/feedback & daily operations.',
        'Deputy Minister for Administration' => 'DMA (Head of Administration): approves HR, Finance, Asset & Procurement requests and executive memos.',
        'Human Resource Director'            => 'Executive HR leadership: final Stage-3 approvals for leave, payroll, movements, renewals & HR governance.',
        'Assistant HR Director'              => 'Deputy HR leadership: Stage-2 approvals, HR operations oversight, recruitment & staffing reports.',
        'HR Officer'                         => 'Day-to-day HR operations: employee records, Stage-1 verifications, recruitment, leave/excuses & communications.',
        'HR Data Analyst'                    => 'Dedicated data-entry workspace: register employees, contractor agreements & bulk CSV roster imports.',
        'Asset Manager'                      => 'Asset Management Division: government property, vehicles, equipment, inventory, maintenance & GSA exports.',
        'Payroll Officer'                    => 'Generates and manages dual-currency (LRD/USD) payroll runs, allowances/deductions & payslips.',
        'Finance Officer'                    => 'Finance Division: budgets, payment vouchers, accounting ledger, payroll disbursements & financial reports.',
        'Director/Manager'                   => 'Departmental oversight, leave/excuse approvals, document workflow & staff performance appraisals.',
        'Department Head'                    => 'Manages department staff, leave/excuse approvals, document workflow, appraisals & training.',
        'Supervisor'                         => 'Unit supervision, leave/excuse review & performance appraisals.',
        'Auditor'                            => 'Internal Audit Division: verifies vouchers, scans irregularities, assesses internal controls & escalates findings.',
        'Procurement Officer'                => 'Procurement Unit: tenders, bidding, bid evaluation (70/30), supplier registry & compliance monitoring.',
        'Stadium Manager'                    => 'Stadium Management: facilities, event readiness checklists, maintenance work orders & safety inspections.',
        'Employee'                           => 'Permanent staff self-service portal: leave requests, excuses, payslips, ID card, profile & vacancies.',
        'Contractor'                         => 'Contractor self-service portal: contract timeline, renewal requests, payment slips, ID card & excuses.',
    ];

    public const ROLE_DASHBOARDS = [
        'System Administrator'               => 'Super Admin Dashboard',
        'Minister'                           => "Minister's Office Dashboard",
        'Deputy Minister for Administration' => 'DMA Command Center',
        'Human Resource Director'            => 'HR Executive Dashboard',
        'Assistant HR Director'              => 'HR Executive Dashboard',
        'HR Officer'                         => 'HR Operations Dashboard',
        'HR Data Analyst'                    => 'HR Data Analyst Desk',
        'Asset Manager'                      => 'Asset Management Dashboard',
        'Finance Officer'                    => 'Finance Division Dashboard',
        'Payroll Officer'                    => 'Payroll & HR Dashboard',
        'Auditor'                            => 'Internal Audit Command Center',
        'Procurement Officer'                => 'Procurement Unit Dashboard',
        'Stadium Manager'                    => 'Stadium Management Dashboard',
        'Department Head'                    => 'Departmental Dashboard',
        'Director/Manager'                   => 'Departmental Dashboard',
        'Supervisor'                         => 'Supervisor Dashboard',
        'Employee'                           => 'Employee Self-Service Portal',
        'Contractor'                         => 'Contractor Self-Service Portal',
    ];

    public const ROLE_TIERS = [
        'Executive Leadership'         => ['Minister', 'Deputy Minister for Administration', 'System Administrator'],
        'Human Resources & Data'       => ['Human Resource Director', 'Assistant HR Director', 'HR Officer', 'HR Data Analyst'],
        'Finance, Audit & Procurement' => ['Finance Officer', 'Payroll Officer', 'Auditor', 'Procurement Officer'],
        'Operations & Departments'     => ['Asset Manager', 'Stadium Manager', 'Director/Manager', 'Department Head', 'Supervisor'],
        'Staff & Contractors'          => ['Employee', 'Contractor'],
    ];

    private function assignableRoles()
    {
        $query = Role::orderBy('name');
        $auth = auth()->user();

        if ($auth && !$auth->hasRole('System Administrator') && !$auth->can('assign roles')) {
            $query->where('name', '!=', 'System Administrator');
        }

        return $query->get();
    }

    public function index(Request $request)
    {
        $tab = $request->query('tab', 'users');
        $query = User::with(['roles', 'employee.department']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('employee', function ($eq) use ($search) {
                      $eq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('employee_no', 'like', "%{$search}%")
                        ->orWhere('designation', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', function ($rq) use ($request) {
                $rq->where('name', $request->role);
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->orderBy('name')->paginate(20)->withQueryString();
        $roles = Role::with('permissions')->withCount('users')->orderBy('name')->get();
        $rolesByName = $roles->keyBy('name');

        $roleCounts = [
            'total_users' => User::count(),
            'active'      => User::where('is_active', true)->count(),
            'admins'      => User::role('System Administrator')->count(),
            'executives'  => User::role(['Minister', 'Deputy Minister for Administration', 'Finance Officer', 'Auditor', 'Procurement Officer', 'Asset Manager', 'Stadium Manager'])->count(),
            'hr_team'     => User::role(['Human Resource Director', 'Assistant HR Director', 'HR Officer', 'HR Data Analyst'])->count(),
            'staff'       => User::role(['Employee', 'Contractor'])->count(),
            'total_roles' => $roles->count(),
        ];

        $roleDescriptions = self::ROLE_DESCRIPTIONS;
        $roleDashboards = self::ROLE_DASHBOARDS;
        $roleTiers = self::ROLE_TIERS;

        return view('users.index', compact(
            'users',
            'roles',
            'rolesByName',
            'roleCounts',
            'roleDescriptions',
            'roleDashboards',
            'roleTiers',
            'tab'
        ));
    }

    public function create(Request $request)
    {
        $roles = $this->assignableRoles();
        $employees = Employee::active()
            ->with('department')
            ->doesntHave('user')
            ->orderBy('first_name')
            ->get();
        $preselectedRole = $request->query('role', '');
        $roleDescriptions = self::ROLE_DESCRIPTIONS;

        return view('users.create', compact('roles', 'employees', 'preselectedRole', 'roleDescriptions'));
    }

    public function store(Request $request)
    {
        $allowedRoles = $this->assignableRoles()->pluck('name')->toArray();

        $data = $request->validate([
            'name'                  => 'required|string|max:100',
            'email'                 => 'required|email|unique:users,email',
            'employee_id'           => 'nullable|exists:employees,id|unique:users,employee_id',
            'password'              => 'required|string|min:8|confirmed',
            'role'                  => ['required', Rule::in($allowedRoles)],
            'is_active'             => 'boolean',
            'force_password_change' => 'nullable|boolean',
        ]);

        $user = User::create([
            'name'                  => $data['name'],
            'email'                 => $data['email'],
            'employee_id'           => $data['employee_id'] ?? null,
            'password'              => Hash::make($data['password']),
            'is_active'             => (bool) ($data['is_active'] ?? true),
            'force_password_change' => (bool) ($data['force_password_change'] ?? false),
        ]);
        $user->assignRole($data['role']);

        AuditService::log('created', User::class, $user->id, [], ['email' => $user->email, 'role' => $data['role']], "Created user: {$user->name} ({$data['role']})");

        return redirect()->route('users.index')
            ->with('success', "Account '{$user->name}' ({$user->email}) created and assigned role: {$data['role']}.");
    }

    public function edit(User $user)
    {
        if ($user->hasRole('System Administrator') && !auth()->user()->hasRole('System Administrator')) {
            abort(403, 'Only a System Administrator can modify another System Administrator account.');
        }

        $roles = $this->assignableRoles();
        $employees = Employee::active()
            ->with('department')
            ->where(function ($q) use ($user) {
                $q->doesntHave('user')->orWhereHas('user', fn ($sq) => $sq->where('id', $user->id));
            })
            ->orderBy('first_name')
            ->get();
        $roleDescriptions = self::ROLE_DESCRIPTIONS;

        return view('users.edit', compact('user', 'roles', 'employees', 'roleDescriptions'));
    }

    public function update(Request $request, User $user)
    {
        if ($user->hasRole('System Administrator') && !auth()->user()->hasRole('System Administrator')) {
            abort(403, 'Only a System Administrator can modify another System Administrator account.');
        }

        $allowedRoles = $this->assignableRoles()->pluck('name')->toArray();
        $old = $user->toArray();

        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'email'       => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'employee_id' => ['nullable', 'exists:employees,id', Rule::unique('users', 'employee_id')->ignore($user->id)],
            'role'        => ['required', Rule::in($allowedRoles)],
            'is_active'   => 'boolean',
            'password'    => 'nullable|string|min:8|confirmed',
        ]);

        $updateData = [
            'name'        => $data['name'],
            'email'       => $data['email'],
            'employee_id' => $data['employee_id'] ?? null,
            'is_active'   => (bool) ($data['is_active'] ?? true),
        ];
        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);
        $user->syncRoles([$data['role']]);

        if (auth()->id() === $user->id) {
            auth()->setUser($user->fresh());
        }

        AuditService::log('updated', User::class, $user->id, $old, $updateData, "Updated user: {$user->name} ({$data['role']})");

        return redirect()->route('users.index')->with('success', "User '{$user->name}' updated.");
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }
        if ($user->hasRole('System Administrator') && !auth()->user()->hasRole('System Administrator')) {
            abort(403, 'Only a System Administrator can change another System Administrator status.');
        }

        $user->update(['is_active' => !$user->is_active]);
        $state = $user->is_active ? 'activated' : 'deactivated';

        AuditService::log('updated', User::class, $user->id, [], ['is_active' => $user->is_active], "Account {$state}: {$user->name}");

        return back()->with('success', "Account '{$user->name}' has been {$state}.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        if ($user->hasRole('System Administrator') && !auth()->user()->hasRole('System Administrator')) {
            abort(403, 'Only a System Administrator can delete a System Administrator account.');
        }
        $name = $user->name;
        $user->delete();
        AuditService::log('deleted', User::class, $user->id, [], [], "Deleted user: {$name}");
        return redirect()->route('users.index')->with('success', "User '{$name}' deleted.");
    }
}
