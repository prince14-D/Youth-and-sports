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
        'System Administrator'               => 'Full unrestricted system control, settings, audit logs, and admin/user creation.',
        'Minister'                           => 'Office of the Minister: executive oversight of all departments, daily activity & ministry-wide documents.',
        'Deputy Minister for Administration' => 'DMA (Head of Administration): approves HR, Finance, Asset & Procurement requests and routes executive memos.',
        'Human Resource Director'            => 'Executive HR leadership: approves leave, excuses, payroll, appraisals, movements & renewals.',
        'Assistant HR Director'    => 'Deputy HR leadership: supports Director across HR operations, approvals, reports & staffing.',
        'HR Officer'               => 'Day-to-day HR operations: employee records, recruitment, leave/excuses, communications & assets.',
        'HR Data Analyst'                    => 'Dedicated data-entry workspace: register employees, register contractor contracts & bulk imports.',
        'Asset Manager'                      => 'Asset Management Division: dedicated dashboard to manage government property, vehicles, equipment, inventory & maintenance.',
        'Payroll Officer'          => 'Generates and manages payroll cycles, salary grades, and payslips.',
        'Finance Officer'          => 'Finance Division: dedicated dashboard for budgets, payment vouchers, accounting ledger & payroll disbursements.',
        'Director/Manager'         => 'Departmental oversight, leave/excuse approvals, and staff performance appraisals.',
        'Department Head'          => 'Manages department staff, leave/excuse approvals, appraisals, and training.',
        'Supervisor'               => 'Unit supervision, leave/excuse review, and performance appraisals.',
        'Auditor'                  => 'Internal Audit Division: dedicated dashboard to review financial records, verify vouchers, assess internal controls & flag irregularities.',
        'Procurement Officer'      => 'Procurement Unit: dedicated dashboard for tenders, bidding, bid evaluation, supplier registry & procurement compliance.',
        'Stadium Manager'          => 'Stadium Management: dedicated dashboard for stadium facilities, event preparation checklists, maintenance work orders & safety inspections.',
        'Employee'                 => 'Permanent/staff self-service portal: leave, excuses, payslips, profile, and internal vacancies.',
        'Contractor'               => 'Contractor self-service portal: contract timeline, renewals, payment slips, and excuses.',
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
        $query = User::with(['roles', 'employee.department']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('employee', function ($eq) use ($search) {
                      $eq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('employee_no', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', function ($rq) use ($request) {
                $rq->where('name', $request->role);
            });
        }

        $users = $query->orderBy('name')->paginate(20)->withQueryString();
        $roles = Role::orderBy('name')->get();

        $roleCounts = [
            'admins'       => User::role('System Administrator')->count(),
            'hr_team'      => User::role(['Human Resource Director', 'Assistant HR Director', 'HR Officer', 'HR Data Analyst'])->count(),
            'employees'    => User::role('Employee')->count(),
            'contractors'  => User::role('Contractor')->count(),
        ];

        return view('users.index', compact('users', 'roles', 'roleCounts'));
    }

    public function create(Request $request)
    {
        $roles = $this->assignableRoles();
        $employees = Employee::active()
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
