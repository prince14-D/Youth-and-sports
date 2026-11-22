<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContractRenewal;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HrDataAnalystDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $totalEmployees   = Employee::count();
        $activeEmployees  = Employee::active()->count();
        $permanentCount   = Employee::where('employment_type', 'Permanent')->count();
        $contractorCount  = Employee::where('employment_type', 'Contract')->count();
        $activeContractors = Employee::active()->where('employment_type', 'Contract')->count();

        $allContractors = Employee::with(['department', 'documents'])
            ->where('employment_type', 'Contract')
            ->orderByRaw('CASE WHEN contract_end_date IS NULL THEN 0 ELSE 1 END, contract_end_date ASC')
            ->get();

        $expiringContracts = $allContractors->filter(fn ($c) =>
            $c->employment_status === 'Active'
            && $c->contract_end_date
            && !$c->contract_end_date->isPast()
            && today()->diffInDays($c->contract_end_date) <= 60
        );

        $expiredContracts = $allContractors->filter(fn ($c) =>
            $c->employment_status === 'Active'
            && $c->contract_end_date
            && $c->contract_end_date->isPast()
        );

        $missingContractDates = $allContractors->filter(fn ($c) =>
            $c->employment_status === 'Active' && !$c->contract_end_date
        );

        $incompleteProfiles = Employee::with('department')
            ->active()
            ->where(function ($q) {
                $q->whereNull('phone')->orWhere('phone', '')
                  ->orWhereNull('bank_account')->orWhere('bank_account', '')
                  ->orWhereNull('tin_number')->orWhere('tin_number', '')
                  ->orWhere(function ($cq) {
                      $cq->where('employment_type', 'Contract')->whereNull('contract_end_date');
                  });
            })
            ->latest()
            ->take(8)
            ->get();

        $incompleteCount = Employee::active()
            ->where(function ($q) {
                $q->whereNull('phone')->orWhere('phone', '')
                  ->orWhereNull('bank_account')->orWhere('bank_account', '')
                  ->orWhereNull('tin_number')->orWhere('tin_number', '')
                  ->orWhere(function ($cq) {
                      $cq->where('employment_type', 'Contract')->whereNull('contract_end_date');
                  });
            })
            ->count();

        $recentRegistrations = Employee::with('department')
            ->latest()
            ->take(10)
            ->get();

        $newThisMonth = Employee::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $pendingRenewalsCount = ContractRenewal::where('status', 'Pending')->count();
        $departments = Department::active()->orderBy('name')->get();

        return view('hr-analyst.dashboard', compact(
            'totalEmployees',
            'activeEmployees',
            'permanentCount',
            'contractorCount',
            'activeContractors',
            'allContractors',
            'expiringContracts',
            'expiredContracts',
            'missingContractDates',
            'incompleteProfiles',
            'incompleteCount',
            'recentRegistrations',
            'newThisMonth',
            'pendingRenewalsCount',
            'departments'
        ));
    }

    public function quickUpdateContract(Request $request, Employee $employee): RedirectResponse
    {
        abort_unless($employee->employment_type === 'Contract', 422, 'Selected record is not a contractor.');

        $data = $request->validate([
            'department_id'     => 'required|exists:departments,id',
            'designation'       => 'required|string|max:150',
            'date_joined'       => 'required|date',
            'contract_end_date' => 'required|date|after:date_joined',
            'salary_amount_lrd' => 'nullable|numeric|min:0',
            'salary_amount_usd' => 'nullable|numeric|min:0',
            'employment_status' => 'required|in:Active,Inactive,Suspended,Terminated',
            'contract_file'     => 'nullable|file|max:5120|mimes:pdf,doc,docx,jpg,jpeg,png',
        ]);

        $old = $employee->toArray();

        $employee->update([
            'department_id'     => $data['department_id'],
            'designation'       => $data['designation'],
            'date_joined'       => $data['date_joined'],
            'contract_end_date' => $data['contract_end_date'],
            'salary_amount_lrd' => $data['salary_amount_lrd'] ?? $employee->salary_amount_lrd,
            'salary_amount_usd' => $data['salary_amount_usd'] ?? $employee->salary_amount_usd,
            'employment_status' => $data['employment_status'],
            'updated_by'        => auth()->id(),
        ]);

        if ($request->hasFile('contract_file')) {
            $file = $request->file('contract_file');
            $path = $file->store('employees/documents/' . $employee->id, 'public');
            EmployeeDocument::create([
                'employee_id'   => $employee->id,
                'document_type' => 'contract',
                'title'         => 'Contractor Agreement (' . now()->format('M Y') . ')',
                'file_path'     => $path,
                'file_size'     => round($file->getSize() / 1024, 1) . ' KB',
                'mime_type'     => $file->getMimeType(),
                'expiry_date'   => $data['contract_end_date'],
                'uploaded_by'   => auth()->id(),
            ]);
        }

        AuditService::log(
            'updated',
            Employee::class,
            $employee->id,
            $old,
            $employee->fresh()->toArray(),
            "Updated contractor contract details for {$employee->full_name} ({$employee->employee_no})"
        );

        return back()->with('success', "Contract details for {$employee->full_name} updated successfully.");
    }
}
