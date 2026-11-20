@extends('layouts.app')
@section('title', 'Performance Appraisals')
@section('page-title', 'Performance Appraisals')
@section('breadcrumb', 'HRMS › Performance Management')

@section('content')
<div class="animate-in">
    <div class="page-header">
        <div>
            <h1>Performance Appraisals</h1>
            <p>Conduct reviews, record scores, and track ratings for Ministry personnel.</p>
        </div>
        <button class="btn btn-accent" data-bs-toggle="modal" data-bs-target="#newAppraisalModal">
            <i class="bi bi-patch-plus-fill me-1"></i>New Annual Appraisal
        </button>
    </div>



    <!-- Appraisal Analytics Cards -->
    <div class="row g-4 mb-4">
        <!-- Average Score -->
        <div class="col-12 col-sm-6 col-lg-3">
            <x-ui.kpi-card 
                title="Average Score" 
                value="{{ number_format($avgScore, 1) }}%" 
                subtitle="Ministry performance mean" 
                badgeColor="#0284C7" 
                badgeBg="#E0F2FE" 
                trend="Overall Score" 
                trendType="{{ $avgScore >= 75 ? 'up' : 'down' }}" />
        </div>

        <!-- Excellent Ratings -->
        <div class="col-12 col-sm-6 col-lg-3">
            <x-ui.kpi-card 
                title="Excellent Ratings" 
                value="{{ number_format($ratingsCount['Excellent']) }}" 
                subtitle="Grade A achievements" 
                badgeColor="#16A34A" 
                badgeBg="#DCFCE7" 
                trend="Top Tier" 
                trendType="up" />
        </div>

        <!-- Good & Satisfactory -->
        <div class="col-12 col-sm-6 col-lg-3">
            <x-ui.kpi-card 
                title="Good & Satisfactory" 
                value="{{ number_format($ratingsCount['Good'] + $ratingsCount['Satisfactory']) }}" 
                subtitle="Standard performance" 
                badgeColor="#D97706" 
                badgeBg="#FEF3C7" 
                trend="Target Met" 
                trendType="neutral" />
        </div>

        <!-- Poor Ratings -->
        <div class="col-12 col-sm-6 col-lg-3">
            <x-ui.kpi-card 
                title="Poor Ratings" 
                value="{{ number_format($ratingsCount['Poor']) }}" 
                subtitle="Requires improvement" 
                badgeColor="#EF4444" 
                badgeBg="#FEE2E2" 
                trend="PIP Required" 
                trendType="down" />
        </div>
    </div>

    <!-- Appraisals Grid Table -->
    <div class="card">
        <div class="card-header"><div class="card-header-title"><i class="bi bi-graph-up-arrow"></i>Appraisal Log ({{ $appraisals->count() }})</div></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                            <th class="ps-4 py-3">Employee</th>
                            <th class="py-3">Appraisal Year</th>
                            <th class="py-3 text-center">Score</th>
                            <th class="py-3 text-center">Rating</th>
                            <th class="py-3">Appraiser</th>
                            <th class="py-3">Comments</th>
                            <th class="pe-4 py-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appraisals as $app)
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td class="ps-4 py-3">
                                <div style="font-weight:600; color:#1e293b; font-size:13.5px;">{{ $app->employee?->full_name ?? 'Employee Record Deleted' }}</div>
                                <div style="font-size:11px; color: #dc2626 !important;" class="fw-bold">{{ $app->employee?->employee_no ?? '—' }}</div>
                            </td>
                            <td class="font-monospace" style="font-size:13px;">{{ $app->appraisal_year }}</td>
                            <td class="text-center font-monospace fw-bold" style="font-size:13px;">{{ number_format($app->score, 1) }}%</td>
                            <td class="text-center">
                                @if($app->rating == 'Excellent')
                                <span class="badge bg-success">Excellent</span>
                                @elseif($app->rating == 'Good')
                                <span class="badge bg-primary">Good</span>
                                @elseif($app->rating == 'Satisfactory')
                                <span class="badge bg-warning text-dark">Satisfactory</span>
                                @else
                                <span class="badge bg-danger">Poor</span>
                                @endif
                            </td>
                            <td style="font-size:13px; color:#334155;">{{ $app->appraiser->name ?? '—' }}</td>
                            <td style="font-size:12.5px; color:#64748b; max-width: 250px; text-overflow:ellipsis; overflow:hidden; white-space:nowrap;" title="{{ $app->comments }}">
                                {{ $app->comments ?? '—' }}
                            </td>
                            <td class="pe-4 py-3 text-end">
                                <form action="{{ route('performance.destroy', $app->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this appraisal?')" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-graph-up-arrow fs-2 d-block mb-2"></i>
                                No appraisals logged yet. Click "New Annual Appraisal" to submit.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- New Appraisal Modal -->
<div class="modal fade" id="newAppraisalModal" tabindex="-1" aria-labelledby="newAppraisalModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('performance.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="newAppraisalModalLabel"><i class="bi bi-patch-plus me-2 text-navy"></i>New Annual Appraisal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Employee <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Select Staff</option>
                            @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->employee_no }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Appraisal Year <span class="text-danger">*</span></label>
                            <input type="number" name="appraisal_year" class="form-control" value="{{ now()->year }}" min="2000" max="{{ now()->year + 1 }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Score (0 - 100%) <span class="text-danger">*</span></label>
                            <input type="number" name="score" class="form-control" placeholder="e.g. 87.5" step="0.1" min="0" max="100" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Review Comments / Achievements</label>
                        <textarea name="comments" class="form-control" rows="2" placeholder="List core milestones, achievements, areas of success..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Development Recommendations</label>
                        <textarea name="recommendations" class="form-control" rows="2" placeholder="Recommended training, course actions, career progression plan..."></textarea>
                    </div>
                    <div class="p-3 bg-light rounded text-muted" style="font-size:11.5px;">
                        <strong>Ratings calculation formula:</strong><br>
                        Score &ge; 85% = Excellent | &ge; 70% = Good | &ge; 50% = Satisfactory | &lt; 50% = Poor
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent">Log Appraisal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
