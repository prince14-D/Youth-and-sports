<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ExcuseApplication;
use App\Models\JobApplication;
use App\Models\LeaveApplication;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class WorkforceReportController extends Controller
{
    public function index(Request $request): View
    {
        $from = $request->date('date_from')?->startOfDay() ?? now()->startOfMonth();
        $to = $request->date('date_to')?->endOfDay() ?? now()->endOfDay();
        $leave = LeaveApplication::with(['employee', 'leaveType'])->whereBetween('start_date', [$from->toDateString(), $to->toDateString()])->latest()->get();
        $excuses = ExcuseApplication::with('employee')->whereBetween('excuse_date', [$from->toDateString(), $to->toDateString()])->latest()->get();
        $attendance = Attendance::with('employee')->whereBetween('attendance_date', [$from->toDateString(), $to->toDateString()])->latest()->get();
        $pipeline = JobApplication::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        return view('reports.workforce', compact('from', 'to', 'leave', 'excuses', 'attendance', 'pipeline'));
    }

    public function csv(Request $request): Response
    {
        $from = $request->date('date_from')?->toDateString() ?? now()->startOfMonth()->toDateString();
        $to = $request->date('date_to')?->toDateString() ?? now()->toDateString();
        $rows = Attendance::with('employee')->whereBetween('attendance_date', [$from, $to])->get();
        $csv = "Employee,Date,Status,Check In,Check Out\n";
        foreach ($rows as $row) {
            $csv .= implode(',', array_map(fn ($value) => '"' . str_replace('"', '""', (string) $value) . '"', [$row->employee?->full_name, $row->attendance_date?->toDateString(), $row->status, $row->check_in, $row->check_out])) . "\n";
        }
        return response($csv, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="workforce-attendance-report.csv"']);
    }

    public function pdf(Request $request)
    {
        $from = $request->date('date_from')?->startOfDay() ?? now()->startOfMonth();
        $to = $request->date('date_to')?->endOfDay() ?? now()->endOfDay();
        $leave = LeaveApplication::with(['employee', 'leaveType'])->whereBetween('start_date', [$from->toDateString(), $to->toDateString()])->get();
        $excuses = ExcuseApplication::with('employee')->whereBetween('excuse_date', [$from->toDateString(), $to->toDateString()])->get();
        $attendance = Attendance::with('employee')->whereBetween('attendance_date', [$from->toDateString(), $to->toDateString()])->get();
        return Pdf::loadView('reports.workforce-pdf', compact('from', 'to', 'leave', 'excuses', 'attendance'))->download('workforce-report.pdf');
    }
}
