@extends('layouts.app')
@section('title', 'Advanced Analytics')
@section('page-title', 'Advanced Analytics')
@section('breadcrumb', 'HRMS › Analytics')

@section('content')
<div class="animate-in">
    <div class="page-header">
        <div>
            <h1>Advanced Analytics</h1>
            <p>Interactive stats, metrics tracking, and intelligence logs for the Ministry.</p>
        </div>
    </div>

    <!-- Analytics KPI Stat Cards -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-4">
            <x-ui.kpi-card 
                title="Overall Lateness Rate" 
                value="{{ $latenessRate }}%" 
                subtitle="Verified via biometric GPS logs" 
                badgeText="%" 
                badgeColor="#0284C7" 
                badgeBg="#E0F2FE" 
                trend="Attendance Rate" 
                trendType="{{ $latenessRate <= 15 ? 'up' : 'down' }}" />
        </div>

        <div class="col-12 col-md-4">
            <x-ui.kpi-card 
                title="Total Net Payroll (LRD)" 
                value="LRD {{ number_format($budgetLrd, 2) }}" 
                subtitle="Monthly Liberian Dollar payout" 
                badgeText="L$" 
                badgeColor="#059669" 
                badgeBg="#D1FAE5" 
                trend="Disbursed" 
                trendType="up" />
        </div>

        <div class="col-12 col-md-4">
            <x-ui.kpi-card 
                title="Total Net Payroll (USD)" 
                value="USD {{ number_format($budgetUsd, 2) }}" 
                subtitle="Monthly United States Dollar payout" 
                badgeText="$" 
                badgeColor="#D97706" 
                badgeBg="#FEF3C7" 
                trend="Disbursed" 
                trendType="up" />
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="row g-4 mb-4">
        <!-- Dept distribution chart -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 16px;">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="card-title mb-0 text-navy fw-bold"><i class="bi bi-diagram-3-fill me-2 text-primary"></i>Staff Allocation by Department</h5>
                </div>
                <div class="card-body p-4">
                    <div style="height:320px; position:relative;">
                        <canvas id="deptChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Appraisals Bell Curve Chart -->
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 16px;">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="card-title mb-0 text-navy fw-bold"><i class="bi bi-graph-up me-2 text-info"></i>Performance Appraisals Bell Curve</h5>
                </div>
                <div class="card-body p-4">
                    <div style="height:320px; position:relative;">
                        <canvas id="appraisalChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // 1. Department Allocation Chart
    const deptCanvas = document.getElementById('deptChart');
    if (deptCanvas) {
        const deptCtx = deptCanvas.getContext('2d');
        const deptGradient = deptCtx.createLinearGradient(0, 0, 0, 300);
        deptGradient.addColorStop(0, '#2563eb');
        deptGradient.addColorStop(1, '#1e40af');

        new Chart(deptCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($deptLabels) !!},
                datasets: [{
                    label: 'Employees Count',
                    data: {!! json_encode($deptCounts) !!},
                    backgroundColor: deptGradient,
                    borderRadius: 8,
                    barThickness: 36
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Inter', size: 12, weight: '500' }, color: '#64748b' }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: { stepSize: 1, font: { family: 'Inter', size: 12 }, color: '#64748b' }
                    }
                }
            }
        });
    }

    // 2. Appraisal Distribution Chart
    const appCanvas = document.getElementById('appraisalChart');
    if (appCanvas) {
        const appCtx = appCanvas.getContext('2d');
        new Chart(appCtx, {
            type: 'doughnut',
            data: {
                labels: ['Poor (<50%)', 'Satisfactory (50-69%)', 'Good (70-84%)', 'Excellent (>=85%)'],
                datasets: [{
                    data: {!! json_encode($bellCurve) !!},
                    backgroundColor: ['#ef4444', '#f59e0b', '#3b82f6', '#10b981'],
                    borderWidth: 3,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { 
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 16,
                            font: { family: 'Inter', size: 12, weight: '500' }
                        }
                    }
                }
            }
        });
    }
</script>
@endpush
