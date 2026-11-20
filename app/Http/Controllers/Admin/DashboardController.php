<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Vacancy;
use App\Models\JobApplication;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user && $user->hasRole('Employee')) {
            $employee = $user->employee;

            if (!$employee) {
                abort(403, 'Employee record not found for this account.');
            }

            $today = now()->toDateString();
            $attendanceToday = Attendance::where('employee_id', $employee->id)
                ->whereDate('attendance_date', $today)
                ->latest()
                ->first();

            $leaveCount = \App\Models\LeaveApplication::where('employee_id', $employee->id)
                ->where('status', 'Approved')
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->count();

            $leaveApplications = \App\Models\LeaveApplication::with('leaveType')
                ->where('employee_id', $employee->id)
                ->latest()
                ->take(5)
                ->get();
            $excuseApplications = \App\Models\ExcuseApplication::latest()->where('employee_id', $employee->id)->take(5)->get();
            $recruitmentApplications = JobApplication::with('vacancy')->where('employee_id', $employee->id)->latest()->take(5)->get();

            return view('employee.dashboard', compact('employee', 'attendanceToday', 'leaveCount', 'leaveApplications', 'excuseApplications', 'recruitmentApplications'));
        }

        // Query the 4 specific recent applicants seeded to match screenshot layout exactly
        $recentApplicants = JobApplication::where('applicant_name', 'not like', 'Mock%')
            ->latest()
            ->take(4)
            ->get();

        // Query the seeded vacancies and count applications
        $vacancies = Vacancy::withCount('applications')
            ->latest()
            ->get();

        return view('dashboard.index', compact('recentApplicants', 'vacancies'));
    }
}
