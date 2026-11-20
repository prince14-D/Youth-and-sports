<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveApplication;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $employee = $user?->employee;

        if (!$employee) {
            abort(403, 'Employee record not found for this account.');
        }

        $today = now()->toDateString();
        $attendanceToday = Attendance::where('employee_id', $employee->id)
            ->whereDate('attendance_date', $today)
            ->latest()
            ->first();

        $leaveCount = LeaveApplication::where('employee_id', $employee->id)
            ->where('status', 'Approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->count();

        $leaveApplications = LeaveApplication::with('leaveType')
            ->where('employee_id', $employee->id)
            ->latest()
            ->take(5)
            ->get();
        $excuseApplications = \App\Models\ExcuseApplication::latest()->where('employee_id', $employee->id)->take(5)->get();
        $recruitmentApplications = \App\Models\JobApplication::with('vacancy')->where('employee_id', $employee->id)->latest()->take(5)->get();

        $profileSummary = [
            'employee' => $employee,
            'attendance_today' => $attendanceToday,
            'on_leave' => $leaveCount > 0,
            'department' => $employee->department?->name ?? 'Unassigned',
            'designation' => $employee->designation ?? 'Employee',
        ];

        return view('employee.dashboard', compact('profileSummary', 'employee', 'attendanceToday', 'leaveCount', 'leaveApplications', 'excuseApplications', 'recruitmentApplications'));
    }
}
