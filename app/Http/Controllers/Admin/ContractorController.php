<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContractorController extends Controller
{
    public function index(Request $request): View
    {
        $query = Employee::query()->where('employment_type', 'Contract');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('employee_no', 'like', "%{$search}%")
                    ->orWhere('designation', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('employment_status', $request->status);
        }

        $contractors = $query->with('department')->latest()->paginate(20);
        $totalContractors = Employee::where('employment_type', 'Contract')->count();
        $activeContractors = Employee::where('employment_type', 'Contract')->where('employment_status', 'Active')->count();
        $inactiveContractors = $totalContractors - $activeContractors;

        return view('contractors.index', compact('contractors', 'totalContractors', 'activeContractors', 'inactiveContractors'));
    }

    public function show(Employee $employee): View
    {
        abort_unless($employee->employment_type === 'Contract', 404, 'Contractor record not found.');

        $employee->load([
            'department', 'supervisor:id,first_name,last_name,designation',
            'education', 'certifications', 'nextOfKin',
            'emergencyContacts', 'documents', 'user',
        ]);

        return view('contractors.show', compact('employee'));
    }
}
