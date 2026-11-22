<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AuditFinding;
use App\Models\AuditLog;
use App\Models\BudgetAllocation;
use App\Models\Department;
use App\Models\DocumentRoutingLog;
use App\Models\Employee;
use App\Models\FinancialTransaction;
use App\Models\InternalControlAssessment;
use App\Models\MinistryDocument;
use App\Services\AuditService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InternalAuditController extends Controller
{
    public function dashboard(Request $request): View
    {
        $auditStatusFilter = (string) $request->query('audit_status', '');
        $findingStatusFilter = (string) $request->query('finding_status', '');
        $severityFilter = (string) $request->query('severity', '');
        $activeTab = (string) $request->query('tab', 'vouchers');

        $anomalies = $this->runAnomalyScanner();
        $activeAnomalyCount = collect($anomalies)->where('count', '>', 0)->count();

        // Financial Vouchers Queue
        $txQuery = FinancialTransaction::with(['department', 'budgetAllocation', 'recorder', 'approver', 'auditor', 'document'])
            ->latest('transaction_date')
            ->latest('id');

        if ($auditStatusFilter !== '') {
            if ($auditStatusFilter === 'Pending Review') {
                $txQuery->where(function ($q) {
                    $q->whereNull('audit_status')->orWhere('audit_status', 'Pending Review');
                });
            } else {
                $txQuery->where('audit_status', $auditStatusFilter);
            }
        }
        $transactions = $txQuery->take(30)->get();

        // Audit Findings & Irregularities
        $findingQuery = AuditFinding::with(['department', 'transaction', 'document', 'reporter', 'resolver'])
            ->latest('created_at');

        if ($findingStatusFilter !== '') {
            $findingQuery->where('status', $findingStatusFilter);
        }
        if ($severityFilter !== '') {
            $findingQuery->where('severity', $severityFilter);
        }
        $findings = $findingQuery->take(40)->get();

        // Internal Control Assessments
        $assessments = InternalControlAssessment::with(['department', 'assessor'])
            ->orderByDesc('assessment_date')
            ->orderByDesc('id')
            ->take(30)
            ->get();

        // KPIs
        $pendingAuditVouchers = FinancialTransaction::where(function ($q) {
            $q->whereNull('audit_status')->orWhere('audit_status', 'Pending Review');
        })->count();

        $verifiedVouchers = FinancialTransaction::where('audit_status', 'Verified')->count();
        $flaggedVouchers = FinancialTransaction::whereIn('audit_status', ['Queried', 'Irregularity Flagged'])->count();

        $openFindingsQuery = AuditFinding::whereIn('status', ['Open', 'Under Investigation']);
        $openFindingsCount = (clone $openFindingsQuery)->count();
        $criticalHighCount = (clone $openFindingsQuery)->whereIn('severity', ['Critical', 'High'])->count();
        $financialExposureUsd = (float) (clone $openFindingsQuery)->sum('financial_impact_usd');

        $avgScoreRaw = InternalControlAssessment::avg('score');
        $avgControlScore = $avgScoreRaw !== null ? (int) round((float) $avgScoreRaw) : null;

        $kpis = [
            'pending_vouchers'       => $pendingAuditVouchers,
            'verified_vouchers'      => $verifiedVouchers,
            'flagged_vouchers'       => $flaggedVouchers,
            'open_findings'          => $openFindingsCount,
            'critical_high_findings' => $criticalHighCount,
            'financial_exposure_usd' => $financialExposureUsd,
            'avg_control_score'      => $avgControlScore,
            'active_anomalies'       => $activeAnomalyCount,
            'system_logs_7d'         => AuditLog::where('created_at', '>=', now()->subDays(7))->count(),
        ];

        // Departmental Compliance Overview
        $departments = Department::active()->orderBy('name')->get();
        $deptFindings = AuditFinding::whereIn('status', ['Open', 'Under Investigation'])
            ->selectRaw('department_id, count(*) as open_count')
            ->groupBy('department_id')
            ->pluck('open_count', 'department_id');

        $deptScores = InternalControlAssessment::selectRaw('department_id, round(avg(score)) as avg_score, count(*) as reviews_count')
            ->groupBy('department_id')
            ->get()
            ->keyBy('department_id');

        $departmentCompliance = $departments->map(function (Department $dept) use ($deptFindings, $deptScores) {
            $scoreRow = $deptScores->get($dept->id);
            return [
                'department'    => $dept,
                'open_findings' => (int) ($deptFindings[$dept->id] ?? 0),
                'avg_score'     => $scoreRow ? (int) $scoreRow->avg_score : null,
                'reviews_count' => $scoreRow ? (int) $scoreRow->reviews_count : 0,
            ];
        })->filter(fn ($row) => $row['open_findings'] > 0 || $row['reviews_count'] > 0)->values();

        // Recent System Activity Logs
        $recentSystemLogs = AuditLog::with('user')->latest('id')->take(10)->get();

        // Incoming documents routed to Internal Audit Office
        $auditDept = Department::where('code', 'AUDIT')->first();
        $incomingAuditDocs = $auditDept
            ? MinistryDocument::with(['originDepartment', 'sender'])
                ->where('current_department_id', $auditDept->id)
                ->whereNotIn('status', ['Completed', 'Rejected'])
                ->latest('updated_at')
                ->take(8)
                ->get()
            : collect();

        $auditAreas = AuditFinding::AUDIT_AREAS;
        $severities = AuditFinding::SEVERITIES;
        $findingStatuses = AuditFinding::STATUSES;
        $controlAreas = InternalControlAssessment::CONTROL_AREAS;
        $controlRatings = InternalControlAssessment::RATINGS;
        $vouchersForSelect = FinancialTransaction::latest('transaction_date')->take(50)->get(['id', 'voucher_no', 'payee_name', 'amount_usd']);

        return view('internal-audit.dashboard', compact(
            'kpis',
            'anomalies',
            'transactions',
            'findings',
            'assessments',
            'departmentCompliance',
            'recentSystemLogs',
            'incomingAuditDocs',
            'departments',
            'auditAreas',
            'severities',
            'findingStatuses',
            'controlAreas',
            'controlRatings',
            'vouchersForSelect',
            'auditStatusFilter',
            'findingStatusFilter',
            'severityFilter',
            'activeTab'
        ));
    }

    public function verifyTransaction(Request $request, FinancialTransaction $transaction): RedirectResponse
    {
        $data = $request->validate([
            'audit_status'     => ['required', Rule::in(['Verified', 'Queried', 'Irregularity Flagged'])],
            'audit_notes'      => 'nullable|string|max:1500',
            'auto_log_finding' => 'nullable|boolean',
            'escalate_to'      => ['nullable', Rule::in(['none', 'MINISTER', 'DMA'])],
        ]);

        $transaction->update([
            'audit_status' => $data['audit_status'],
            'audited_by'   => $request->user()->id,
            'audited_at'   => now(),
            'audit_notes'  => ($data['audit_notes'] ?? null) ?: null,
        ]);

        AuditService::log(
            'audit_voucher_verified',
            FinancialTransaction::class,
            $transaction->id,
            [],
            ['voucher_no' => $transaction->voucher_no, 'audit_status' => $data['audit_status']],
            "Internal Audit marked voucher {$transaction->voucher_no} as {$data['audit_status']}"
        );

        $findingMessage = '';
        $shouldCreateFinding = $data['audit_status'] === 'Irregularity Flagged'
            || ($data['audit_status'] === 'Queried' && !empty($data['auto_log_finding']));

        if ($shouldCreateFinding) {
            $severity = $data['audit_status'] === 'Irregularity Flagged'
                ? ((float) $transaction->amount_usd >= 5000 ? 'Critical' : 'High')
                : 'Medium';

            $finding = AuditFinding::create([
                'finding_no'               => AuditFinding::generateFindingNo(),
                'title'                    => "{$data['audit_status']}: Voucher {$transaction->voucher_no} ({$transaction->payee_name})",
                'audit_area'               => 'Financial Transactions',
                'department_id'            => $transaction->department_id,
                'severity'                 => $severity,
                'status'                   => 'Open',
                'financial_impact_usd'     => (float) $transaction->amount_usd > 0 ? $transaction->amount_usd : null,
                'description'              => ($data['audit_notes'] ?? null) ?: "Internal Audit flagged payment voucher {$transaction->voucher_no} for {$transaction->payee_name} (USD " . number_format((float) $transaction->amount_usd, 2) . ") for immediate verification and supporting documentation review.",
                'recommendation'           => 'Finance Division and originating department must provide complete supporting invoices, authorization trail, and bank/check verification prior to clearance.',
                'financial_transaction_id' => $transaction->id,
                'due_date'                 => now()->addDays(7)->toDateString(),
                'reported_by'              => $request->user()->id,
            ]);

            $findingMessage = " Audit Finding {$finding->finding_no} logged.";

            $escalateTo = $data['escalate_to'] ?? 'none';
            if (in_array($escalateTo, ['MINISTER', 'DMA'], true)) {
                $doc = $this->escalateFindingToExecutive($finding, $escalateTo, $request->user());
                if ($doc) {
                    $finding->update(['ministry_document_id' => $doc->id]);
                    $findingMessage .= " Escalated to {$escalateTo} ({$doc->tracking_number}).";
                }
            }
        }

        return back()->with('success', "Voucher {$transaction->voucher_no} updated to {$data['audit_status']}.{$findingMessage}");
    }

    public function storeFinding(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'                    => 'required|string|max:200',
            'audit_area'               => ['required', Rule::in(AuditFinding::AUDIT_AREAS)],
            'department_id'            => 'nullable|exists:departments,id',
            'severity'                 => ['required', Rule::in(AuditFinding::SEVERITIES)],
            'financial_impact_usd'     => 'nullable|numeric|min:0',
            'financial_transaction_id' => 'nullable|exists:financial_transactions,id',
            'due_date'                 => 'nullable|date',
            'description'              => 'required|string|max:3000',
            'recommendation'           => 'required|string|max:2000',
            'escalate_to'              => ['nullable', Rule::in(['none', 'MINISTER', 'DMA'])],
        ]);

        $finding = AuditFinding::create([
            'finding_no'               => AuditFinding::generateFindingNo(),
            'title'                    => $data['title'],
            'audit_area'               => $data['audit_area'],
            'department_id'            => ($data['department_id'] ?? null) ?: null,
            'severity'                 => $data['severity'],
            'status'                   => 'Open',
            'financial_impact_usd'     => isset($data['financial_impact_usd']) && $data['financial_impact_usd'] !== '' ? $data['financial_impact_usd'] : null,
            'financial_transaction_id' => ($data['financial_transaction_id'] ?? null) ?: null,
            'due_date'                 => ($data['due_date'] ?? null) ?: null,
            'description'              => $data['description'],
            'recommendation'           => $data['recommendation'],
            'reported_by'              => $request->user()->id,
        ]);

        $escalationMsg = '';
        $escalateTo = $data['escalate_to'] ?? 'none';
        if (in_array($escalateTo, ['MINISTER', 'DMA'], true)) {
            $doc = $this->escalateFindingToExecutive($finding, $escalateTo, $request->user());
            if ($doc) {
                $finding->update(['ministry_document_id' => $doc->id]);
                $escalationMsg = " Escalated via paperless workflow ({$doc->tracking_number}) to {$doc->destinationDepartment?->name}.";
            }
        }

        AuditService::log(
            'audit_finding_created',
            AuditFinding::class,
            $finding->id,
            [],
            ['finding_no' => $finding->finding_no, 'severity' => $finding->severity, 'audit_area' => $finding->audit_area],
            "Logged Internal Audit finding {$finding->finding_no} ({$finding->severity}): {$finding->title}"
        );

        return redirect()
            ->route('internal-audit.dashboard', ['tab' => 'findings'])
            ->with('success', "Audit Finding {$finding->finding_no} recorded.{$escalationMsg}");
    }

    public function updateFindingStatus(Request $request, AuditFinding $finding): RedirectResponse
    {
        $data = $request->validate([
            'status'              => ['required', Rule::in(AuditFinding::STATUSES)],
            'management_response' => 'nullable|string|max:2000',
        ]);

        $update = [
            'status'              => $data['status'],
            'management_response' => ($data['management_response'] ?? null) ?: $finding->management_response,
        ];

        if (in_array($data['status'], ['Remediated', 'Closed'], true)) {
            $update['resolved_by'] = $request->user()->id;
            $update['resolved_at'] = now();
        }

        $finding->update($update);

        AuditService::log(
            'audit_finding_updated',
            AuditFinding::class,
            $finding->id,
            [],
            ['finding_no' => $finding->finding_no, 'status' => $data['status']],
            "Updated Audit Finding {$finding->finding_no} status to {$data['status']}"
        );

        return redirect()
            ->route('internal-audit.dashboard', ['tab' => 'findings'])
            ->with('success', "Audit Finding {$finding->finding_no} updated to {$data['status']}.");
    }

    public function storeAssessment(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'department_id'   => 'required|exists:departments,id',
            'control_area'    => ['required', Rule::in(InternalControlAssessment::CONTROL_AREAS)],
            'assessment_date' => 'required|date',
            'rating'          => ['required', Rule::in(InternalControlAssessment::RATINGS)],
            'score'           => 'required|integer|min:0|max:100',
            'observations'    => 'required|string|max:2500',
            'action_plan'     => 'nullable|string|max:2000',
        ]);

        $assessment = InternalControlAssessment::create([
            'assessment_no'   => InternalControlAssessment::generateAssessmentNo(),
            'department_id'   => $data['department_id'],
            'control_area'    => $data['control_area'],
            'assessment_date' => $data['assessment_date'],
            'rating'          => $data['rating'],
            'score'           => (int) $data['score'],
            'observations'    => $data['observations'],
            'action_plan'     => ($data['action_plan'] ?? null) ?: null,
            'assessed_by'     => $request->user()->id,
        ]);

        AuditService::log(
            'internal_control_assessed',
            InternalControlAssessment::class,
            $assessment->id,
            [],
            ['assessment_no' => $assessment->assessment_no, 'rating' => $assessment->rating, 'score' => $assessment->score],
            "Recorded Internal Control Assessment {$assessment->assessment_no} ({$assessment->rating}, {$assessment->score}%)"
        );

        return redirect()
            ->route('internal-audit.dashboard', ['tab' => 'controls'])
            ->with('success', "Internal Control Assessment {$assessment->assessment_no} recorded ({$assessment->score}% — {$assessment->rating}).");
    }

    public function reportPdf(Request $request)
    {
        $anomalies = $this->runAnomalyScanner();
        $transactions = FinancialTransaction::with(['department', 'auditor', 'recorder'])
            ->latest('transaction_date')
            ->take(25)
            ->get();

        $findings = AuditFinding::with(['department', 'transaction', 'reporter'])
            ->latest('created_at')
            ->take(30)
            ->get();

        $assessments = InternalControlAssessment::with(['department', 'assessor'])
            ->orderByDesc('assessment_date')
            ->take(25)
            ->get();

        $summary = [
            'pending_vouchers'       => FinancialTransaction::where(fn ($q) => $q->whereNull('audit_status')->orWhere('audit_status', 'Pending Review'))->count(),
            'verified_vouchers'      => FinancialTransaction::where('audit_status', 'Verified')->count(),
            'flagged_vouchers'       => FinancialTransaction::whereIn('audit_status', ['Queried', 'Irregularity Flagged'])->count(),
            'open_findings'          => AuditFinding::whereIn('status', ['Open', 'Under Investigation'])->count(),
            'critical_high_findings' => AuditFinding::whereIn('status', ['Open', 'Under Investigation'])->whereIn('severity', ['Critical', 'High'])->count(),
            'exposure_usd'           => (float) AuditFinding::whereIn('status', ['Open', 'Under Investigation'])->sum('financial_impact_usd'),
            'avg_control_score'      => InternalControlAssessment::avg('score') !== null ? (int) round((float) InternalControlAssessment::avg('score')) : null,
            'active_anomalies'       => collect($anomalies)->where('count', '>', 0)->count(),
        ];

        $logoPath = public_path('images/logo.png');
        $logoDataUri = is_file($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;

        AuditService::log('exported', AuditFinding::class, null, [], ['findings_count' => $findings->count()], 'Exported Internal Audit Compliance & Irregularities Report PDF');

        return Pdf::loadView('internal-audit.report-pdf', compact(
            'summary',
            'anomalies',
            'transactions',
            'findings',
            'assessments',
            'logoDataUri'
        ))
            ->setPaper('a4', 'landscape')
            ->download('MYS-Internal-Audit-Report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportFindingsCsv()
    {
        $findings = AuditFinding::with(['department', 'transaction', 'document', 'reporter', 'resolver'])
            ->latest('created_at')
            ->get();

        AuditService::log('exported', AuditFinding::class, null, [], ['count' => $findings->count()], 'Exported Internal Audit Findings Registry CSV');

        return response()->streamDownload(function () use ($findings) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Finding No', 'Title', 'Audit Area', 'Department', 'Severity', 'Status',
                'Financial Impact (USD)', 'Linked Voucher', 'Escalated Tracking #',
                'Due Date', 'Reported By', 'Resolved By', 'Description', 'Recommendation', 'Management Response',
            ]);

            foreach ($findings as $f) {
                fputcsv($out, [
                    $f->finding_no,
                    $f->title,
                    $f->audit_area,
                    $f->department?->name ?? 'Ministry-Wide',
                    $f->severity,
                    $f->status,
                    $f->financial_impact_usd !== null ? number_format((float) $f->financial_impact_usd, 2, '.', '') : '0.00',
                    $f->transaction?->voucher_no ?? '—',
                    $f->document?->tracking_number ?? '—',
                    $f->due_date?->format('Y-m-d') ?? '—',
                    $f->reporter?->name ?? '—',
                    $f->resolver?->name ?? '—',
                    $f->description,
                    $f->recommendation,
                    $f->management_response ?: '—',
                ]);
            }
            fclose($out);
        }, 'MYS-Internal-Audit-Findings-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function runAnomalyScanner(): array
    {
        $paidNoBankRef = FinancialTransaction::where('status', 'Paid')
            ->where(function ($q) {
                $q->whereNull('bank_reference')->orWhere('bank_reference', '');
            })->count();

        $highValueUnverified = FinancialTransaction::where('amount_usd', '>=', 5000)
            ->where(function ($q) {
                $q->whereNull('audit_status')->orWhere('audit_status', 'Pending Review');
            })->count();

        $currentYear = now()->format('Y');
        $overBudgetCount = BudgetAllocation::where('fiscal_year', $currentYear)
            ->get()
            ->filter(fn (BudgetAllocation $b) => $b->utilization_percent >= 90 || $b->remaining_usd < 0)
            ->count();

        $staffMissingTinOrBank = Employee::where('employment_status', 'Active')
            ->where(function ($q) {
                $q->whereNull('tin_number')
                  ->orWhere('tin_number', '')
                  ->orWhereNull('bank_account')
                  ->orWhere('bank_account', '');
            })->count();

        $expiredActiveContracts = Employee::where('employment_type', 'Contract')
            ->where('employment_status', 'Active')
            ->whereNotNull('contract_end_date')
            ->whereDate('contract_end_date', '<', today())
            ->count();

        $overdueDocuments = MinistryDocument::whereNotIn('status', ['Completed', 'Rejected'])
            ->where('received_at_current_office_at', '<=', now()->subHours(48))
            ->count();

        $damagedAssets = Asset::whereIn('status', ['Damaged', 'Under Repair', 'Missing'])->count();

        return [
            [
                'category'   => 'Financial Control',
                'severity'   => $paidNoBankRef > 0 ? 'Critical' : 'Clear',
                'title'      => 'Paid Vouchers Missing Bank / Check Reference',
                'detail'     => 'Disbursed vouchers without a CBL transfer, check, or treasury reference number.',
                'count'      => $paidNoBankRef,
                'action_url' => route('finance.ledger', ['status' => 'Paid']),
            ],
            [
                'category'   => 'Voucher Verification',
                'severity'   => $highValueUnverified > 0 ? 'High' : 'Clear',
                'title'      => 'High-Value Vouchers (≥ USD 5,000) Unverified',
                'detail'     => 'Transactions at or above USD 5,000 awaiting Internal Audit verification.',
                'count'      => $highValueUnverified,
                'action_url' => route('internal-audit.dashboard', ['audit_status' => 'Pending Review']),
            ],
            [
                'category'   => 'Budget Discipline',
                'severity'   => $overBudgetCount > 0 ? 'High' : 'Clear',
                'title'      => "FY {$currentYear} Budget Lines ≥ 90% Utilized / Over-Spent",
                'detail'     => 'Departmental allotment lines nearing exhaustion or exceeding approved ceilings.',
                'count'      => $overBudgetCount,
                'action_url' => route('finance.dashboard'),
            ],
            [
                'category'   => 'Payroll Integrity',
                'severity'   => $staffMissingTinOrBank > 0 ? 'Medium' : 'Clear',
                'title'      => 'Active Personnel Missing TIN or Bank Account',
                'detail'     => 'Active employees/contractors lacking tax ID or verified banking details on file.',
                'count'      => $staffMissingTinOrBank,
                'action_url' => route('employees.index'),
            ],
            [
                'category'   => 'Contract Compliance',
                'severity'   => $expiredActiveContracts > 0 ? 'High' : 'Clear',
                'title'      => 'Expired Contracts Still Marked Active',
                'detail'     => 'Contractors whose contract end date has passed without formal renewal or closure.',
                'count'      => $expiredActiveContracts,
                'action_url' => route('contractors.index'),
            ],
            [
                'category'   => 'Workflow Bottlenecks',
                'severity'   => $overdueDocuments > 0 ? 'Medium' : 'Clear',
                'title'      => 'Ministry Documents Stalled ≥ 48 Hours',
                'detail'     => 'Paperless files/requests sitting in a single office beyond the 48-hour SLA.',
                'count'      => $overdueDocuments,
                'action_url' => route('documents.track'),
            ],
            [
                'category'   => 'Asset Custody',
                'severity'   => $damagedAssets > 0 ? 'Medium' : 'Clear',
                'title'      => 'Damaged, Under-Repair, or Missing Government Assets',
                'detail'     => 'Ministry vehicles or property requiring physical verification or GSA write-off review.',
                'count'      => $damagedAssets,
                'action_url' => route('assets.dashboard'),
            ],
        ];
    }

    private function escalateFindingToExecutive(AuditFinding $finding, string $targetCode, $user): ?MinistryDocument
    {
        $auditDept = Department::where('code', 'AUDIT')->first();
        $targetDept = Department::where('code', $targetCode)->first();
        if (!$targetDept) {
            return null;
        }

        $originId = $auditDept?->id ?? $user->employee?->department_id ?? $targetDept->id;
        $allowedIds = array_values(array_unique(array_filter([$originId, $targetDept->id, $finding->department_id])));

        $doc = MinistryDocument::create([
            'tracking_number'               => MinistryDocument::generateTrackingNumber(),
            'subject'                       => "[INTERNAL AUDIT {$finding->severity}] {$finding->finding_no}: {$finding->title}",
            'document_type'                 => 'Internal Audit Irregularity / Compliance Escalation',
            'priority'                      => $finding->severity === 'Critical' ? 'Urgent' : 'High',
            'confidentiality'               => 'Confidential',
            'origin_department_id'          => $originId,
            'current_department_id'         => $targetDept->id,
            'destination_department_id'     => $targetDept->id,
            'allowed_department_ids'        => $allowedIds,
            'sender_id'                     => $user->id,
            'amount_usd'                    => $finding->financial_impact_usd,
            'summary'                       => "Audit Area: {$finding->audit_area}\nFinding: {$finding->description}\n\nRecommendation: {$finding->recommendation}",
            'status'                        => 'Dispatched',
            'received_at_current_office_at' => now(),
        ]);

        DocumentRoutingLog::create([
            'ministry_document_id' => $doc->id,
            'from_department_id'   => $originId,
            'to_department_id'     => $targetDept->id,
            'action'               => 'Dispatched',
            'actor_id'             => $user->id,
            'actor_name'           => $user->name,
            'actor_role'           => $user->primary_role,
            'minutes_note'         => "Internal Audit escalated {$finding->finding_no} ({$finding->severity}) to {$targetDept->name} for executive intervention.",
            'digital_signature'    => 'AUD-ESC-' . strtoupper(substr(hash('sha256', $doc->tracking_number . '|' . $finding->finding_no . '|' . now()->toIso8601String()), 0, 10)),
            'acted_at'             => now(),
        ]);

        return $doc;
    }
}
