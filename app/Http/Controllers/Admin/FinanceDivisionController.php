<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BudgetAllocation;
use App\Models\Department;
use App\Models\DocumentRoutingLog;
use App\Models\Employee;
use App\Models\FinancialTransaction;
use App\Models\MinistryDocument;
use App\Models\PayrollPeriod;
use App\Services\AuditService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinanceDivisionController extends Controller
{
    public function dashboard(Request $request): View
    {
        $fiscalYear = (string) $request->query('year', now()->format('Y'));

        $budgets = BudgetAllocation::with('department')
            ->where('fiscal_year', $fiscalYear)
            ->orderBy('department_id')
            ->get();

        $allYearTx = FinancialTransaction::with(['department', 'budgetAllocation', 'document'])
            ->whereYear('transaction_date', $fiscalYear)
            ->get();

        $outflows = $allYearTx->filter(fn ($t) => !$t->is_inflow && in_array($t->status, ['Approved', 'Paid'], true));
        $inflows  = $allYearTx->filter(fn ($t) => $t->is_inflow && in_array($t->status, ['Approved', 'Paid'], true));

        $totalBudgetUsd = (float) $budgets->sum('allocated_usd');
        $totalBudgetLrd = (float) $budgets->sum('allocated_lrd');
        $totalSpentUsd  = (float) $outflows->sum('amount_usd');
        $totalSpentLrd  = (float) $outflows->sum('amount_lrd');
        $totalReceiptsUsd = (float) $inflows->sum('amount_usd');
        $budgetUtilization = $totalBudgetUsd > 0 ? (int) min(100, round(($totalSpentUsd / $totalBudgetUsd) * 100)) : 0;

        $latestPeriod = PayrollPeriod::with('records.employee.department')->latest('start_date')->first();
        $payrollRecords = $latestPeriod?->records ?? collect();

        $finDept = Department::where('code', 'FIN')->first();
        $incomingFinanceDocs = $finDept
            ? MinistryDocument::with(['originDepartment', 'sender'])
                ->where('current_department_id', $finDept->id)
                ->whereNotIn('status', ['Completed', 'Rejected'])
                ->latest('updated_at')
                ->take(8)
                ->get()
            : collect();

        $kpis = [
            'budget_usd'         => $totalBudgetUsd,
            'budget_lrd'         => $totalBudgetLrd,
            'spent_usd'          => $totalSpentUsd,
            'spent_lrd'          => $totalSpentLrd,
            'remaining_usd'      => round($totalBudgetUsd - $totalSpentUsd, 2),
            'utilization_pct'    => $budgetUtilization,
            'receipts_usd'       => $totalReceiptsUsd,
            'pending_vouchers'   => FinancialTransaction::where('status', 'Pending DMA')->count(),
            'approved_unpaid'    => FinancialTransaction::where('status', 'Approved')->count(),
            'latest_payroll_lrd' => (float) $payrollRecords->sum('net_pay_lrd'),
            'latest_payroll_usd' => (float) $payrollRecords->sum('net_pay_usd'),
            'incoming_fin_docs'  => $incomingFinanceDocs->count(),
        ];

        // Category Expenditure Breakdown
        $categorySpend = $outflows->groupBy('category')->map(fn ($grp) => [
            'count' => $grp->count(),
            'usd'   => (float) $grp->sum('amount_usd'),
            'lrd'   => (float) $grp->sum('amount_lrd'),
        ])->sortByDesc('usd');

        // Monthly Expenditure Chart (Jan - Dec of fiscal year)
        $monthlyLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $monthlyUsd = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlyUsd[] = round((float) $outflows->filter(fn ($t) => $t->transaction_date?->month === $m)->sum('amount_usd'), 2);
        }

        $recentTransactions = FinancialTransaction::with(['department', 'budgetAllocation', 'document', 'recorder'])
            ->latest('transaction_date')
            ->latest('id')
            ->take(10)
            ->get();

        $departments = Department::active()->orderBy('name')->get();
        $categories = BudgetAllocation::CATEGORIES;
        $fundingSources = BudgetAllocation::FUNDING_SOURCES;
        $transactionTypes = FinancialTransaction::TYPES;
        $paymentMethods = FinancialTransaction::PAYMENT_METHODS;

        return view('finance.dashboard', compact(
            'fiscalYear',
            'kpis',
            'budgets',
            'latestPeriod',
            'incomingFinanceDocs',
            'categorySpend',
            'monthlyLabels',
            'monthlyUsd',
            'recentTransactions',
            'departments',
            'categories',
            'fundingSources',
            'transactionTypes',
            'paymentMethods'
        ));
    }

    public function ledger(Request $request): View
    {
        $deptFilter = $request->query('department_id');
        $statusFilter = $request->query('status');
        $typeFilter = $request->query('type');
        $categoryFilter = $request->query('category');
        $search = trim((string) $request->query('search', ''));

        $query = FinancialTransaction::with(['department', 'budgetAllocation', 'document', 'recorder', 'approver']);

        if ($deptFilter) {
            $query->where('department_id', $deptFilter);
        }
        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }
        if ($typeFilter) {
            $query->where('transaction_type', $typeFilter);
        }
        if ($categoryFilter) {
            $query->where('category', $categoryFilter);
        }
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('voucher_no', 'like', "%{$search}%")
                  ->orWhere('payee_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('bank_reference', 'like', "%{$search}%");
            });
        }

        $transactions = $query->latest('transaction_date')->latest('id')->paginate(20)->withQueryString();
        $departments = Department::active()->orderBy('name')->get();
        $budgets = BudgetAllocation::with('department')->where('fiscal_year', now()->format('Y'))->get();
        $categories = BudgetAllocation::CATEGORIES;
        $transactionTypes = FinancialTransaction::TYPES;
        $paymentMethods = FinancialTransaction::PAYMENT_METHODS;

        return view('finance.ledger', compact(
            'transactions',
            'departments',
            'budgets',
            'categories',
            'transactionTypes',
            'paymentMethods',
            'deptFilter',
            'statusFilter',
            'typeFilter',
            'categoryFilter',
            'search'
        ));
    }

    public function storeBudget(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'fiscal_year'    => 'required|string|max:10',
            'department_id'  => 'required|exists:departments,id',
            'budget_code'    => 'required|string|max:50',
            'category'       => 'required|string|max:80',
            'allocated_usd'  => 'nullable|numeric|min:0',
            'allocated_lrd'  => 'nullable|numeric|min:0',
            'funding_source' => 'required|string|max:100',
            'notes'          => 'nullable|string|max:255',
        ]);

        $budget = BudgetAllocation::create([
            'fiscal_year'    => $data['fiscal_year'],
            'department_id'  => $data['department_id'],
            'budget_code'    => strtoupper(trim($data['budget_code'])),
            'category'       => $data['category'],
            'allocated_usd'  => $data['allocated_usd'] ?? 0,
            'allocated_lrd'  => $data['allocated_lrd'] ?? 0,
            'funding_source' => $data['funding_source'],
            'notes'          => ($data['notes'] ?? null) ?: null,
            'created_by'     => $request->user()->id,
        ]);

        AuditService::log('budget_created', BudgetAllocation::class, $budget->id, [], $budget->toArray(), "Created budget allocation {$budget->budget_code} (FY {$budget->fiscal_year})");

        return back()->with('success', "Budget line {$budget->budget_code} allocated successfully.");
    }

    public function storeTransaction(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'transaction_type'     => 'required|string|max:50',
            'category'             => 'required|string|max:80',
            'department_id'        => 'nullable|exists:departments,id',
            'budget_allocation_id' => 'nullable|exists:budget_allocations,id',
            'payee_name'           => 'required|string|max:160',
            'description'          => 'required|string|max:2000',
            'transaction_date'     => 'required|date',
            'amount_lrd'           => 'nullable|numeric|min:0',
            'amount_usd'           => 'nullable|numeric|min:0',
            'payment_method'       => 'required|string|max:60',
            'bank_reference'       => 'nullable|string|max:100',
            'route_to_dma'         => 'nullable|boolean',
            'attachment'           => 'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
        ]);

        if ((float) ($data['amount_lrd'] ?? 0) <= 0 && (float) ($data['amount_usd'] ?? 0) <= 0) {
            return back()->withInput()->with('error', 'Enter a positive amount in either USD or LRD.');
        }

        $voucherNo = FinancialTransaction::generateVoucherNo($data['transaction_type']);
        $attachmentPath = $request->hasFile('attachment')
            ? $request->file('attachment')->store('finance/vouchers', 'public')
            : null;

        $isInflow = $data['transaction_type'] === 'Budget Release / Receipt';
        $routeToDma = !$isInflow && $request->boolean('route_to_dma', true);
        $initialStatus = $isInflow ? 'Paid' : ($routeToDma ? 'Pending DMA' : 'Approved');

        $linkedDocId = null;
        if ($routeToDma) {
            $finDeptId = Department::where('code', 'FIN')->value('id') ?: ($data['department_id'] ?: Department::value('id'));
            $dmaDeptId = Department::where('code', 'DMA')->value('id') ?: Department::where('code', 'ADMIN')->value('id') ?: $finDeptId;
            $trackingNo = MinistryDocument::generateTrackingNumber();

            $doc = MinistryDocument::create([
                'tracking_number'               => $trackingNo,
                'subject'                       => "Payment Voucher ({$voucherNo}): {$data['payee_name']}",
                'document_type'                 => 'Budget / Payment Requisition',
                'priority'                      => 'High',
                'confidentiality'               => 'Restricted',
                'origin_department_id'          => $finDeptId,
                'current_department_id'         => $dmaDeptId,
                'destination_department_id'     => $dmaDeptId,
                'allowed_department_ids'        => array_values(array_unique(array_filter([$finDeptId, $dmaDeptId, $data['department_id'] ?? null]))),
                'sender_id'                     => $user->id,
                'amount_lrd'                    => $data['amount_lrd'] ?: null,
                'amount_usd'                    => $data['amount_usd'] ?: null,
                'summary'                       => "Voucher {$voucherNo} | Payee: {$data['payee_name']} | Category: {$data['category']} | Method: {$data['payment_method']}. {$data['description']}",
                'attachment_path'               => $attachmentPath,
                'status'                        => 'In Transit',
                'received_at_current_office_at' => now(),
            ]);

            DocumentRoutingLog::create([
                'ministry_document_id' => $doc->id,
                'from_department_id'   => $finDeptId,
                'to_department_id'     => $dmaDeptId,
                'action'               => 'dispatched',
                'actor_id'             => $user->id,
                'actor_name'           => $user->name,
                'actor_role'           => $user->primary_role,
                'minutes_note'         => "Finance voucher {$voucherNo} routed to DMA for payment approval.",
                'digital_signature'    => 'DOC-SIG-' . strtoupper(substr(hash('sha256', $trackingNo . '|' . now()->toIso8601String()), 0, 10)),
                'acted_at'             => now(),
            ]);

            $linkedDocId = $doc->id;
        }

        $tx = FinancialTransaction::create([
            'voucher_no'           => $voucherNo,
            'transaction_type'     => $data['transaction_type'],
            'category'             => $data['category'],
            'department_id'        => ($data['department_id'] ?? null) ?: null,
            'budget_allocation_id' => ($data['budget_allocation_id'] ?? null) ?: null,
            'payee_name'           => $data['payee_name'],
            'description'          => $data['description'],
            'transaction_date'     => $data['transaction_date'],
            'amount_lrd'           => ($data['amount_lrd'] ?? 0) ?: 0,
            'amount_usd'           => ($data['amount_usd'] ?? 0) ?: 0,
            'payment_method'       => $data['payment_method'],
            'bank_reference'       => ($data['bank_reference'] ?? null) ?: null,
            'status'               => $initialStatus,
            'attachment_path'      => $attachmentPath,
            'ministry_document_id' => $linkedDocId,
            'recorded_by'          => $user->id,
            'approved_by'          => $initialStatus !== 'Pending DMA' ? $user->id : null,
            'approved_at'          => $initialStatus !== 'Pending DMA' ? now() : null,
            'paid_at'              => $initialStatus === 'Paid' ? now() : null,
        ]);

        AuditService::log('financial_transaction_created', FinancialTransaction::class, $tx->id, [], $tx->toArray(), "Recorded voucher {$voucherNo} ({$data['transaction_type']}) for {$data['payee_name']}");

        $msg = "Financial voucher {$voucherNo} recorded ({$initialStatus}).";
        if ($linkedDocId && isset($trackingNo)) {
            $msg .= " Routed to DMA under Document Tracking #: {$trackingNo}.";
        }

        return back()->with('success', $msg);
    }

    public function updateStatus(Request $request, FinancialTransaction $transaction): RedirectResponse
    {
        $data = $request->validate([
            'decision'         => 'required|in:Approved,Paid,Rejected',
            'bank_reference'   => 'nullable|string|max:100',
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $update = ['status' => $data['decision']];

        if ($data['decision'] === 'Approved') {
            $update['approved_by'] = $request->user()->id;
            $update['approved_at'] = now();
        } elseif ($data['decision'] === 'Paid') {
            $update['approved_by'] = $transaction->approved_by ?: $request->user()->id;
            $update['approved_at'] = $transaction->approved_at ?: now();
            $update['paid_at'] = now();
            if (!empty($data['bank_reference'])) {
                $update['bank_reference'] = $data['bank_reference'];
            }
        } elseif ($data['decision'] === 'Rejected') {
            $update['approved_by'] = $request->user()->id;
            $update['approved_at'] = now();
            $update['rejection_reason'] = $data['rejection_reason'] ?: 'Rejected during review.';
        }

        $transaction->update($update);

        if ($transaction->document) {
            $doc = $transaction->document;
            $docStatus = match ($data['decision']) {
                'Approved' => 'Approved',
                'Paid'     => 'Completed',
                default    => 'Rejected',
            };
            $doc->update([
                'status'       => $docStatus,
                'completed_at' => in_array($docStatus, ['Completed', 'Rejected'], true) ? now() : null,
            ]);
            DocumentRoutingLog::create([
                'ministry_document_id' => $doc->id,
                'from_department_id'   => $doc->current_department_id,
                'to_department_id'     => $doc->current_department_id,
                'action'               => strtolower($data['decision']),
                'actor_id'             => $request->user()->id,
                'actor_name'           => $request->user()->name,
                'actor_role'           => $request->user()->primary_role,
                'minutes_note'         => "Finance voucher {$transaction->voucher_no} marked {$data['decision']}." . (!empty($data['bank_reference']) ? " Ref: {$data['bank_reference']}" : ''),
                'digital_signature'    => 'DOC-SIG-' . strtoupper(substr(hash('sha256', $doc->tracking_number . '|' . $data['decision'] . '|' . now()->toIso8601String()), 0, 10)),
                'acted_at'             => now(),
            ]);
        }

        AuditService::log('financial_transaction_updated', FinancialTransaction::class, $transaction->id, [], $update, "Voucher {$transaction->voucher_no} marked {$data['decision']}");

        return back()->with('success', "Voucher {$transaction->voucher_no} marked as {$data['decision']}.");
    }

    public function voucherPdf(FinancialTransaction $transaction)
    {
        $transaction->load(['department', 'budgetAllocation', 'recorder', 'approver', 'auditor', 'document']);
        $logoPath = public_path('images/logo.png');
        $logoDataUri = is_file($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;

        return Pdf::loadView('finance.voucher-pdf', compact('transaction', 'logoDataUri'))
            ->setPaper('a4', 'portrait')
            ->download("MYS-Voucher-{$transaction->voucher_no}.pdf");
    }

    public function financialReportPdf(Request $request)
    {
        $fiscalYear = (string) $request->query('year', now()->format('Y'));
        $budgets = BudgetAllocation::with('department')->where('fiscal_year', $fiscalYear)->get();
        $transactions = FinancialTransaction::with(['department', 'budgetAllocation'])
            ->whereYear('transaction_date', $fiscalYear)
            ->orderByDesc('transaction_date')
            ->get();

        $outflows = $transactions->filter(fn ($t) => !$t->is_inflow && in_array($t->status, ['Approved', 'Paid'], true));
        $inflows  = $transactions->filter(fn ($t) => $t->is_inflow && in_array($t->status, ['Approved', 'Paid'], true));

        $summary = [
            'budget_usd'   => (float) $budgets->sum('allocated_usd'),
            'budget_lrd'   => (float) $budgets->sum('allocated_lrd'),
            'receipts_usd' => (float) $inflows->sum('amount_usd'),
            'spent_usd'    => (float) $outflows->sum('amount_usd'),
            'spent_lrd'    => (float) $outflows->sum('amount_lrd'),
            'payroll_lrd'  => (float) PayrollPeriod::whereYear('start_date', $fiscalYear)->whereIn('status', ['Approved', 'Paid'])->with('records')->get()->sum(fn ($p) => $p->records->sum('net_pay_lrd')),
            'payroll_usd'  => (float) PayrollPeriod::whereYear('start_date', $fiscalYear)->whereIn('status', ['Approved', 'Paid'])->with('records')->get()->sum(fn ($p) => $p->records->sum('net_pay_usd')),
        ];

        $categorySummary = $outflows->groupBy('category')->map(fn ($g) => [
            'count' => $g->count(),
            'usd'   => (float) $g->sum('amount_usd'),
            'lrd'   => (float) $g->sum('amount_lrd'),
        ])->sortByDesc('usd');

        $logoPath = public_path('images/logo.png');
        $logoDataUri = is_file($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;

        AuditService::log('exported', FinancialTransaction::class, null, [], ['fiscal_year' => $fiscalYear], "Exported Ministry Financial Statement PDF (FY {$fiscalYear})");

        return Pdf::loadView('finance.report-pdf', compact('fiscalYear', 'budgets', 'transactions', 'summary', 'categorySummary', 'logoDataUri'))
            ->setPaper('a4', 'landscape')
            ->download("MYS-Financial-Report-FY{$fiscalYear}.pdf");
    }

    public function exportLedgerCsv(Request $request)
    {
        $transactions = FinancialTransaction::with(['department', 'budgetAllocation', 'recorder', 'approver'])
            ->latest('transaction_date')
            ->get();

        AuditService::log('exported', FinancialTransaction::class, null, [], ['count' => $transactions->count()], 'Exported Finance Division Accounting Ledger CSV');

        return response()->streamDownload(function () use ($transactions) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Voucher No', 'Date', 'Type', 'Category', 'Department', 'Budget Code',
                'Payee', 'Description', 'Amount (USD)', 'Amount (LRD)',
                'Payment Method', 'Bank / Check Ref', 'Status', 'Recorded By', 'Approved By',
            ]);
            foreach ($transactions as $t) {
                fputcsv($out, [
                    $t->voucher_no,
                    $t->transaction_date?->format('Y-m-d'),
                    $t->transaction_type,
                    $t->category,
                    $t->department?->name ?? 'Ministry HQ',
                    $t->budgetAllocation?->budget_code ?? '—',
                    $t->payee_name,
                    $t->description,
                    number_format((float) $t->amount_usd, 2, '.', ''),
                    number_format((float) $t->amount_lrd, 2, '.', ''),
                    $t->payment_method,
                    $t->bank_reference ?: '—',
                    $t->status,
                    $t->recorder?->name ?? '—',
                    $t->approver?->name ?? '—',
                ]);
            }
            fclose($out);
        }, 'MYS-Finance-Ledger-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
