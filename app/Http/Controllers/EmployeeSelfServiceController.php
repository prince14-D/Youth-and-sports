<?php

namespace App\Http\Controllers;

use App\Models\PayrollRecord;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EmployeeSelfServiceController extends Controller
{
    public function profile(Request $request): View
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403, 'Employee record not found for this account.');
        return view('employee.profile', compact('employee'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403, 'Employee record not found for this account.');

        $data = $request->validate([
            'phone' => 'nullable|string|max:20',
            'personal_email' => 'nullable|email|max:150',
            'address' => 'nullable|string|max:500',
            'marital_status' => 'nullable|in:Single,Married,Divorced,Widowed',
            'photo' => 'nullable|image|max:5120',
        ]);
        if ($request->hasFile('photo')) {
            if ($employee->photo_path) Storage::disk('public')->delete($employee->photo_path);
            $data['photo_path'] = $request->file('photo')->store('employees/photos', 'public');
        }
        $employee->update($data);
        return back()->with('success', 'Your contact details were updated successfully.');
    }

    public function payslips(Request $request): View
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403, 'Employee record not found for this account.');

        $selectedYear = $request->query('year');
        $allPayslips = PayrollRecord::with('period')
            ->where('employee_id', $employee->id)
            ->latest()
            ->get();

        $availableYears = $allPayslips
            ->map(fn ($p) => $p->period?->start_date?->year ?? $p->created_at?->year)
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();

        $payslips = $selectedYear
            ? $allPayslips->filter(fn ($p) => (string) ($p->period?->start_date?->year ?? $p->created_at?->year) === (string) $selectedYear)
            : $allPayslips;

        $summary = [
            'count'        => $payslips->count(),
            'latest'       => $payslips->first(),
            'total_net_lrd' => $payslips->sum('net_pay_lrd'),
            'total_net_usd' => $payslips->sum('net_pay_usd'),
            'total_ded_lrd' => $payslips->sum(fn ($p) => (float) $p->tax_lrd + (float) $p->pension_lrd + (float) $p->deductions_lrd),
        ];

        return view('employee.payslips', compact('employee', 'payslips', 'availableYears', 'selectedYear', 'summary'));
    }

    public function profilePdf(Request $request)
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403, 'Employee record not found for this account.');
        $employee->load(['department', 'supervisor', 'education', 'certifications', 'nextOfKin', 'emergencyContacts', 'user']);
        $photoFile = $employee->photo_path ? public_path("storage/{$employee->photo_path}") : null;
        $profilePhoto = ($photoFile && is_file($photoFile))
            ? 'data:' . (mime_content_type($photoFile) ?: 'image/png') . ';base64,' . base64_encode(file_get_contents($photoFile))
            : null;
        return Pdf::loadView('employees.profile-pdf', compact('employee', 'profilePhoto'))
            ->setPaper('a4')
            ->download("employee-profile-{$employee->employee_no}.pdf");
    }
}
