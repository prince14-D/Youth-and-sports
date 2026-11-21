<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Services\AuditService;
use App\Services\EmployeeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function __construct(private readonly EmployeeService $employeeService) {}

    public function index(Request $request): View
    {
        $departments = Department::active()->orderBy('name')->get();
        $filters = $request->only(['department_id', 'employment_status', 'employment_type', 'gender', 'search']);
        $employees = $this->employeeService->getForDataTable($filters);
        $stats = $this->employeeService->getStats();

        return view('employees.index', compact('employees', 'departments', 'filters', 'stats'));
    }

    public function create(): View
    {
        $departments = Department::active()->orderBy('name')->get();
        $supervisors = Employee::active()->orderBy('first_name')->get();
        return view('employees.create', compact('departments', 'supervisors'));
    }

    public function import(): View
    {
        return view('employees.import');
    }

    public function store(EmployeeRequest $request): RedirectResponse
    {
        $employee = $this->employeeService->create(
            $request->validated(),
            $request->file('photo')
        );

        AuditService::log('created', Employee::class, $employee->id,
            [], $employee->toArray(),
            "Created employee: {$employee->full_name} ({$employee->employee_no})"
        );

        return redirect()->route('employees.show', $employee)
            ->with('success', "Employee {$employee->full_name} ({$employee->employee_no}) registered successfully.");
    }

    public function show(Employee $employee): View
    {
        $employee->load([
            'department', 'supervisor:id,first_name,last_name,designation',
            'education', 'certifications', 'nextOfKin',
            'emergencyContacts', 'documents', 'user',
        ]);
        return view('employees.show', compact('employee'));
    }

    public function downloadPdf(Employee $employee)
    {
        $employee->load([
            'department', 'supervisor:id,first_name,last_name,designation',
            'education', 'certifications', 'nextOfKin',
            'emergencyContacts', 'user',
        ]);

        $photoFile = $employee->photo_path
            ? public_path("storage/{$employee->photo_path}")
            : null;
        $profilePhoto = ($photoFile && is_file($photoFile))
            ? 'data:' . (mime_content_type($photoFile) ?: 'image/png') . ';base64,' . base64_encode(file_get_contents($photoFile))
            : null;

        return Pdf::loadView('employees.profile-pdf', compact('employee', 'profilePhoto'))
            ->setPaper('a4')
            ->download("employee-profile-{$employee->employee_no}.pdf");
    }

    public function edit(Employee $employee): View
    {
        return view('employees.edit', compact('employee'));
    }

    public function update(EmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $old = $employee->toArray();
        $this->employeeService->update($employee, $request->validated(), $request->file('photo'));

        AuditService::log('updated', Employee::class, $employee->id,
            $old, $employee->fresh()->toArray(),
            "Updated employee: {$employee->full_name}"
        );

        return redirect()->route('employees.show', $employee)
            ->with('success', 'Employee record updated successfully.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $name = $employee->full_name;
        $no   = $employee->employee_no;
        $employee->delete();

        AuditService::log('deleted', Employee::class, $employee->id, [],
            [], "Deleted employee: {$name} ({$no})"
        );

        return redirect()->route('employees.index')
            ->with('success', "Employee {$name} has been removed.");
    }

    // ── AJAX: employee search for autocomplete ────────────────────────
    public function search(Request $request)
    {
        $q = $request->get('q', '');
        $employees = Employee::active()
            ->where(function ($query) use ($q) {
                $query->where('first_name', 'like', "%{$q}%")
                    ->orWhere('last_name', 'like', "%{$q}%")
                    ->orWhere('employee_no', 'like', "%{$q}%");
            })
            ->select('id', 'employee_no', 'first_name', 'last_name', 'designation')
            ->limit(10)
            ->get()
            ->map(fn($e) => [
                'id'   => $e->id,
                'text' => "{$e->employee_no} — {$e->full_name} ({$e->designation})",
            ]);

        return response()->json($employees);
    }

    public function storeDocument(Request $request, Employee $employee): RedirectResponse
    {
        $request->validate([
            'title'         => 'required|string|max:100',
            'document_type' => 'required|string|max:50',
            'expiry_date'   => 'nullable|date',
            'file'          => 'required|file|max:5120|mimes:pdf,doc,docx,jpg,jpeg,png,xls,xlsx',
        ]);

        $file = $request->file('file');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('employees/documents/' . $employee->id, $filename, 'public');

        $doc = $employee->documents()->create([
            'document_type' => $request->document_type,
            'title'         => $request->title,
            'file_path'     => $path,
            'file_size'     => $this->formatBytes($file->getSize()),
            'mime_type'     => $file->getMimeType(),
            'expiry_date'   => $request->expiry_date,
            'uploaded_by'   => auth()->id(),
        ]);

        AuditService::log('created', \App\Models\EmployeeDocument::class, $doc->id,
            [], $doc->toArray(),
            "Uploaded document '{$doc->title}' for employee: {$employee->full_name}"
        );

        return back()->with('success', "Document '{$doc->title}' uploaded successfully.");
    }

    public function destroyDocument(Employee $employee, $documentId): RedirectResponse
    {
        $doc = $employee->documents()->findOrFail($documentId);
        $title = $doc->title;

        // Delete actual file
        \Illuminate\Support\Facades\Storage::disk('public')->delete($doc->file_path);
        
        $doc->delete();

        AuditService::log('deleted', \App\Models\EmployeeDocument::class, $documentId,
            [], [],
            "Deleted document '{$title}' for employee: {$employee->full_name}"
        );

        return back()->with('success', "Document '{$title}' has been deleted.");
    }

    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
