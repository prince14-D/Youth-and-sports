<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\PayslipDistributionMail;
use App\Models\PayrollLineItem;
use App\Models\PayrollPeriod;
use App\Models\PayrollRecord;
use App\Models\Employee;
use App\Services\ApprovalWorkflowService;
use App\Services\AuditService;
use App\Services\UserNotificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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
            'name'           => $request->name,
            'start_date'     => $request->start_date,
            'end_date'       => $request->end_date,
            'status'         => 'Draft',
            'approval_stage' => 0,
            'created_by'     => auth()->id(),
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
        $period = PayrollPeriod::with(['creator', 'reviewer', 'approvalTrails'])->findOrFail($id);
        $records = PayrollRecord::with(['employee.department', 'lineItems'])
            ->where('payroll_period_id', $id)
            ->get();

        $totals = [
            'lrd' => $records->sum('net_pay_lrd'),
            'usd' => $records->sum('net_pay_usd'),
        ];

        $allowanceCategories = PayrollLineItem::ALLOWANCE_CATEGORIES;
        $deductionCategories = PayrollLineItem::DEDUCTION_CATEGORIES;

        return view('payroll.show', compact('period', 'records', 'totals', 'allowanceCategories', 'deductionCategories'));
    }

    public function storeLineItem(Request $request, $recordId): RedirectResponse
    {
        $record = PayrollRecord::with(['period', 'employee'])->findOrFail($recordId);
        abort_if($record->period?->status !== 'Draft', 422, 'Itemized allowances and deductions can only be modified while the payroll cycle is in Draft.');

        $data = $request->validate([
            'type'        => 'required|in:allowance,deduction',
            'category'    => 'required|string|max:80',
            'description' => 'nullable|string|max:200',
            'currency'    => 'required|in:LRD,USD',
            'amount'      => 'required|numeric|min:0.01|max:99999999',
        ]);

        $item = $record->lineItems()->create([
            'type'        => $data['type'],
            'category'    => $data['category'],
            'description' => $data['description'] ?? null,
            'currency'    => $data['currency'],
            'amount'      => $data['amount'],
            'created_by'  => $request->user()->id,
        ]);

        $record->recalculateTotals();

        AuditService::log(
            'payroll_line_item_added',
            PayrollRecord::class,
            $record->id,
            [],
            $item->toArray(),
            "Added {$data['type']} ({$data['category']}: {$data['currency']} {$data['amount']}) for {$record->employee?->full_name}"
        );

        return back()->with('success', "Added {$data['category']} ({$data['currency']} " . number_format((float) $data['amount'], 2) . ") for {$record->employee?->full_name} and recalculated net pay.");
    }

    public function destroyLineItem(Request $request, $itemId): RedirectResponse
    {
        $item = PayrollLineItem::with(['record.period', 'record.employee'])->findOrFail($itemId);
        $record = $item->record;
        abort_if($record?->period?->status !== 'Draft', 422, 'Cannot remove line items after the payroll cycle leaves Draft.');

        $label = "{$item->category} ({$item->currency} {$item->amount})";
        $empName = $record->employee?->full_name ?? 'staff';
        $item->delete();
        $record->recalculateTotals();

        AuditService::log('payroll_line_item_removed', PayrollRecord::class, $record->id, [], [], "Removed {$label} for {$empName}");

        return back()->with('success', "Removed {$label} and recalculated net pay for {$empName}.");
    }

    public function bankScheduleCsv($id)
    {
        $period = PayrollPeriod::findOrFail($id);
        $records = PayrollRecord::with(['employee.department'])
            ->where('payroll_period_id', $id)
            ->get()
            ->sortBy(fn ($r) => ($r->employee?->bank_name ?? 'ZZZ') . '|' . ($r->employee?->full_name ?? ''));

        $slug = \Illuminate\Support\Str::slug($period->name);
        $filename = "MYS-Bank-Schedule-{$slug}.csv";

        AuditService::log('exported', PayrollPeriod::class, $period->id, [], [], "Exported Bank Payment Schedule CSV for {$period->name}");

        return response()->streamDownload(function () use ($period, $records) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Seq', 'Employee/Contractor ID', 'Full Name', 'Category', 'Department',
                'Bank Name', 'Bank Account Number', 'TIN Number', 'NASSCORP No.',
                'Basic Salary (LRD)', 'Allowances (LRD)', 'Overtime (LRD)',
                'Tax (LRD)', 'Pension (LRD)', 'Other Deductions (LRD)',
                'Net Pay (LRD)', 'Net Pay (USD)', 'Payment Reference', 'Status',
            ]);
            $seq = 1;
            foreach ($records as $rec) {
                $emp = $rec->employee;
                fputcsv($out, [
                    $seq++,
                    $emp?->employee_no ?? '—',
                    $emp?->full_name ?? 'Deleted',
                    $emp?->employment_type ?? '—',
                    $emp?->department?->name ?? 'Unassigned',
                    $emp?->bank_name ?: 'Direct Ministry Payroll',
                    $emp?->bank_account ?: '—',
                    $emp?->tin_number ?: '—',
                    $emp?->social_security_no ?: '—',
                    number_format((float) $rec->basic_salary_lrd, 2, '.', ''),
                    number_format((float) $rec->allowances_lrd, 2, '.', ''),
                    number_format((float) $rec->overtime_lrd, 2, '.', ''),
                    number_format((float) $rec->tax_lrd, 2, '.', ''),
                    number_format((float) $rec->pension_lrd, 2, '.', ''),
                    number_format((float) $rec->deductions_lrd, 2, '.', ''),
                    number_format((float) $rec->net_pay_lrd, 2, '.', ''),
                    number_format((float) $rec->net_pay_usd, 2, '.', ''),
                    $period->payment_reference ?: ('CYCLE-' . $period->id),
                    $rec->status,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function bankSchedulePdf($id)
    {
        $period = PayrollPeriod::with(['creator', 'reviewer'])->findOrFail($id);
        $records = PayrollRecord::with(['employee.department'])
            ->where('payroll_period_id', $id)
            ->get()
            ->sortBy(fn ($r) => ($r->employee?->bank_name ?? 'ZZZ') . '|' . ($r->employee?->full_name ?? ''));

        $bankGroups = $records->groupBy(fn ($r) => $r->employee?->bank_name ?: 'Direct Ministry Payroll')
            ->map(fn ($group) => [
                'count'   => $group->count(),
                'net_lrd' => $group->sum('net_pay_lrd'),
                'net_usd' => $group->sum('net_pay_usd'),
            ]);

        $totals = [
            'gross_lrd' => $records->sum(fn ($r) => (float) $r->basic_salary_lrd + (float) $r->allowances_lrd + (float) $r->overtime_lrd),
            'ded_lrd'   => $records->sum(fn ($r) => (float) $r->tax_lrd + (float) $r->pension_lrd + (float) $r->deductions_lrd),
            'net_lrd'   => $records->sum('net_pay_lrd'),
            'net_usd'   => $records->sum('net_pay_usd'),
        ];

        $logoPath = public_path('images/logo.png');
        $logoDataUri = is_file($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;

        AuditService::log('exported', PayrollPeriod::class, $period->id, [], [], "Exported Treasury Bank Payment Schedule PDF for {$period->name}");

        $slug = \Illuminate\Support\Str::slug($period->name);
        return Pdf::loadView('payroll.bank-schedule-pdf', compact('period', 'records', 'bankGroups', 'totals', 'logoDataUri'))
            ->setPaper('a4', 'landscape')
            ->download("MYS-Treasury-Schedule-{$slug}.pdf");
    }

    public function review(Request $request, $id): RedirectResponse
    {
        $period = PayrollPeriod::findOrFail($id);
        abort_if($period->status !== 'Draft', 422, 'Only draft payroll can be submitted into the approval workflow.');

        $period->update([
            'status'           => 'Processing',
            'approval_stage'   => 1,
            'rejection_reason' => null,
            'reviewed_by'      => $request->user()->id,
            'reviewed_at'      => now(),
        ]);

        ApprovalWorkflowService::recordSubmission(
            $period,
            $request->user(),
            "Payroll run '{$period->name}' submitted for Stage 1 HR Officer verification."
        );
        ApprovalWorkflowService::notifyRole(
            'HR Officer',
            'workflow_stage_pending',
            'Payroll Run Awaiting Stage 1 Verification',
            "Payroll cycle '{$period->name}' requires HR Officer review & verification.",
            ['url' => route('payroll.show', $period->id)]
        );
        AuditService::log('updated', PayrollPeriod::class, $period->id, [], $period->fresh()->toArray(), "Submitted payroll period {$period->name} to 3-tier HR approval workflow");

        return back()->with('success', "Payroll period '{$period->name}' submitted to Stage 1 (HR Officer Review).");
    }

    public function approve(Request $request, $id): RedirectResponse
    {
        $request->validate(['remarks' => 'nullable|string|max:500']);
        $period = PayrollPeriod::with('creator')->findOrFail($id);
        abort_if($period->status !== 'Processing', 422, 'Payroll must be In Review (Processing) before tiered approval.');

        $result = ApprovalWorkflowService::advance(
            $period,
            $request->user(),
            $request->input('remarks'),
            "Payroll Cycle ({$period->name})",
            route('payroll.show', $period->id)
        );

        if ($result['is_final']) {
            $period->update([
                'status'         => 'Approved',
                'approval_stage' => 4,
                'reviewed_by'    => $request->user()->id,
                'reviewed_at'    => now(),
            ]);
            PayrollRecord::where('payroll_period_id', $id)->update(['status' => 'Approved']);

            if ($period->creator) {
                UserNotificationService::send(
                    $period->creator,
                    'payroll_approved',
                    'Payroll Cycle Fully Approved',
                    "Payroll cycle '{$period->name}' received final HR Director approval and is ready for disbursement.",
                    ['url' => route('payroll.show', $period->id)]
                );
            }
        } else {
            $period->update([
                'approval_stage' => $result['next_stage'],
                'reviewed_by'    => $request->user()->id,
                'reviewed_at'    => now(),
            ]);
        }

        return back()->with('success', $result['message']);
    }

    public function reject(Request $request, $id): RedirectResponse
    {
        $data = $request->validate(['rejection_reason' => 'required|string|max:500']);
        $period = PayrollPeriod::with('creator')->findOrFail($id);
        abort_if($period->status !== 'Processing', 422, 'Only payroll cycles currently in review can be returned/rejected.');

        $trail = ApprovalWorkflowService::reject(
            $period,
            $request->user(),
            $data['rejection_reason'],
            "Payroll Cycle ({$period->name})"
        );

        $period->update([
            'status'           => 'Draft',
            'approval_stage'   => 0,
            'rejection_reason' => $data['rejection_reason'],
            'reviewed_by'      => $request->user()->id,
            'reviewed_at'      => now(),
        ]);
        PayrollRecord::where('payroll_period_id', $id)->update(['status' => 'Draft']);

        if ($period->creator) {
            UserNotificationService::send(
                $period->creator,
                'payroll_rejected',
                'Payroll Returned to Draft',
                "Payroll cycle '{$period->name}' was returned at Stage {$trail->stage}: {$data['rejection_reason']}",
                ['url' => route('payroll.show', $period->id)]
            );
        }

        return back()->with('warning', "Payroll cycle '{$period->name}' returned to Draft at Stage {$trail->stage} [{$trail->digital_signature}].");
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
        $data = $this->buildPayslipData($id);
        return view('payroll.payslip', $data);
    }

    public function payslipPdf($id)
    {
        $data = $this->buildPayslipData($id);
        $empNo = $data['record']->employee?->employee_no ?? 'STAFF';
        $periodSlug = \Illuminate\Support\Str::slug($data['record']->period?->name ?? 'period');

        return Pdf::loadView('payroll.payslip-pdf', $data)
            ->setPaper('a4', 'portrait')
            ->download("MYS-Payslip-{$empNo}-{$periodSlug}.pdf");
    }

    public function emailSinglePayslip(Request $request, $id): RedirectResponse
    {
        $data = $this->buildPayslipData($id);
        $record = $data['record'];
        $employee = $record->employee;

        if (!$employee) {
            return back()->with('error', 'Cannot distribute payslip: linked employee record not found.');
        }

        $result = $this->dispatchPayslipToEmployee($data);

        AuditService::log(
            'payslip_emailed',
            PayrollRecord::class,
            $record->id,
            [],
            ['recipient' => $result['email'], 'emailed' => $result['emailed'], 'notified' => $result['notified']],
            "Distributed payslip {$data['payslipRef']} to {$employee->full_name}"
        );

        if ($result['emailed']) {
            return back()->with('success', "Official PDF payslip emailed to {$employee->full_name} ({$result['email']}) and delivered to their portal.");
        }

        return back()->with('warning', "Payslip delivered to {$employee->full_name}'s portal notifications, but email delivery could not be completed ({$result['reason']}).");
    }

    public function emailAllPayslips(Request $request, $id): RedirectResponse
    {
        $period = PayrollPeriod::with('records.employee.user')->findOrFail($id);
        abort_unless(in_array($period->status, ['Approved', 'Paid'], true), 422, 'Only Approved or Paid payroll cycles can distribute payslips.');

        $emailed = 0;
        $notified = 0;
        $emailFailed = 0;

        foreach ($period->records as $rec) {
            if (!$rec->employee) {
                continue;
            }
            $data = $this->buildPayslipData($rec->id);
            $res = $this->dispatchPayslipToEmployee($data);
            if ($res['emailed']) {
                $emailed++;
            } else {
                $emailFailed++;
            }
            if ($res['notified']) {
                $notified++;
            }
        }

        AuditService::log(
            'payroll_payslips_distributed',
            PayrollPeriod::class,
            $period->id,
            [],
            ['emailed' => $emailed, 'notified' => $notified, 'email_failed' => $emailFailed],
            "Batch distributed payslips for {$period->name}: {$emailed} emailed, {$notified} portal alerts"
        );

        $msg = "Payslip distribution for '{$period->name}' completed: {$emailed} PDF email(s) sent and {$notified} staff portal notification(s) delivered.";
        if ($emailFailed > 0) {
            return back()->with('warning', $msg . " ({$emailFailed} recipient(s) had missing email or mail-server errors — portal notifications were still delivered.)");
        }

        return back()->with('success', $msg);
    }

    private function dispatchPayslipToEmployee(array $data): array
    {
        $record = $data['record'];
        $employee = $record->employee;
        $recipientEmail = $employee?->official_email ?: ($employee?->user?->email ?: $employee?->personal_email);
        $empNo = $employee?->employee_no ?? 'STAFF';
        $periodSlug = \Illuminate\Support\Str::slug($record->period?->name ?? 'period');
        $filename = "MYS-Payslip-{$empNo}-{$periodSlug}.pdf";

        $notified = false;
        if ($employee?->user) {
            UserNotificationService::send(
                $employee->user,
                'payslip_ready',
                ($data['isContractor'] ? 'Payment Advice Ready: ' : 'Payslip Ready: ') . ($record->period?->name ?? 'Payroll'),
                "Your official " . ($data['isContractor'] ? 'payment advice' : 'payslip') . " ({$data['payslipRef']}) is ready to view and download.",
                ['url' => route('payroll.payslip', $record->id)]
            );
            $notified = true;
        }

        if (!$recipientEmail) {
            return ['emailed' => false, 'notified' => $notified, 'email' => null, 'reason' => 'no email address on file'];
        }

        try {
            $pdfBinary = Pdf::loadView('payroll.payslip-pdf', $data)
                ->setPaper('a4', 'portrait')
                ->output();

            Mail::to($recipientEmail)->send(new PayslipDistributionMail($data, $pdfBinary, $filename));

            return ['emailed' => true, 'notified' => $notified, 'email' => $recipientEmail, 'reason' => null];
        } catch (\Throwable $e) {
            Log::warning('Payslip email dispatch failed.', [
                'payroll_record_id' => $record->id,
                'email'             => $recipientEmail,
                'error'             => $e->getMessage(),
            ]);
            return ['emailed' => false, 'notified' => $notified, 'email' => $recipientEmail, 'reason' => $e->getMessage()];
        }
    }

    private function buildPayslipData($id): array
    {
        $record = PayrollRecord::with(['employee.department', 'employee.supervisor', 'period.creator', 'lineItems'])->findOrFail($id);
        $user = auth()->user();

        $canViewPayroll = $user && $user->can('view payroll');
        $isOwner = $user && $user->employee_id && (int) $user->employee_id === (int) $record->employee_id;

        abort_unless($canViewPayroll || $isOwner, 403, 'You are not authorized to view this payslip.');

        $grossLrd = (float) $record->basic_salary_lrd + (float) $record->allowances_lrd + (float) $record->overtime_lrd;
        $grossUsd = (float) $record->basic_salary_usd + (float) $record->allowances_usd;
        $totalDeductionsLrd = (float) $record->tax_lrd + (float) $record->pension_lrd + (float) $record->deductions_lrd;

        $periodYear = $record->period?->start_date?->year ?? $record->created_at?->year ?? now()->year;
        $periodEnd = $record->period?->end_date ?? $record->created_at ?? now();

        $ytdRecords = PayrollRecord::where('employee_id', $record->employee_id)
            ->whereHas('period', function ($q) use ($periodYear, $periodEnd) {
                $q->whereYear('start_date', $periodYear)
                  ->whereDate('end_date', '<=', $periodEnd);
            })
            ->get();

        if ($ytdRecords->isEmpty()) {
            $ytdRecords = collect([$record]);
        }

        $ytd = [
            'year'           => $periodYear,
            'periods_count'  => $ytdRecords->count(),
            'gross_lrd'      => $ytdRecords->sum(fn ($r) => (float) $r->basic_salary_lrd + (float) $r->allowances_lrd + (float) $r->overtime_lrd),
            'tax_lrd'        => $ytdRecords->sum('tax_lrd'),
            'pension_lrd'    => $ytdRecords->sum('pension_lrd'),
            'deductions_lrd' => $ytdRecords->sum(fn ($r) => (float) $r->tax_lrd + (float) $r->pension_lrd + (float) $r->deductions_lrd),
            'net_lrd'        => $ytdRecords->sum('net_pay_lrd'),
            'net_usd'        => $ytdRecords->sum('net_pay_usd'),
        ];

        $payslipRef = sprintf('MYS-PS-%d-%05d', $periodYear, $record->id);
        $verificationCode = strtoupper(substr(hash('sha256', $payslipRef . '|' . $record->employee_id . '|' . $record->net_pay_lrd), 0, 12));
        $isContractor = $record->employee?->employment_type === 'Contract';

        $logoPath = public_path('images/logo.png');
        $logoDataUri = is_file($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        $amountInWordsLrd = $this->currencyToWords((float) $record->net_pay_lrd, 'Liberian Dollars', 'Cents');
        $amountInWordsUsd = $this->currencyToWords((float) $record->net_pay_usd, 'US Dollars', 'Cents');

        $maskedAccount = null;
        if ($record->employee?->bank_account) {
            $acct = trim($record->employee->bank_account);
            $maskedAccount = strlen($acct) > 4
                ? str_repeat('*', strlen($acct) - 4) . substr($acct, -4)
                : $acct;
        }

        return compact(
            'record',
            'canViewPayroll',
            'grossLrd',
            'grossUsd',
            'totalDeductionsLrd',
            'ytd',
            'payslipRef',
            'verificationCode',
            'isContractor',
            'logoDataUri',
            'amountInWordsLrd',
            'amountInWordsUsd',
            'maskedAccount'
        );
    }

    private function currencyToWords(float $amount, string $majorUnit, string $minorUnit): string
    {
        $amount = round(max(0, $amount), 2);
        $whole = (int) floor($amount);
        $cents = (int) round(($amount - $whole) * 100);

        $words = $this->intToWords($whole) . ' ' . $majorUnit;
        if ($cents > 0) {
            $words .= ' and ' . $this->intToWords($cents) . ' ' . $minorUnit;
        }
        return $words . ' Only';
    }

    private function intToWords(int $num): string
    {
        if ($num === 0) {
            return 'Zero';
        }
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
            'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        $scales = ['', 'Thousand', 'Million', 'Billion'];

        $parts = [];
        $scaleIdx = 0;
        while ($num > 0) {
            $chunk = $num % 1000;
            if ($chunk > 0) {
                $chunkWords = [];
                if ($chunk >= 100) {
                    $chunkWords[] = $ones[(int) floor($chunk / 100)] . ' Hundred';
                    $chunk %= 100;
                }
                if ($chunk >= 20) {
                    $t = $tens[(int) floor($chunk / 10)];
                    $o = $chunk % 10;
                    $chunkWords[] = $o > 0 ? "{$t}-{$ones[$o]}" : $t;
                } elseif ($chunk > 0) {
                    $chunkWords[] = $ones[$chunk];
                }
                $label = trim(implode(' ', $chunkWords) . ($scales[$scaleIdx] ? ' ' . $scales[$scaleIdx] : ''));
                array_unshift($parts, $label);
            }
            $num = (int) floor($num / 1000);
            $scaleIdx++;
        }

        return implode(' ', $parts);
    }
}
