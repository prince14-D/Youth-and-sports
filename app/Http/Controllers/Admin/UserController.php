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
    public function index(Request $request)
    {
        $query = User::with(['roles', 'employee.department']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('employee', function($eq) use ($search) {
                      $eq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('employee_no', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', function($rq) use ($request) {
                $rq->where('name', $request->role);
            });
        }

        $users = $query->orderBy('name')->paginate(20)->withQueryString();
        $roles = Role::orderBy('name')->get();

        return view('users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles     = Role::orderBy('name')->get();
        $employees = Employee::active()
            ->doesntHave('user')
            ->orderBy('first_name')
            ->get();
        return view('users.create', compact('roles', 'employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'email'       => 'required|email|unique:users,email',
            'employee_id' => 'nullable|exists:employees,id|unique:users,employee_id',
            'password'    => 'required|string|min:8|confirmed',
            'role'        => 'required|exists:roles,name',
            'is_active'   => 'boolean',
        ]);

        $user = User::create([
            'name'        => $data['name'],
            'email'       => $data['email'],
            'employee_id' => $data['employee_id'] ?? null,
            'password'    => Hash::make($data['password']),
            'is_active'   => $data['is_active'] ?? true,
            'force_password_change' => true,
        ]);
        $user->assignRole($data['role']);

        AuditService::log('created', User::class, $user->id, [], ['email' => $user->email, 'role' => $data['role']], "Created user: {$user->name}");

        return redirect()->route('users.index')
            ->with('success', "User '{$user->name}' created and assigned role: {$data['role']}.");
    }

    public function edit(User $user)
    {
        $roles     = Role::orderBy('name')->get();
        $employees = Employee::active()
            ->where(function ($q) use ($user) {
                $q->doesntHave('user')->orWhereHas('user', fn($sq) => $sq->where('id', $user->id));
            })
            ->orderBy('first_name')
            ->get();
        return view('users.edit', compact('user', 'roles', 'employees'));
    }

    public function update(Request $request, User $user)
    {
        $old = $user->toArray();
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'email'       => ['required','email', Rule::unique('users','email')->ignore($user->id)],
            'employee_id' => ['nullable','exists:employees,id', Rule::unique('users','employee_id')->ignore($user->id)],
            'role'        => 'required|exists:roles,name',
            'is_active'   => 'boolean',
            'password'    => 'nullable|string|min:8|confirmed',
        ]);

        $updateData = [
            'name'        => $data['name'],
            'email'       => $data['email'],
            'employee_id' => $data['employee_id'] ?? null,
            'is_active'   => $data['is_active'] ?? true,
        ];
        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);
        $user->syncRoles([$data['role']]);

        if (auth()->id() === $user->id) {
            auth()->setUser($user->fresh());
        }

        AuditService::log('updated', User::class, $user->id, $old, $updateData, "Updated user: {$user->name}");

        return redirect()->route('users.index')->with('success', "User '{$user->name}' updated.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        $name = $user->name;
        $user->delete();
        AuditService::log('deleted', User::class, $user->id, [], [], "Deleted user: {$name}");
        return redirect()->route('users.index')->with('success', "User '{$name}' deleted.");
    }
}
