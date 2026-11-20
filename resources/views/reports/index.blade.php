@extends('layouts.app')
@section('title', 'Reports Generator')
@section('page-title', 'Reports Center')
@section('breadcrumb', 'HRMS › Reports')

@section('content')
<div class="animate-in">
    <div class="page-header">
        <div>
            <h1>Reports Center</h1>
            <p>Generate, filter, and export formal reports for management and auditing purposes.</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Available Reports Cards -->
        @php
        $reports = [
            ['title' => 'Employee Master Registry', 'desc' => 'Comprehensive details of all active and inactive employees.', 'icon' => 'people-fill', 'url' => route('employees.index')],
            ['title' => 'Department Allocation Report', 'desc' => 'Headcount breakdown by operational departments.', 'icon' => 'diagram-3-fill', 'url' => route('departments.index')],
            ['title' => 'Gender Diversity Index', 'desc' => 'Statistical summary of gender distribution in the ministry.', 'icon' => 'gender-ambiguous', 'url' => route('dashboard')],
            ['title' => 'System Audit Log Report', 'desc' => 'A trail of user interactions, logins, modifications, and deletions.', 'icon' => 'journal-text', 'url' => route('audit.index')],
            ['title' => 'Retirement Forecast Report', 'desc' => 'Identifies staff nearing retirement age (60) in the next 1-5 years.', 'icon' => 'hourglass-split', 'url' => route('dashboard')],
        ];
        @endphp

        @can('view recruitment reports')
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100"><div class="card-body d-flex gap-3"><div style="width:48px;height:48px;border-radius:12px;background:rgba(78,96,255,.1);color:var(--accent-dark);display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;"><i class="bi bi-briefcase-fill"></i></div><div><h5 style="font-size:15px;font-weight:700;margin-bottom:6px;">Recruitment Pipeline Report</h5><p style="font-size:12px;color:var(--text-muted);margin-bottom:14px;min-height:36px;">Vacancy performance, candidate pipeline, and hiring funnel totals.</p><a href="{{ route('recruitment.reports') }}" class="btn btn-sm btn-navy"><i class="bi bi-file-earmark-bar-graph me-1"></i>Open Report</a></div></div></div>
        </div>
        @endcan

        @can('view reports')
        <div class="col-12 col-md-6 col-lg-4"><div class="card h-100"><div class="card-body d-flex gap-3"><div style="width:48px;height:48px;border-radius:12px;background:rgba(16,185,129,.1);color:#047857;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;"><i class="bi bi-bar-chart-fill"></i></div><div><h5 style="font-size:15px;font-weight:700;margin-bottom:6px;">Workforce Report</h5><p style="font-size:12px;color:var(--text-muted);margin-bottom:14px;min-height:36px;">Attendance exceptions, leave, excuses, and recruitment totals.</p><a href="{{ route('reports.workforce') }}" class="btn btn-sm btn-navy"><i class="bi bi-file-earmark-bar-graph me-1"></i>Open Report</a></div></div></div></div>
        @endcan

        @foreach($reports as $r)
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body d-flex gap-3">
                    <div style="width:48px;height:48px;border-radius:12px;background:rgba(78,96,255,.1);color:var(--accent-dark);display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;">
                        <i class="bi bi-{{ $r['icon'] }}"></i>
                    </div>
                    <div>
                        <h5 style="font-size:15px;font-weight:700;margin-bottom:6px;">{{ $r['title'] }}</h5>
                        <p style="font-size:12px;color:var(--text-muted);margin-bottom:14px;min-height:36px;">{{ $r['desc'] }}</p>
                        <a href="{{ $r['url'] }}" class="btn btn-sm btn-navy">
                            <i class="bi bi-file-earmark-bar-graph me-1"></i>Open Report
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
