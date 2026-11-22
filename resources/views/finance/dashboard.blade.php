@extends('layouts.app')
@section('title', 'Finance Division Dashboard')
@section('page-title', 'Finance Division Command Center')
@section('breadcrumb', 'Ministry of Youth & Sports › Finance Division')

@section('content')
<div class="animate-in">
    <!-- Hero Banner -->
    <div class="card border-0 shadow-sm mb-4 text-white overflow-hidden" style="background: linear-gradient(135deg, #0b1f4b 0%, #123f7a 55%, #047857 100%); border-radius: 16px;">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <span class="badge rounded-pill px-3 py-1 mb-2" style="background: rgba(255,255,255,0.18); font-size:11px; letter-spacing:0.8px;">
                        <i class="bi bi-bank2 text-warning me-1"></i>FINANCE &amp; ACCOUNTING DIVISION &bull; FY {{ $fiscalYear }}
                    </span>
                    <h3 class="fw-bold mb-1">Financial Transactions, Payments &amp; Accounting</h3>
                    <p class="mb-0 small opacity-85">Manage Ministry budgets, payment vouchers, departmental expenditures, payroll disbursements, and MFDP/audit financial statements.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-warning text-dark fw-semibold btn-sm px-3 py-2" data-bs-toggle="modal" data-bs-target="#newVoucherModal">
                        <i class="bi bi-receipt-cutoff me-1"></i>Record Payment / Voucher
                    </button>
                    <button type="button" class="btn btn-light text-navy fw-semibold btn-sm px-3 py-2" data-bs-toggle="modal" data-bs-target="#newBudgetModal">
                        <i class="bi bi-pie-chart-fill me-1 text-primary"></i>Allocate Budget
                    </button>
                    <a href="{{ route('finance.report.pdf', ['year' => $fiscalYear]) }}" class="btn btn-outline-light fw-semibold btn-sm px-3 py-2">
                        <i class="bi bi-file-earmark-pdf me-1"></i>Financial Statement PDF
                    </a>
                    <a href="{{ route('finance.ledger') }}" class="btn btn-outline-light fw-semibold btn-sm px-3 py-2">
                        <i class="bi bi-journal-bookmark me-1"></i>Full Ledger
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 6 Executive Finance KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-body">
                    <div class="text-muted small">FY {{ $fiscalYear }} Budget</div>
                    <div class="fs-5 fw-bold text-navy mt-1 font-monospace">${{ number_format($kpis['budget_usd'], 0) }}</div>
                    <div class="small text-muted">LRD {{ number_format($kpis['budget_lrd'], 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-body">
                    <div class="text-muted small">Expenditures YTD</div>
                    <div class="fs-5 fw-bold text-danger mt-1 font-monospace">${{ number_format($kpis['spent_usd'], 0) }}</div>
                    <div class="small text-muted">{{ $kpis['utilization_pct'] }}% utilized</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-body">
                    <div class="text-muted small">Budget Balance</div>
                    <div class="fs-5 fw-bold {{ $kpis['remaining_usd'] >= 0 ? 'text-success' : 'text-danger' }} mt-1 font-monospace">${{ number_format($kpis['remaining_usd'], 0) }}</div>
                    <div class="small text-muted">Receipts: ${{ number_format($kpis['receipts_usd'], 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-body">
                    <div class="text-muted small">Pending Vouchers</div>
                    <div class="fs-3 fw-bold text-warning mt-1">{{ $kpis['pending_vouchers'] }}</div>
                    <div class="small text-primary">{{ $kpis['approved_unpaid'] }} approved unpaid</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-body">
                    <div class="text-muted small">Latest Payroll</div>
                    <div class="fs-6 fw-bold text-navy mt-1 font-monospace">LRD {{ number_format($kpis['latest_payroll_lrd'], 0) }}</div>
                    <div class="small text-primary font-monospace">USD {{ number_format($kpis['latest_payroll_usd'], 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-body">
                    <div class="text-muted small">Finance Inbox Files</div>
                    <div class="fs-3 fw-bold text-primary mt-1">{{ $kpis['incoming_fin_docs'] }}</div>
                    <div class="small text-muted">Routed to Finance</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Incoming Files in Finance Office (e.g. DMA-Approved Salary/Payment Requests) -->
    @if($incomingFinanceDocs->isNotEmpty())
    <div class="card border-0 shadow-sm border-start border-4 border-primary mb-4" style="border-radius:14px;">
        <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-navy"><i class="bi bi-inbox-fill text-primary me-2"></i>Documents Routed to Finance Office ({{ $incomingFinanceDocs->count() }})</h5>
            <a href="{{ route('documents.index', ['tab' => 'inbox']) }}" class="btn btn-sm btn-outline-navy">Open Inbox</a>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th class="ps-4">Tracking # &amp; Subject</th><th>Origin</th><th>Value</th><th>Status</th><th class="text-end pe-4">Action</th></tr>
                </thead>
                <tbody>
                    @foreach($incomingFinanceDocs as $doc)
                        <tr>
                            <td class="ps-4 py-2">
                                <span class="badge bg-navy font-monospace">{{ $doc->tracking_number }}</span>
                                <span class="fw-bold text-navy small ms-1">{{ $doc->subject }}</span>
                            </td>
                            <td class="small">{{ $doc->originDepartment?->name }}</td>
                            <td class="small font-monospace">
                                {{ $doc->amount_usd ? 'USD ' . number_format((float)$doc->amount_usd, 2) : '' }}
                                {{ $doc->amount_lrd ? 'LRD ' . number_format((float)$doc->amount_lrd, 2) : '' }}
                            </td>
                            <td><span class="badge {{ $doc->status_badge_class }}">{{ $doc->status }}</span></td>
                            <td class="text-end pe-4"><a href="{{ route('documents.show', $doc) }}" class="btn btn-sm btn-outline-navy py-0 px-2">Open File</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="row g-4 mb-4">
        <!-- Monthly Expenditure Chart -->
        <div class="col-12 col-xl-7">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-navy"><i class="bi bi-bar-chart-line text-primary me-2"></i>FY {{ $fiscalYear }} Monthly Expenditure Trend (USD)</h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('finance.ledger.csv') }}" class="btn btn-sm btn-outline-success"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Ledger CSV</a>
                        <a href="{{ route('payroll.index') }}" class="btn btn-sm btn-outline-navy"><i class="bi bi-cash-stack me-1"></i>Payroll</a>
                    </div>
                </div>
                <div class="card-body">
                    <div style="height:260px;"><canvas id="financeMonthlyChart"></canvas></div>
                </div>
            </div>
        </div>

        <!-- Budget vs Actual by Allocation -->
        <div class="col-12 col-xl-5">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-navy"><i class="bi bi-pie-chart-fill text-success me-2"></i>Departmental Budget vs. Actual</h5>
                    <button type="button" class="btn btn-sm btn-outline-navy" data-bs-toggle="modal" data-bs-target="#newBudgetModal">+ Budget Line</button>
                </div>
                <div class="card-body" style="max-height:300px; overflow-y:auto;">
                    @forelse($budgets as $b)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <div>
                                    <strong class="text-navy">{{ $b->department?->name }}</strong>
                                    <span class="badge bg-light text-dark border font-monospace ms-1" style="font-size:10px;">{{ $b->budget_code }}</span>
                                    <div class="text-muted" style="font-size:11px;">{{ $b->category }}</div>
                                </div>
                                <div class="text-end font-monospace">
                                    <strong>${{ number_format($b->spent_usd, 0) }}</strong> / ${{ number_format((float)$b->allocated_usd, 0) }}
                                    <div class="text-muted" style="font-size:10.5px;">{{ $b->utilization_percent }}% used</div>
                                </div>
                            </div>
                            <div class="progress" style="height:6px;">
                                <div class="progress-bar {{ $b->utilization_percent >= 90 ? 'bg-danger' : ($b->utilization_percent >= 70 ? 'bg-warning' : 'bg-success') }}" style="width:{{ $b->utilization_percent }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted small py-5">
                            No budget lines allocated for FY {{ $fiscalYear }} yet. Click <strong>Allocate Budget</strong> above to add departmental lines.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Payment Vouchers & Financial Transactions -->
    <div class="card border-0 shadow-sm" style="border-radius:14px;">
        <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-navy"><i class="bi bi-receipt-cutoff text-primary me-2"></i>Recent Payment Vouchers &amp; Ledger Entries</h5>
            <a href="{{ route('finance.ledger') }}" class="btn btn-sm btn-outline-navy">Open Full Accounting Ledger</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Voucher # &amp; Payee</th>
                        <th>Department / Category</th>
                        <th>Date &amp; Method</th>
                        <th class="text-end">Amount (USD / LRD)</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentTransactions as $tx)
                        <tr>
                            <td class="ps-4 py-2.5">
                                <span class="badge bg-navy font-monospace">{{ $tx->voucher_no }}</span>
                                <span class="badge bg-light text-dark border ms-1" style="font-size:10px;">{{ $tx->transaction_type }}</span>
                                <div class="fw-bold text-navy mt-1">{{ $tx->payee_name }}</div>
                                <small class="text-muted">{{ \Illuminate\Support\Str::limit($tx->description, 60) }}</small>
                            </td>
                            <td class="small">
                                <div class="fw-semibold text-navy">{{ $tx->department?->name ?? 'Ministry HQ' }}</div>
                                <small class="text-muted">{{ $tx->category }}</small>
                            </td>
                            <td class="small">
                                <div>{{ $tx->transaction_date?->format('d M Y') }}</div>
                                <small class="text-muted">{{ $tx->payment_method }}</small>
                            </td>
                            <td class="text-end font-monospace small">
                                @if((float)$tx->amount_usd > 0)
                                    <div class="fw-bold {{ $tx->is_inflow ? 'text-success' : 'text-navy' }}">{{ $tx->is_inflow ? '+' : '' }}USD {{ number_format((float)$tx->amount_usd, 2) }}</div>
                                @endif
                                @if((float)$tx->amount_lrd > 0)
                                    <div class="text-muted">LRD {{ number_format((float)$tx->amount_lrd, 2) }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $tx->status_badge_class }}">{{ $tx->status }}</span>
                                @if($tx->document)
                                    <a href="{{ route('documents.show', $tx->document) }}" class="d-block small font-monospace text-decoration-none mt-1">{{ $tx->document->tracking_number }}</a>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('finance.vouchers.pdf', $tx) }}" class="btn btn-sm btn-outline-danger" title="Download Official Voucher PDF">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </a>
                                @if($tx->status === 'Pending DMA' && auth()->user()->can('approve payroll'))
                                    <form method="POST" action="{{ route('finance.transactions.status', $tx) }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="decision" value="Approved">
                                        <button type="submit" class="btn btn-sm btn-outline-primary" title="Approve">Approve</button>
                                    </form>
                                @elseif($tx->status === 'Approved')
                                    <form method="POST" action="{{ route('finance.transactions.status', $tx) }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="decision" value="Paid">
                                        <button type="submit" class="btn btn-sm btn-success" title="Mark Paid">Mark Paid</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-5 text-muted">No financial transactions recorded yet. Click <strong>Record Payment / Voucher</strong> above to begin.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Record Payment Voucher / Transaction -->
<div class="modal fade" id="newVoucherModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('finance.transactions.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-navy text-white">
                    <h5 class="modal-title"><i class="bi bi-receipt-cutoff me-2"></i>Record Financial Transaction / Payment Voucher</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Transaction Type <span class="text-danger">*</span></label>
                            <select name="transaction_type" class="form-select" required>
                                @foreach($transactionTypes as $tt)<option value="{{ $tt }}">{{ $tt }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Expenditure / Account Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select" required>
                                @foreach($categories as $cat)<option value="{{ $cat }}">{{ $cat }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payee / Beneficiary / Vendor <span class="text-danger">*</span></label>
                            <input name="payee_name" class="form-control" placeholder="e.g. Liberia Electricity Corp / Vendor / Staff" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department</label>
                            <select name="department_id" class="form-select">
                                <option value="">Ministry Headquarters</option>
                                @foreach($departments as $dept)<option value="{{ $dept->id }}">{{ $dept->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Transaction Date <span class="text-danger">*</span></label>
                            <input type="date" name="transaction_date" class="form-control" value="{{ today()->toDateString() }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Amount (USD)</label>
                            <input type="number" step="0.01" min="0" name="amount_usd" class="form-control" placeholder="0.00">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Amount (LRD)</label>
                            <input type="number" step="0.01" min="0" name="amount_lrd" class="form-control" placeholder="0.00">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Budget Line <span class="text-muted">(optional)</span></label>
                            <select name="budget_allocation_id" class="form-select">
                                <option value="">Unlinked</option>
                                @foreach($budgets as $b)
                                    <option value="{{ $b->id }}">{{ $b->budget_code }} ({{ $b->department?->code }} - {{ $b->category }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select" required>
                                @foreach($paymentMethods as $pm)<option value="{{ $pm }}">{{ $pm }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Check / Bank Reference</label>
                            <input name="bank_reference" class="form-control" placeholder="e.g. CBL-TR-8821">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description &amp; Narration <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control" rows="3" required placeholder="Detailed payment purpose, invoice number, or accounting narration..."></textarea>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label">Attach Supporting Invoice / Receipt</label>
                            <input type="file" name="attachment" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                        </div>
                        <div class="col-md-5 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="route_to_dma" value="1" id="routeDmaCheck" checked>
                                <label class="form-check-label small fw-semibold" for="routeDmaCheck">
                                    Route voucher to DMA for executive sign-off
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-navy"><i class="bi bi-check-circle me-1"></i>Generate Payment Voucher</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Allocate Budget Line -->
<div class="modal fade" id="newBudgetModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('finance.budgets.store') }}">
                @csrf
                <div class="modal-header bg-navy text-white">
                    <h5 class="modal-title"><i class="bi bi-pie-chart-fill me-2"></i>Allocate Departmental Budget Line</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label">Fiscal Year</label><input name="fiscal_year" class="form-control" value="{{ $fiscalYear }}" required></div>
                        <div class="col-md-8"><label class="form-label">Budget Code <span class="text-danger">*</span></label><input name="budget_code" class="form-control" placeholder="e.g. MYS-2026-YOUTH-01" required></div>
                        <div class="col-12">
                            <label class="form-label">Department <span class="text-danger">*</span></label>
                            <select name="department_id" class="form-select" required>
                                @foreach($departments as $dept)<option value="{{ $dept->id }}">{{ $dept->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Budget Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select" required>
                                @foreach($categories as $cat)<option value="{{ $cat }}">{{ $cat }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-6"><label class="form-label">Allocated (USD)</label><input type="number" step="0.01" min="0" name="allocated_usd" class="form-control" placeholder="0.00"></div>
                        <div class="col-md-6"><label class="form-label">Allocated (LRD)</label><input type="number" step="0.01" min="0" name="allocated_lrd" class="form-control" placeholder="0.00"></div>
                        <div class="col-12">
                            <label class="form-label">Funding Source</label>
                            <select name="funding_source" class="form-select">
                                @foreach($fundingSources as $fs)<option value="{{ $fs }}">{{ $fs }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-navy">Save Budget Allocation</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('financeMonthlyChart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($monthlyLabels),
            datasets: [{
                label: 'Expenditures (USD)',
                data: @json($monthlyUsd),
                backgroundColor: '#123F7A',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true }, x: { grid: { display: false } } }
        }
    });
});
</script>
@endpush
