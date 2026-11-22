<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BudgetAllocation;
use App\Models\Department;
use App\Models\DocumentRoutingLog;
use App\Models\MinistryDocument;
use App\Models\ProcurementBid;
use App\Models\ProcurementRequest;
use App\Models\Supplier;
use App\Models\SupplierEvaluation;
use App\Services\AuditService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProcurementController extends Controller
{
    // ─────────────────────────────────────────────────────────────── Dashboard

    public function dashboard(Request $request): View
    {
        $activeTab = (string) $request->query('tab', 'tenders');
        $statusFilter = (string) $request->query('status', '');
        $supplierStatusFilter = (string) $request->query('supplier_status', '');
        $search = trim((string) $request->query('search', ''));

        $anomalies = $this->runComplianceScanner();

        $tenderQuery = ProcurementRequest::with(['requestingDepartment', 'awardedSupplier', 'creator'])
            ->withCount('bids')
            ->latest('id');
        if ($statusFilter !== '') {
            $tenderQuery->where('status', $statusFilter);
        }
        if ($search !== '') {
            $tenderQuery->where(function ($q) use ($search) {
                $q->where('tender_no', 'like', "%{$search}%")->orWhere('title', 'like', "%{$search}%");
            });
        }
        $tenders = $tenderQuery->take(50)->get();

        $supplierQuery = Supplier::withCount(['bids', 'awardedTenders'])->orderBy('name');
        if ($supplierStatusFilter !== '') {
            $supplierQuery->where('status', $supplierStatusFilter);
        }
        if ($search !== '' && $activeTab === 'suppliers') {
            $supplierQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('supplier_no', 'like', "%{$search}%");
            });
        }
        $suppliers = $supplierQuery->take(100)->get();

        $year = now()->format('Y');
        $awardedThisYear = ProcurementRequest::whereNotNull('awarded_at')->whereYear('awarded_at', $year)
            ->where('status', '!=', 'Cancelled')->get();

        $activeTenders = ProcurementRequest::whereIn('status', ['Open for Bids', 'Bid Evaluation']);

        $kpis = [
            'active_tenders'     => (clone $activeTenders)->count(),
            'pipeline_usd'       => (float) (clone $activeTenders)->sum('estimated_cost_usd'),
            'awarded_usd'        => (float) $awardedThisYear->sum('awarded_amount_usd'),
            'awarded_count'      => $awardedThisYear->count(),
            'savings_usd'        => round((float) $awardedThisYear->sum('estimated_cost_usd') - (float) $awardedThisYear->sum('awarded_amount_usd'), 2),
            'closing_soon'       => ProcurementRequest::where('status', 'Open for Bids')
                ->whereBetween('bid_deadline', [today(), today()->addDays(7)])->count(),
            'suppliers_active'   => Supplier::where('status', 'Active')->count(),
            'suppliers_flagged'  => Supplier::whereIn('status', ['Suspended', 'Blacklisted', 'Under Review'])->count(),
            'avg_rating'         => Supplier::whereNotNull('performance_rating')->avg('performance_rating'),
            'compliance_flags'   => collect($anomalies)->where('count', '>', 0)->count(),
            'drafts'             => ProcurementRequest::where('status', 'Draft')->count(),
        ];

        $recentEvaluations = SupplierEvaluation::with(['supplier', 'tender', 'evaluator'])->latest('id')->take(15)->get();

        $procDept = Department::where('code', 'PROC')->first();
        $incomingDocs = $procDept
            ? MinistryDocument::with(['originDepartment', 'sender'])
                ->where('current_department_id', $procDept->id)
                ->whereNotIn('status', ['Completed', 'Rejected'])
                ->latest('updated_at')->take(10)->get()
            : collect();

        $departments = Department::active()->orderBy('name')->get();
        $budgets = BudgetAllocation::with('department')->where('fiscal_year', $year)->get();

        return view('procurement.dashboard', [
            'kpis'                 => $kpis,
            'anomalies'            => $anomalies,
            'tenders'              => $tenders,
            'suppliers'            => $suppliers,
            'recentEvaluations'    => $recentEvaluations,
            'incomingDocs'         => $incomingDocs,
            'departments'          => $departments,
            'budgets'              => $budgets,
            'categories'           => ProcurementRequest::CATEGORIES,
            'methods'              => ProcurementRequest::METHODS,
            'statuses'             => ProcurementRequest::STATUSES,
            'supplierCategories'   => Supplier::CATEGORIES,
            'supplierStatuses'     => Supplier::STATUSES,
            'activeTab'            => $activeTab,
            'statusFilter'         => $statusFilter,
            'supplierStatusFilter' => $supplierStatusFilter,
            'search'               => $search,
        ]);
    }

    public function showTender(ProcurementRequest $tender): View
    {
        $tender->load(['requestingDepartment', 'budgetAllocation', 'awardedSupplier', 'creator', 'awarder', 'document']);
        $bids = $tender->bids()->with('supplier')->get()
            ->sortByDesc(fn ($b) => $b->total_score ?? -1)->values();

        $biddedSupplierIds = $bids->pluck('supplier_id')->all();
        $availableSuppliers = Supplier::where('status', 'Active')->whereNotIn('id', $biddedSupplierIds)->orderBy('name')->get();
        $evaluations = $tender->evaluations()->with(['supplier', 'evaluator'])->latest('id')->get();

        return view('procurement.show', compact('tender', 'bids', 'availableSuppliers', 'evaluations'));
    }

    // ─────────────────────────────────────────────────────────────── Tenders

    public function storeTender(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'                    => 'required|string|max:220',
            'description'              => 'nullable|string|max:3000',
            'requesting_department_id' => 'nullable|exists:departments,id',
            'category'                 => ['required', Rule::in(ProcurementRequest::CATEGORIES)],
            'method'                   => ['required', Rule::in(ProcurementRequest::METHODS)],
            'estimated_cost_usd'       => 'required|numeric|min:0',
            'budget_allocation_id'     => 'nullable|exists:budget_allocations,id',
            'bid_deadline'             => 'nullable|date',
            'justification'            => 'nullable|string|max:2500',
        ]);

        $isSole = str_starts_with($data['method'], 'Sole Source');
        if ($isSole && trim((string) ($data['justification'] ?? '')) === '') {
            return back()->withInput()->withErrors(['justification' => 'Sole-source / direct procurement requires a written justification.']);
        }

        $tender = ProcurementRequest::create([
            'tender_no'                => ProcurementRequest::generateTenderNo(),
            'title'                    => $data['title'],
            'description'              => ($data['description'] ?? null) ?: null,
            'requesting_department_id' => ($data['requesting_department_id'] ?? null) ?: null,
            'category'                 => $data['category'],
            'method'                   => $data['method'],
            'estimated_cost_usd'       => $data['estimated_cost_usd'],
            'budget_allocation_id'     => ($data['budget_allocation_id'] ?? null) ?: null,
            'bid_deadline'             => ($data['bid_deadline'] ?? null) ?: null,
            'justification'            => ($data['justification'] ?? null) ?: null,
            'status'                   => 'Draft',
            'created_by'               => $request->user()->id,
        ]);

        AuditService::log('procurement_tender_created', ProcurementRequest::class, $tender->id, [],
            ['tender_no' => $tender->tender_no, 'method' => $tender->method, 'estimated_cost_usd' => $tender->estimated_cost_usd],
            "Created procurement tender {$tender->tender_no}: {$tender->title}");

        return redirect()->route('procurement.tenders.show', $tender)->with('success', "Tender {$tender->tender_no} created as Draft.");
    }

    public function updateStatus(Request $request, ProcurementRequest $tender): RedirectResponse
    {
        $data = $request->validate([
            'status'               => ['required', Rule::in(['Open for Bids', 'Bid Evaluation', 'Contract Signed', 'Completed', 'Cancelled'])],
            'bid_deadline'         => 'nullable|date',
            'contract_signed_date' => 'nullable|date',
            'delivery_due_date'    => 'nullable|date',
            'cancellation_reason'  => 'nullable|string|max:1500',
        ]);

        $to = $data['status'];
        $from = $tender->status;
        $update = ['status' => $to];

        $allowed = [
            'Open for Bids'   => ['Draft'],
            'Bid Evaluation'  => ['Open for Bids', 'Draft'],
            'Contract Signed' => ['Awarded'],
            'Completed'       => ['Contract Signed'],
            'Cancelled'       => ['Draft', 'Open for Bids', 'Bid Evaluation', 'Awarded'],
        ];
        if (!in_array($from, $allowed[$to], true)) {
            return back()->with('error', "A tender in '{$from}' status cannot move to '{$to}'.");
        }

        if ($to === 'Open for Bids') {
            if ($tender->is_sole_source) {
                return back()->with('error', 'Sole-source procurement is not advertised — move it directly to Bid Evaluation.');
            }
            $deadline = ($data['bid_deadline'] ?? null) ?: $tender->bid_deadline?->toDateString();
            if (!$deadline) {
                return back()->with('error', 'Set a bid submission deadline before advertising the tender.');
            }
            $update['bid_deadline'] = $deadline;
            $update['advertised_date'] = today()->toDateString();
        }

        if ($to === 'Bid Evaluation') {
            if ($tender->is_sole_source === false && $from === 'Draft') {
                return back()->with('error', 'Advertise the tender (Open for Bids) before bid evaluation.');
            }
            if ($tender->bids()->count() < 1) {
                return back()->with('error', 'Record at least one bid/quotation before starting evaluation.');
            }
        }

        if ($to === 'Contract Signed') {
            if (empty($data['contract_signed_date']) || empty($data['delivery_due_date'])) {
                return back()->with('error', 'Contract signing date and delivery due date are required.');
            }
            $update['contract_signed_date'] = $data['contract_signed_date'];
            $update['delivery_due_date'] = $data['delivery_due_date'];
        }

        if ($to === 'Completed') {
            $update['completed_at'] = now();
        }

        if ($to === 'Cancelled') {
            if (trim((string) ($data['cancellation_reason'] ?? '')) === '') {
                return back()->with('error', 'A cancellation reason is required.');
            }
            $update['cancellation_reason'] = $data['cancellation_reason'];
        }

        $tender->update($update);

        AuditService::log('procurement_tender_status', ProcurementRequest::class, $tender->id, ['status' => $from],
            ['status' => $to], "Tender {$tender->tender_no} moved from {$from} to {$to}");

        return back()->with('success', "Tender {$tender->tender_no} is now '{$to}'.");
    }

    // ─────────────────────────────────────────────────────────────── Bids

    public function storeBid(Request $request, ProcurementRequest $tender): RedirectResponse
    {
        $data = $request->validate([
            'supplier_id'    => 'required|exists:suppliers,id',
            'bid_amount_usd' => 'required|numeric|min:0.01',
            'submitted_date' => 'required|date',
            'delivery_days'  => 'nullable|integer|min:1|max:1000',
            'notes'          => 'nullable|string|max:1500',
        ]);

        if (!in_array($tender->status, ['Draft', 'Open for Bids'], true) || ($tender->status === 'Draft' && !$tender->is_sole_source)) {
            return back()->with('error', 'Bids can only be recorded while the tender is Open for Bids (or Draft for sole-source).');
        }

        if ($tender->bids()->where('supplier_id', $data['supplier_id'])->exists()) {
            return back()->with('error', 'This supplier has already submitted a bid for this tender.');
        }

        $supplier = Supplier::findOrFail($data['supplier_id']);
        if (in_array($supplier->status, ['Blacklisted', 'Suspended'], true)) {
            return back()->with('error', "{$supplier->name} is {$supplier->status} and cannot bid.");
        }

        if ($tender->bid_deadline && !$tender->is_sole_source && \Carbon\Carbon::parse($data['submitted_date'])->gt($tender->bid_deadline)) {
            return back()->with('error', 'Submission date is after the bid deadline — late bids are not accepted.');
        }

        $bid = ProcurementBid::create([
            'procurement_request_id' => $tender->id,
            'supplier_id'            => $supplier->id,
            'bid_amount_usd'         => $data['bid_amount_usd'],
            'submitted_date'         => $data['submitted_date'],
            'delivery_days'          => ($data['delivery_days'] ?? null) ?: null,
            'notes'                  => ($data['notes'] ?? null) ?: null,
            'status'                 => 'Submitted',
            'recorded_by'            => $request->user()->id,
        ]);

        $tender->recomputeScores();

        AuditService::log('procurement_bid_recorded', ProcurementBid::class, $bid->id, [],
            ['tender_no' => $tender->tender_no, 'supplier' => $supplier->name, 'amount_usd' => $bid->bid_amount_usd],
            "Recorded bid from {$supplier->name} on {$tender->tender_no}");

        return back()->with('success', "Bid from {$supplier->name} recorded.");
    }

    public function scoreBid(Request $request, ProcurementBid $bid): RedirectResponse
    {
        $data = $request->validate([
            'technical_score' => 'nullable|integer|min:0|max:100',
            'is_responsive'   => 'required|boolean',
            'notes'           => 'nullable|string|max:1500',
        ]);

        $tender = $bid->tender;
        if (!in_array($tender->status, ['Open for Bids', 'Bid Evaluation', 'Draft'], true)) {
            return back()->with('error', 'Scores can no longer be changed after award.');
        }

        $responsive = (bool) $data['is_responsive'];
        $bid->update([
            'is_responsive'   => $responsive,
            'technical_score' => $responsive ? ($data['technical_score'] ?? null) : null,
            'status'          => $responsive ? $bid->status : 'Rejected',
            'notes'           => ($data['notes'] ?? null) ?: $bid->notes,
        ]);
        if ($responsive && $bid->status === 'Rejected') {
            $bid->update(['status' => 'Submitted']);
        }

        $tender->recomputeScores();

        AuditService::log('procurement_bid_scored', ProcurementBid::class, $bid->id, [],
            ['technical_score' => $data['technical_score'] ?? null, 'responsive' => $responsive],
            "Evaluated bid on {$tender->tender_no} (responsive: " . ($responsive ? 'yes' : 'no') . ')');

        return back()->with('success', 'Bid evaluation saved and scores recomputed.');
    }

    public function award(Request $request, ProcurementRequest $tender): RedirectResponse
    {
        $data = $request->validate([
            'bid_id'      => 'required|exists:procurement_bids,id',
            'award_note'  => 'nullable|string|max:2000',
            'waiver_note' => 'nullable|string|max:2000',
        ]);

        if ($tender->status !== 'Bid Evaluation') {
            return back()->with('error', 'Only tenders in Bid Evaluation can be awarded.');
        }

        $bid = ProcurementBid::with('supplier')->where('procurement_request_id', $tender->id)->find($data['bid_id']);
        if (!$bid) {
            return back()->with('error', 'Selected bid does not belong to this tender.');
        }
        if (!$bid->is_responsive) {
            return back()->with('error', 'A non-responsive bid cannot be awarded.');
        }

        if ($tender->is_sole_source) {
            if (trim((string) $tender->justification) === '') {
                return back()->with('error', 'Sole-source awards require a written justification on the tender.');
            }
        } else {
            if ($bid->technical_score === null) {
                return back()->with('error', 'Complete the technical evaluation of the selected bid first.');
            }
            $responsiveBids = $tender->bids()->where('is_responsive', true)->get();
            $unscored = $responsiveBids->whereNull('technical_score')->count();
            if ($unscored > 0) {
                return back()->with('error', "{$unscored} responsive bid(s) still lack technical scores — evaluate all bids before award.");
            }

            if ($responsiveBids->count() < ProcurementRequest::MIN_COMPETITIVE_BIDS && trim((string) ($data['waiver_note'] ?? '')) === '') {
                return back()->with('error', 'Competitive procurement requires at least ' . ProcurementRequest::MIN_COMPETITIVE_BIDS . ' responsive bids. Provide a documented waiver justification to proceed.');
            }

            $top = $responsiveBids->sortByDesc('total_score')->first();
            if ($top && $top->id !== $bid->id && trim((string) ($data['award_note'] ?? '')) === '') {
                return back()->with('error', "The selected bid is not the highest-ranked evaluation score ({$top->supplier?->name}). Provide a written deviation reason.");
            }
        }

        $reasons = $bid->supplier->ineligibilityReasons();
        if ($reasons) {
            return back()->with('error', "{$bid->supplier->name} is ineligible for award: " . implode('; ', $reasons) . '.');
        }

        $noteParts = array_filter([
            ($data['award_note'] ?? null) ?: null,
            !empty($data['waiver_note']) ? 'Reduced-competition waiver: ' . $data['waiver_note'] : null,
        ]);

        $tender->update([
            'status'              => 'Awarded',
            'awarded_supplier_id' => $bid->supplier_id,
            'awarded_bid_id'      => $bid->id,
            'awarded_amount_usd'  => $bid->bid_amount_usd,
            'awarded_at'          => now(),
            'awarded_by'          => $request->user()->id,
            'award_note'          => $noteParts ? implode("\n", $noteParts) : null,
        ]);

        $tender->bids()->where('id', '!=', $bid->id)->where('status', '!=', 'Rejected')->update(['status' => 'Rejected']);
        $bid->update(['status' => 'Awarded']);

        $docMsg = '';
        if ((float) $bid->bid_amount_usd >= ProcurementRequest::DMA_APPROVAL_THRESHOLD_USD) {
            $doc = $this->dispatchAwardToDma($tender->fresh(['awardedSupplier', 'requestingDepartment']), $request->user());
            if ($doc) {
                $tender->update(['ministry_document_id' => $doc->id]);
                $docMsg = " Award dossier {$doc->tracking_number} sent to DMA for approval.";
            }
        }

        AuditService::log('procurement_awarded', ProcurementRequest::class, $tender->id, [],
            ['tender_no' => $tender->tender_no, 'supplier' => $bid->supplier->name, 'amount_usd' => $bid->bid_amount_usd],
            "Awarded {$tender->tender_no} to {$bid->supplier->name} (USD " . number_format((float) $bid->bid_amount_usd, 2) . ')');

        return back()->with('success', "Tender {$tender->tender_no} awarded to {$bid->supplier->name}.{$docMsg}");
    }

    // ─────────────────────────────────────────────────────────────── Suppliers

    public function storeSupplier(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'                    => 'required|string|max:200',
            'category'                => ['required', Rule::in(Supplier::CATEGORIES)],
            'business_reg_no'         => 'nullable|string|max:80',
            'tin_number'              => 'nullable|string|max:60',
            'contact_person'          => 'nullable|string|max:120',
            'phone'                   => 'nullable|string|max:40',
            'email'                   => 'nullable|email|max:150',
            'address'                 => 'nullable|string|max:500',
            'tax_clearance_expiry'    => 'nullable|date',
            'business_license_expiry' => 'nullable|date',
        ]);

        $supplier = Supplier::create(array_merge(
            collect($data)->map(fn ($v) => $v === '' ? null : $v)->all(),
            ['supplier_no' => Supplier::generateSupplierNo(), 'status' => 'Active', 'registered_by' => $request->user()->id]
        ));

        AuditService::log('supplier_registered', Supplier::class, $supplier->id, [],
            ['supplier_no' => $supplier->supplier_no, 'name' => $supplier->name], "Registered supplier {$supplier->supplier_no}: {$supplier->name}");

        return redirect()->route('procurement.dashboard', ['tab' => 'suppliers'])
            ->with('success', "Supplier {$supplier->supplier_no} registered.");
    }

    public function updateSupplierStatus(Request $request, Supplier $supplier): RedirectResponse
    {
        $data = $request->validate([
            'status'                  => ['required', Rule::in(Supplier::STATUSES)],
            'status_reason'           => 'nullable|string|max:1500',
            'tax_clearance_expiry'    => 'nullable|date',
            'business_license_expiry' => 'nullable|date',
        ]);

        if (in_array($data['status'], ['Suspended', 'Blacklisted'], true) && trim((string) ($data['status_reason'] ?? '')) === '') {
            return back()->with('error', 'A written reason is required to suspend or blacklist a supplier.');
        }

        $old = $supplier->only(['status', 'tax_clearance_expiry', 'business_license_expiry']);
        $supplier->update([
            'status'                  => $data['status'],
            'status_reason'           => ($data['status_reason'] ?? null) ?: null,
            'tax_clearance_expiry'    => ($data['tax_clearance_expiry'] ?? null) ?: $supplier->tax_clearance_expiry,
            'business_license_expiry' => ($data['business_license_expiry'] ?? null) ?: $supplier->business_license_expiry,
        ]);

        AuditService::log('supplier_updated', Supplier::class, $supplier->id, $old,
            ['status' => $data['status']], "Supplier {$supplier->supplier_no} set to {$data['status']}");

        return redirect()->route('procurement.dashboard', ['tab' => 'suppliers'])
            ->with('success', "{$supplier->name} updated to {$data['status']}.");
    }

    public function storeEvaluation(Request $request, Supplier $supplier): RedirectResponse
    {
        $data = $request->validate([
            'procurement_request_id' => 'nullable|exists:procurement_requests,id',
            'quality_score'          => 'required|integer|min:1|max:5',
            'delivery_score'         => 'required|integer|min:1|max:5',
            'compliance_score'       => 'required|integer|min:1|max:5',
            'comments'               => 'nullable|string|max:1500',
        ]);

        $overall = round(($data['quality_score'] + $data['delivery_score'] + $data['compliance_score']) / 3, 2);

        $eval = SupplierEvaluation::create([
            'supplier_id'            => $supplier->id,
            'procurement_request_id' => ($data['procurement_request_id'] ?? null) ?: null,
            'quality_score'          => $data['quality_score'],
            'delivery_score'         => $data['delivery_score'],
            'compliance_score'       => $data['compliance_score'],
            'overall_score'          => $overall,
            'comments'               => ($data['comments'] ?? null) ?: null,
            'evaluated_by'           => $request->user()->id,
        ]);

        $supplier->update(['performance_rating' => round((float) $supplier->evaluations()->avg('overall_score'), 2)]);

        AuditService::log('supplier_evaluated', SupplierEvaluation::class, $eval->id, [],
            ['supplier' => $supplier->name, 'overall' => $overall], "Evaluated supplier {$supplier->name}: {$overall}/5");

        return back()->with('success', "Performance evaluation saved — {$supplier->name} now rated {$supplier->performance_rating}/5.");
    }

    // ─────────────────────────────────────────────────────────────── Reports

    public function tenderPdf(ProcurementRequest $tender)
    {
        $tender->load(['requestingDepartment', 'budgetAllocation', 'awardedSupplier', 'creator', 'awarder']);
        $bids = $tender->bids()->with('supplier')->get()->sortByDesc(fn ($b) => $b->total_score ?? -1)->values();
        $logoPath = public_path('images/logo.png');
        $logoDataUri = is_file($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;

        AuditService::log('exported', ProcurementRequest::class, $tender->id, [], [], "Exported bid evaluation report for {$tender->tender_no}");

        return Pdf::loadView('procurement.tender-pdf', compact('tender', 'bids', 'logoDataUri'))
            ->setPaper('a4', 'portrait')
            ->download("MYS-Bid-Evaluation-{$tender->tender_no}.pdf");
    }

    public function reportPdf(Request $request)
    {
        $tenders = ProcurementRequest::with(['requestingDepartment', 'awardedSupplier'])->withCount('bids')->latest('id')->take(60)->get();
        $suppliers = Supplier::orderBy('name')->get();
        $anomalies = $this->runComplianceScanner();

        $awarded = ProcurementRequest::whereNotNull('awarded_at')->where('status', '!=', 'Cancelled')->get();
        $summary = [
            'total_tenders'  => ProcurementRequest::count(),
            'active'         => ProcurementRequest::whereIn('status', ['Open for Bids', 'Bid Evaluation'])->count(),
            'awarded_count'  => $awarded->count(),
            'awarded_usd'    => (float) $awarded->sum('awarded_amount_usd'),
            'estimated_usd'  => (float) $awarded->sum('estimated_cost_usd'),
            'suppliers'      => $suppliers->count(),
            'flagged'        => collect($anomalies)->where('count', '>', 0)->count(),
        ];

        $logoPath = public_path('images/logo.png');
        $logoDataUri = is_file($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;

        AuditService::log('exported', ProcurementRequest::class, null, [], ['tenders' => $tenders->count()], 'Exported Procurement Unit report PDF');

        return Pdf::loadView('procurement.report-pdf', compact('tenders', 'suppliers', 'anomalies', 'summary', 'logoDataUri'))
            ->setPaper('a4', 'landscape')
            ->download('MYS-Procurement-Report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportTendersCsv()
    {
        $tenders = ProcurementRequest::with(['requestingDepartment', 'awardedSupplier', 'creator'])->withCount('bids')->latest('id')->get();

        AuditService::log('exported', ProcurementRequest::class, null, [], ['count' => $tenders->count()], 'Exported procurement tender register CSV');

        return response()->streamDownload(function () use ($tenders) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Tender No', 'Title', 'Requesting Dept', 'Category', 'Method', 'Estimated (USD)', 'Status', 'Bids', 'Bid Deadline', 'Awarded Supplier', 'Awarded (USD)', 'Awarded At', 'Created By']);
            foreach ($tenders as $t) {
                fputcsv($out, [
                    $t->tender_no, $t->title, $t->requestingDepartment?->name ?? 'Ministry-Wide', $t->category, $t->method,
                    number_format((float) $t->estimated_cost_usd, 2, '.', ''), $t->status, $t->bids_count,
                    $t->bid_deadline?->format('Y-m-d') ?? '—', $t->awardedSupplier?->name ?? '—',
                    $t->awarded_amount_usd !== null ? number_format((float) $t->awarded_amount_usd, 2, '.', '') : '—',
                    $t->awarded_at?->format('Y-m-d') ?? '—', $t->creator?->name ?? '—',
                ]);
            }
            fclose($out);
        }, 'MYS-Procurement-Tenders-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ─────────────────────────────────────────────────────────────── Compliance scanner

    public function runComplianceScanner(): array
    {
        $ceiling = ProcurementRequest::RFQ_CEILING_USD;
        $soleLimit = ProcurementRequest::SOLE_SOURCE_REVIEW_USD;
        $minBids = ProcurementRequest::MIN_COMPETITIVE_BIDS;

        $overdueOpen = ProcurementRequest::where('status', 'Open for Bids')->whereDate('bid_deadline', '<', today())->count();

        $thinCompetition = ProcurementRequest::whereNotNull('awarded_at')->where('status', '!=', 'Cancelled')
            ->where('method', 'not like', 'Sole Source%')->withCount('bids')->get()
            ->filter(fn ($t) => $t->bids_count < $minBids)->count();

        $soleSourceHigh = ProcurementRequest::where('method', 'like', 'Sole Source%')
            ->where('status', '!=', 'Cancelled')->where('estimated_cost_usd', '>=', $soleLimit)->count();

        $overEstimate = ProcurementRequest::whereNotNull('awarded_amount_usd')->where('estimated_cost_usd', '>', 0)
            ->get()->filter(fn ($t) => (float) $t->awarded_amount_usd > (float) $t->estimated_cost_usd * 1.10)->count();

        $rfqOverCeiling = ProcurementRequest::where('method', 'like', 'Request for Quotation%')
            ->where('status', '!=', 'Cancelled')->where('estimated_cost_usd', '>', $ceiling)->count();

        $expiredDocs = Supplier::where('status', 'Active')->where(function ($q) {
            $q->whereDate('tax_clearance_expiry', '<', today())->orWhereDate('business_license_expiry', '<', today());
        })->count();

        $missingTin = Supplier::where('status', 'Active')->where(function ($q) {
            $q->whereNull('tin_number')->orWhere('tin_number', '');
        })->count();

        $lateDelivery = ProcurementRequest::where('status', 'Contract Signed')->whereDate('delivery_due_date', '<', today())->count();

        $mk = fn (string $cat, string $sev, string $title, string $detail, int $count) => [
            'category' => $cat, 'severity' => $count > 0 ? $sev : 'Clear', 'title' => $title, 'detail' => $detail, 'count' => $count,
        ];

        return [
            $mk('Tender Timeliness', 'Medium', 'Open Tenders Past Bid Deadline', 'Advertised tenders whose deadline has passed and still await closing/evaluation.', $overdueOpen),
            $mk('Competition Rules', 'High', "Awards With Fewer Than {$minBids} Bids", 'Competitive awards made under a reduced-competition waiver — requires audit review.', $thinCompetition),
            $mk('Sole-Source Control', 'High', 'Sole-Source Procurement ≥ USD ' . number_format($soleLimit), 'Direct awards above the review limit that must be justified to DMA / Internal Audit.', $soleSourceHigh),
            $mk('Price Reasonableness', 'High', 'Awards >10% Above Estimate', 'Contracts awarded materially above the approved cost estimate.', $overEstimate),
            $mk('Method Threshold', 'High', 'RFQ Used Above USD ' . number_format($ceiling), 'Request-for-Quotation used where formal competitive bidding is required.', $rfqOverCeiling),
            $mk('Supplier Compliance', 'High', 'Active Suppliers With Expired Tax Clearance / License', 'Registered suppliers whose statutory documents have lapsed.', $expiredDocs),
            $mk('Supplier Registry', 'Medium', 'Active Suppliers Missing TIN', 'Vendors registered without a taxpayer identification number.', $missingTin),
            $mk('Contract Delivery', 'Medium', 'Contracts Past Delivery Due Date', 'Signed contracts overdue for delivery/completion.', $lateDelivery),
        ];
    }

    private function dispatchAwardToDma(ProcurementRequest $tender, $user): ?MinistryDocument
    {
        $procDept = Department::where('code', 'PROC')->first();
        $dma = Department::where('code', 'DMA')->first();
        if (!$dma) {
            return null;
        }
        $originId = $procDept?->id ?? $user->employee?->department_id ?? $dma->id;
        $allowed = array_values(array_unique(array_filter([$originId, $dma->id, $tender->requesting_department_id])));

        $doc = MinistryDocument::create([
            'tracking_number'               => MinistryDocument::generateTrackingNumber(),
            'subject'                       => "[PROCUREMENT AWARD] {$tender->tender_no}: {$tender->title}",
            'document_type'                 => 'Procurement Request',
            'priority'                      => 'High',
            'confidentiality'               => 'Confidential',
            'origin_department_id'          => $originId,
            'current_department_id'         => $dma->id,
            'destination_department_id'     => $dma->id,
            'allowed_department_ids'        => $allowed,
            'sender_id'                     => $user->id,
            'amount_usd'                    => $tender->awarded_amount_usd,
            'summary'                       => "Method: {$tender->method}\nRecommended supplier: {$tender->awardedSupplier?->name}\nAwarded amount: USD " . number_format((float) $tender->awarded_amount_usd, 2) . "\nEstimate: USD " . number_format((float) $tender->estimated_cost_usd, 2) . "\nRequesting office: " . ($tender->requestingDepartment?->name ?? 'Ministry-Wide'),
            'status'                        => 'Dispatched',
            'received_at_current_office_at' => now(),
        ]);

        DocumentRoutingLog::create([
            'ministry_document_id' => $doc->id,
            'from_department_id'   => $originId,
            'to_department_id'     => $dma->id,
            'action'               => 'Dispatched',
            'actor_id'             => $user->id,
            'actor_name'           => $user->name,
            'actor_role'           => $user->primary_role,
            'minutes_note'         => "Procurement Unit submitted award recommendation for {$tender->tender_no} to the DMA for approval.",
            'digital_signature'    => 'PROC-AWD-' . strtoupper(substr(hash('sha256', $doc->tracking_number . '|' . $tender->tender_no . '|' . now()->toIso8601String()), 0, 10)),
            'acted_at'             => now(),
        ]);

        return $doc;
    }
}
