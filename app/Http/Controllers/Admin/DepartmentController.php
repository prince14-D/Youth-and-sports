<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    public function index(): View
    {
        $departments = Department::with(['parent', 'head', 'children'])
            ->withCount(['employees as active_employee_count' => fn($q) => $q->where('employment_status','Active')])
            ->orderBy('name')
            ->get();

        return view('departments.index', compact('departments'));
    }

    public function create(): View
    {
        $parents = Department::active()->orderBy('name')->get();
        $employees = Employee::active()->orderBy('first_name')->get();
        return view('departments.create', compact('parents', 'employees'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'             => 'required|string|max:100',
            'code'             => 'required|string|max:20|unique:departments,code',
            'description'      => 'nullable|string|max:500',
            'parent_id'        => 'nullable|exists:departments,id',
            'head_employee_id' => 'nullable|exists:employees,id',
            'is_active'        => 'boolean',
        ]);
        $data['created_by'] = auth()->id();
        $dept = Department::create($data);

        AuditService::log('created', Department::class, $dept->id, [], $data, "Created department: {$dept->name}");

        return redirect()->route('departments.index')
            ->with('success', "Department '{$dept->name}' created successfully.");
    }

    public function show(Department $department): View
    {
        $department->load(['parent','head','children','employees' => fn($q) => $q->active()->with('user')]);
        return view('departments.show', compact('department'));
    }

    public function edit(Department $department): View
    {
        $parents = Department::active()->where('id','!=',$department->id)->orderBy('name')->get();
        $employees = Employee::active()->orderBy('first_name')->get();
        return view('departments.edit', compact('department', 'parents', 'employees'));
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $old = $department->toArray();
        $data = $request->validate([
            'name'             => 'required|string|max:100',
            'code'             => ['required','string','max:20', Rule::unique('departments','code')->ignore($department->id)],
            'description'      => 'nullable|string|max:500',
            'parent_id'        => ['nullable','exists:departments,id', Rule::notIn([$department->id])],
            'head_employee_id' => 'nullable|exists:employees,id',
            'is_active'        => 'boolean',
        ]);

        $department->update($data);
        AuditService::log('updated', Department::class, $department->id, $old, $data, "Updated department: {$department->name}");

        return redirect()->route('departments.index')
            ->with('success', "Department '{$department->name}' updated.");
    }

    public function destroy(Department $department): RedirectResponse
    {
        if ($department->employees()->active()->exists()) {
            return back()->with('error', "Cannot delete department with active employees. Reassign employees first.");
        }
        $name = $department->name;
        $department->delete();
        AuditService::log('deleted', Department::class, $department->id, [], [], "Deleted department: {$name}");
        return redirect()->route('departments.index')->with('success', "Department '{$name}' deleted.");
    }
}
