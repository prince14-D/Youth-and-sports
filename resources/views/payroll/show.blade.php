@extends('layouts.app')
@section('title', 'Payroll Cycle Details')
@section('page-title', 'Payroll Cycle Details')
@section('breadcrumb', 'HRMS › Finance & Payroll › Cycle Details')

@section('content')
<div class="animate-in">
    @php $authUser = auth()->user(); @endphp

    <div class="page-header flex-wrap">
        <div>
            <h1>{{ $period->name }}</h1>
            <p class="mb-0">
                Status:
                @if($period->status == 'Draft')
                    <span class="badge bg-secondary">Draft</span>
                @elseif($period->status == 'Processing')
                    <span class="badge bg-warning text-dark">In 3-Tier Approval ({{ $period->current_stage_label }})</span>
                @elseif($period->status == 'Approved')
                    <span class="badge bg-primary">Fully Approved — Ready for Payment</span>
                @else
                    <span class="badge bg-success">Paid &amp; Closed</span>
                @endif
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('payroll.index') }}" class="btn btn-white border shadow-xs rounded-pill px-3.5 py-2 text-navy fw-semibold" style="font-size: 13px; background: #ffffff;">
                <i class="bi bi-arrow-left text-primary me-1"></i>Back to Cycles
            </a>
            <a href="{{ route('approvals.index') }}" class="btn btn-outline-navy rounded-pill px-3.5 py-2 fw-semibold" style="font-size:13px;">
                <i class="bi bi-diagram-3 me-1"></i>Approval Center
            </a>
            <a href="{{ route('payroll.bank-schedule.csv', $period->id) }}" class="btn btn-outline-success rounded-pill px-3 py-2 fw-semibold" style="font-size:13px;" title="Download Bank-Ready CSV Schedule">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i>Bank Schedule (CSV)
            </a>
            <a href="{{ route('payroll.bank-schedule.pdf', $period->id) }}" class="btn btn-outline-danger rounded-pill px-3 py-2 fw-semibold" style="font-size:13px;" title="Download Treasury Payment Schedule PDF">
                <i class="bi bi-file-earmark-pdf me-1"></i>Treasury PDF
            </a>
            @if($period->status == 'Draft')
                <form action="{{ route('payroll.review', $period->id) }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-warning fw-semibold" onclick="return confirm('Submit this payroll cycle to Stage 1 (HR Officer Review)?')">
                        <i class="bi bi-send me-1"></i>Submit to HR Approval Workflow
                    </button>
                </form>
            @elseif($period->status == 'Processing')
                @if($period->canUserApprove($authUser))
                    <button type="button" class="btn btn-success fw-semibold" data-bs-toggle="modal" data-bs-target="#approvePayrollModal">
                        <i class="bi bi-pen-fill me-1"></i>{{ $period->next_action_label }}
                    </button>
                    <button type="button" class="btn btn-outline-danger fw-semibold" data-bs-toggle="modal" data-bs-target="#rejectPayrollModal">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Return to Draft
                    </button>
                @else
                    <span class="badge bg-light text-muted border d-inline-flex align-items-center px-3">
                        <i class="bi bi-lock me-1"></i>Awaiting {{ $period->current_stage_info['role'] }}
                    </span>
                @endif
            @endif
            @if(in_array($period->status, ['Approved', 'Paid']))
                <form action="{{ route('payroll.email-all', $period->id) }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-primary fw-semibold" onclick="return confirm('Email official PDF payslips & send portal notifications to all {{ $records->count() }} personnel in this cycle?')">
                        <i class="bi bi-envelope-paper-fill me-1"></i>Email All Payslips
                    </button>
                </form>
            @endif
            @if($period->status == 'Approved')
                <form action="{{ route('payroll.pay', $period->id) }}" method="POST" style="display:inline;">
                    @csrf
                    <input type="hidden" name="payment_reference" value="MYS-PAY-{{ $period->id }}-{{ now()->format('Ymd') }}">
                    <button type="submit" class="btn btn-success fw-semibold" onclick="return confirm('Confirm disbursement? This will finalize and lock the payroll cycle.')">
                        <i class="bi bi-wallet2 me-1"></i>Mark Paid &amp; Lock
                    </button>
                </form>
            @endif
            @if($period->locked_at)
                <span class="badge bg-dark align-self-center px-3 py-2"><i class="bi bi-lock me-1"></i>Locked</span>
            @endif
        </div>
    </div>

    <!-- 3-Tier Payroll Approval Pipeline Banner -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
        <div class="card-body py-3 px-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <div class="fw-bold text-navy small mb-1"><i class="bi bi-diagram-3-fill text-primary me-1"></i>3-Tier Payroll Sign-Off Pipeline</div>
                <x-ui.approval-stepper :model="$period" :uid="'payroll-trail-' . $period->id" />
                @if($period->rejection_reason && $period->status === 'Draft')
                    <div class="alert alert-danger py-1.5 px-2.5 mt-2 mb-0 small">
                        <i class="bi bi-exclamation-triangle me-1"></i><strong>Returned to Draft:</strong> {{ $period->rejection_reason }}
                    </div>
                @endif
            </div>
            <div class="text-md-end small text-muted">
                <div>Created by: <strong class="text-navy">{{ $period->creator?->name ?? 'System' }}</strong></div>
                <div>Period: {{ $period->start_date?->format('d M Y') }} &ndash; {{ $period->end_date?->format('d M Y') }}</div>
            </div>
        </div>
    </div>

    <!-- Cycle Totals Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4">
            <div class="card bg-navy text-white border-0 shadow-sm">
                <div class="card-body">
                    <div style="font-size:12px; text-transform:uppercase; opacity:0.8;">Total Net LRD</div>
                    <div style="font-size:24px; font-weight:700; margin-top:5px;">LRD {{ number_format($totals['lrd'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card bg-gold text-navy border-0 shadow-sm">
                <div class="card-body">
                    <div style="font-size:12px; text-transform:uppercase; opacity:0.8;">Total Net USD</div>
                    <div style="font-size:24px; font-weight:700; margin-top:5px;">USD {{ number_format($totals['usd'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card bg-light border shadow-sm">
                <div class="card-body">
                    <div class="text-muted" style="font-size:12px; text-transform:uppercase;">Staff Enrolled</div>
                    <div style="font-size:24px; font-weight:700; color:var(--navy); margin-top:5px;">{{ $records->count() }} Personnel</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Salaries breakdown grid -->
    <div class="card border-0 shadow-sm">
        <div class="card-header"><div class="card-header-title"><i class="bi bi-wallet"></i>Salaries Registry Breakdown</div></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:13.5px;">
                    <thead>
                        <tr style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                            <th class="ps-4 py-3">Employee</th>
                            <th class="py-3 text-end">Basic LRD</th>
                            <th class="py-3 text-end">Pension LRD</th>
                            <th class="py-3 text-end">Tax LRD</th>
                            <th class="py-3 text-end">Allowances LRD</th>
                            <th class="py-3 text-end">Net Pay LRD</th>
                            <th class="py-3 text-end">Net Pay USD</th>
                            <th class="pe-4 py-3 text-end">Payslip</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $rec)
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td class="ps-4 py-3">
                                <div style="font-weight:600; color:#1e293b;">{{ $rec->employee?->full_name ?? 'Employee Record Deleted' }}</div>
                                <div style="font-size:11px; color: #dc2626 !important;" class="fw-bold">{{ $rec->employee?->employee_no ?? '—' }}</div>
                            </td>
                            <td class="text-end font-monospace">{{ number_format($rec->basic_salary_lrd, 2) }}</td>
                            <td class="text-end font-monospace text-danger">-{{ number_format($rec->pension_lrd, 2) }}</td>
                            <td class="text-end font-monospace text-danger">-{{ number_format((float)$rec->tax_lrd + (float)$rec->deductions_lrd, 2) }}</td>
                            <td class="text-end font-monospace text-success">+{{ number_format((float)$rec->allowances_lrd + (float)$rec->overtime_lrd, 2) }}</td>
                            <td class="text-end font-monospace fw-bold text-navy">{{ number_format($rec->net_pay_lrd, 2) }}</td>
                            <td class="text-end font-monospace fw-bold text-accent">{{ number_format($rec->net_pay_usd, 2) }}</td>
                            <td class="pe-4 py-3 text-end">
                                @if($period->status === 'Draft')
                                <button type="button" class="btn btn-sm btn-outline-warning text-dark" data-bs-toggle="modal" data-bs-target="#itemsModal-{{ $rec->id }}" title="Custom Allowances & Deductions">
                                    <i class="bi bi-sliders"></i>
                                    @if($rec->lineItems->count() > 0)
                                        <span class="badge bg-navy">{{ $rec->lineItems->count() }}</span>
                                    @endif
                                </button>
                                @endif
                                <a href="{{ route('payroll.payslip', $rec->id) }}" class="btn btn-sm btn-outline-navy" title="View Payslip">
                                    <i class="bi bi-eye me-1"></i>View
                                </a>
                                <a href="{{ route('payroll.payslip.pdf', $rec->id) }}" class="btn btn-sm btn-outline-danger" title="Download PDF">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </a>
                                @if(in_array($period->status, ['Approved', 'Paid']))
                                <form action="{{ route('payroll.payslip.email', $rec->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary" title="Email PDF Payslip" onclick="return confirm('Email official PDF payslip to {{ addslashes($rec->employee?->full_name ?? 'this employee') }}?')">
                                        <i class="bi bi-envelope"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @if($period->status === 'Draft')
                        <div class="modal fade" id="itemsModal-{{ $rec->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-navy text-white">
                                        <h5 class="modal-title"><i class="bi bi-sliders me-2"></i>Itemized Allowances &amp; Deductions — {{ $rec->employee?->full_name }}</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        @if($rec->lineItems->isNotEmpty())
                                        <h6 class="fw-bold text-navy small text-uppercase mb-2">Current Custom Adjustments</h6>
                                        <div class="table-responsive mb-4">
                                            <table class="table table-sm align-middle border">
                                                <thead class="table-light">
                                                    <tr><th>Type</th><th>Category &amp; Notes</th><th class="text-end">Amount</th><th class="text-end">Remove</th></tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($rec->lineItems as $li)
                                                    <tr>
                                                        <td><span class="badge {{ $li->type === 'allowance' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ ucfirst($li->type) }}</span></td>
                                                        <td class="small">
                                                            <strong>{{ $li->category }}</strong>
                                                            @if($li->description)<div class="text-muted">{{ $li->description }}</div>@endif
                                                        </td>
                                                        <td class="text-end font-monospace fw-bold {{ $li->type === 'allowance' ? 'text-success' : 'text-danger' }}">
                                                            {{ $li->type === 'allowance' ? '+' : '-' }}{{ $li->currency }} {{ number_format((float)$li->amount, 2) }}
                                                        </td>
                                                        <td class="text-end">
                                                            <form method="POST" action="{{ route('payroll.line-items.destroy', $li->id) }}" class="d-inline">
                                                                @csrf @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2"><i class="bi bi-trash"></i></button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        @endif

                                        <h6 class="fw-bold text-navy small text-uppercase mb-2">Add Allowance or Deduction</h6>
                                        <form method="POST" action="{{ route('payroll.line-items.store', $rec->id) }}" class="row g-3">
                                            @csrf
                                            <div class="col-md-4">
                                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                                <select name="type" class="form-select" required>
                                                    <option value="allowance">Allowance (+)</option>
                                                    <option value="deduction">Deduction (-)</option>
                                                </select>
                                            </div>
                                            <div class="col-md-5">
                                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                                <select name="category" class="form-select" required>
                                                    <optgroup label="Allowances">
                                                        @foreach($allowanceCategories as $cat)<option value="{{ $cat }}">{{ $cat }}</option>@endforeach
                                                    </optgroup>
                                                    <optgroup label="Deductions">
                                                        @foreach($deductionCategories as $cat)<option value="{{ $cat }}">{{ $cat }}</option>@endforeach
                                                    </optgroup>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Currency <span class="text-danger">*</span></label>
                                                <select name="currency" class="form-select" required>
                                                    <option value="LRD">LRD</option>
                                                    <option value="USD">USD</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Amount <span class="text-danger">*</span></label>
                                                <input type="number" step="0.01" min="0.01" name="amount" class="form-control" placeholder="e.g. 5000" required>
                                            </div>
                                            <div class="col-md-8">
                                                <label class="form-label">Description / Reference</label>
                                                <input type="text" name="description" class="form-control" placeholder="Optional note displayed on payslip">
                                            </div>
                                            <div class="col-12 d-flex justify-content-end gap-2">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-navy"><i class="bi bi-plus-circle me-1"></i>Add &amp; Recalculate Net Pay</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">No records processed for this cycle.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@if($period->status === 'Processing' && $period->canUserApprove($authUser))
<!-- Approve Payroll Stage Modal -->
<div class="modal fade" id="approvePayrollModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('payroll.approve', $period->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-pen-fill me-2"></i>{{ $period->next_action_label }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="p-3 bg-light rounded border small mb-3">
                        <div class="fw-bold text-navy">{{ $period->name }} ({{ $records->count() }} personnel)</div>
                        <div class="text-muted">Total Net: LRD {{ number_format($totals['lrd'], 2) }} | USD {{ number_format($totals['usd'], 2) }}</div>
                    </div>
                    <label class="form-label">Digital Sign-Off Remarks <span class="text-muted">(optional)</span></label>
                    <textarea name="remarks" class="form-control" rows="3" placeholder="Verify salary totals, statutory deductions, or budget clearance..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Sign &amp; Advance Stage</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Return Payroll to Draft Modal -->
<div class="modal fade" id="rejectPayrollModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('payroll.reject', $period->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-arrow-counterclockwise me-2"></i>Return Payroll to Draft</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <label class="form-label">Reason for Returning Payroll <span class="text-danger">*</span></label>
                    <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Describe discrepancies or corrections required..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Return to Draft</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
