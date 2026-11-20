<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayrollPeriod;
use App\Models\PayrollRecord;
use App\Models\Employee;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PayrollController extends Controller
{
    public function index(): View
    {
        $periods = PayrollPeriod::with('creator')
            ->withCount('records')
            ->orderBy('start_date', 'desc')
            ->get();

        return view('payroll.index', compact('periods'));
    }

    public function dashboard(): View
    {
        $latestPeriod = PayrollPeriod::with('records.employee.department')->latest('start_date')->first();
        $records = $latestPeriod?->records ?? collect();
        $employees = Employee::active()->with('department')->get();

        $metrics = [
            'gross_lrd' => $records->sum(fn ($record) => (float) $record->basic_salary_lrd + (float) $record->allowances_lrd + (float) $record->overtime_lrd),
            'net_lrd' => $records->sum('net_pay_lrd'),
            'net_usd' => $records->sum('net_pay_usd'),
            'deductions_lrd' => $records->sum(fn ($record) => (float) $record->tax_lrd + (float) $record->pension_lrd + (float) $record->deductions_lrd),
            'employees_paid' => $records->whereIn('status', ['Approved', 'Paid'])->count(),
            'pending_approval' => PayrollPeriod::whereIn('status', ['Draft', 'Processing'])->count(),
        ];

        $departmentTotals = $records->groupBy(fn ($record) => $record->employee?->department?->name ?? 'Unassigned')
            ->map(fn ($group) => $group->sum('net_pay_lrd'))->sortDesc();
        $employmentTotals = $records->groupBy(fn ($record) => $record->employee?->employment_type ?? 'Other')
            ->map(fn ($group) => $group->sum('net_pay_lrd'))->sortDesc();
        $contractAlerts = $employees->where('employment_type', 'Contract')
            ->filter(fn ($employee) => !$employee->contract_end_date || $employee->contract_end_date->isPast() || $employee->contract_end_date->diffInDays(today()) <= 60)
            ->sortBy('contract_end_date')->take(8);

        return view('payroll.dashboard', compact('latestPeriod', 'metrics', 'departmentTotals', 'employmentTotals', 'contractAlerts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $period = PayrollPeriod::create([
            'name'       => $request->name,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'status'     => 'Draft',
            'created_by' => auth()->id(),
        ]);

        // Process active employees
        $employees = Employee::active()->get();

        foreach ($employees as $employee) {
            $basicLrd = $employee->salary_amount_lrd ?? 50000.00;
            $basicUsd = $employee->salary_amount_usd ?? 250.00;

            // Calculations:
            // Allowances = 10% of basic
            $allowLrd = $basicLrd * 0.10;
            $allowUsd = $basicUsd * 0.10;

            // Pension = 4.5% of basic (Civil service rate)
            $pensionLrd = $basicLrd * 0.045;

            // Tax = 10% of basic
            $taxLrd = $basicLrd * 0.10;

            // Net Pay = Basic + Allowances - Pension - Tax
            $netLrd = $basicLrd + $allowLrd - $pensionLrd - $taxLrd;
            // USD Net Pay (assuming allowances only, or direct mapping)
            $netUsd = $basicUsd + $allowUsd;

            PayrollRecord::create([
                'payroll_period_id' => $period->id,
                'employee_id'       => $employee->id,
                'basic_salary_lrd'  => $basicLrd,
                'basic_salary_usd'  => $basicUsd,
                'allowances_lrd'    => $allowLrd,
                'allowances_usd'    => $allowUsd,
                'overtime_lrd'      => 0,
                'deductions_lrd'    => 0,
                'tax_lrd'           => $taxLrd,
                'pension_lrd'       => $pensionLrd,
                'net_pay_lrd'       => $netLrd,
                'net_pay_usd'       => $netUsd,
                'status'            => 'Draft'
            ]);
        }

        AuditService::log('created', PayrollPeriod::class, $period->id,
            [], $period->toArray(),
            "Generated payroll period: {$period->name} with " . $employees->count() . " records"
        );

        return redirect()->route('payroll.index')
            ->with('success', "Payroll period '{$period->name}' created and processed in Draft mode.");
    }

    public function show($id): View
    {
        $period = PayrollPeriod::findOrFail($id);
        $records = PayrollRecord::with('employee.department')
            ->where('payroll_period_id', $id)
            ->get();

        $totals = [
            'lrd' => $records->sum('net_pay_lrd'),
            'usd' => $records->sum('net_pay_usd'),
        ];

        return view('payroll.show', compact('period', 'records', 'totals'));
    }

    public function review($id): RedirectResponse
    {
        $period = PayrollPeriod::findOrFail($id);
        abort_if($period->status !== 'Draft', 422, 'Only draft payroll can be sent for review.');
        $period->update(['status' => 'Processing', 'reviewed_by' => auth()->id(), 'reviewed_at' => now()]);
        PayrollRecord::where('payroll_period_id', $id)->update(['status' => 'Approved']);
        AuditService::log('updated', PayrollPeriod::class, $period->id, [], $period->fresh()->toArray(), "Sent payroll period {$period->name} for review");
        return back()->with('success', "Payroll period '{$period->name}' is now In Review.");
    }

    public function approve($id): RedirectResponse
    {
        $period = PayrollPeriod::findOrFail($id);
        $old = $period->toArray();

        abort_if(!in_array($period->status, ['Processing', 'Approved'], true), 422, 'Payroll must be In Review before approval.');
        $period->update(['status' => 'Approved', 'reviewed_by' => auth()->id(), 'reviewed_at' => now()]);
        PayrollRecord::where('payroll_period_id', $id)->update(['status' => 'Approved']);

        AuditService::log('updated', PayrollPeriod::class, $period->id,
            $old, $period->fresh()->toArray(),
            "Approved payroll period: {$period->name}"
        );

        return back()->with('success', "Payroll period '{$period->name}' approved successfully.");
    }

    public function pay(Request $request, $id): RedirectResponse
    {
        $period = PayrollPeriod::findOrFail($id);
        $old = $period->toArray();

        abort_if($period->status !== 'Approved', 422, 'Only approved payroll can be marked as paid.');
        $period->update([
            'status' => 'Paid',
            'payment_date' => now(),
            'payment_reference' => $request->input('payment_reference'),
            'locked_at' => now(),
        ]);
        PayrollRecord::where('payroll_period_id', $id)->update(['status' => 'Paid']);

        AuditService::log('updated', PayrollPeriod::class, $period->id,
            $old, $period->fresh()->toArray(),
            "Paid and closed payroll period: {$period->name}"
        );

        return back()->with('success', "Payroll period '{$period->name}' marked as Paid.");
    }

    public function payslip($id): View
    {
        $record = PayrollRecord::with(['employee.department', 'period'])->findOrFail($id);
        return view('payroll.payslip', compact('record'));
    }
}
