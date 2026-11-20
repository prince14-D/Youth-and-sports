<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Attendance;
use App\Models\PayrollRecord;
use App\Models\PerformanceAppraisal;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function index(): View
    {
        // 1. Department Headcount breakdown
        $departments = Department::withCount('employees')->get();
        $deptLabels = $departments->pluck('name')->toArray();
        $deptCounts = $departments->pluck('employees_count')->toArray();

        // 2. Attendance Stats & Lateness Rate
        $totalLogs = Attendance::count();
        $lateLogs = Attendance::where('status', 'Late')->count();
        $latenessRate = $totalLogs ? round(($lateLogs / $totalLogs) * 100, 1) : 0;

        // 3. Appraisals Bell Curve distribution
        $excelApp = PerformanceAppraisal::where('rating', 'Excellent')->count();
        $goodApp = PerformanceAppraisal::where('rating', 'Good')->count();
        $satApp = PerformanceAppraisal::where('rating', 'Satisfactory')->count();
        $poorApp = PerformanceAppraisal::where('rating', 'Poor')->count();
        $bellCurve = [$poorApp, $satApp, $goodApp, $excelApp];

        // 4. Salary Budget Expenditures
        // Sum total net pay from all records
        $budgetLrd = PayrollRecord::sum('net_pay_lrd');
        $budgetUsd = PayrollRecord::sum('net_pay_usd');

        return view('analytics.index', compact(
            'deptLabels', 'deptCounts', 'latenessRate',
            'bellCurve', 'budgetLrd', 'budgetUsd'
        ));
    }
}
