<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceDevice;
use App\Models\Employee;
use App\Models\Department;
use App\Services\AuditService;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(private readonly AttendanceService $attendanceService)
    {
    }

    public function index(Request $request): View
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $departmentId = $request->get('department_id');

        $query = Attendance::with(['employee.department'])
            ->where('attendance_date', $date);

        if ($departmentId) {
            $query->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        $records = $query->orderBy('check_in', 'asc')->get();

        $stats = $this->attendanceService->dashboardStats(Carbon::parse($date));
        $devices = Schema::hasTable('attendance_devices')
            ? AttendanceDevice::latest()->take(5)->get()
            : collect();

        $departments = Department::active()->orderBy('name')->get();
        $employees = Employee::active()->orderBy('first_name')->get();

        return view('attendance.index', compact('records', 'date', 'departmentId', 'departments', 'employees', 'stats', 'devices'));
    }

    public function checkIn(Request $request): RedirectResponse
    {
        abort(403, 'Employees must clock in from the attendance terminal.');
    }

    public function checkOut(Request $request): RedirectResponse
    {
        abort(403, 'Employees must clock out from the attendance terminal.');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'employee_id'     => 'required|exists:employees,id',
            'attendance_date' => 'required|date',
            'check_in'        => 'nullable',
            'check_out'       => 'nullable',
            'status'          => 'required|in:Present,Absent,Late,Half Day,On Leave,Holiday',
            'notes'           => 'nullable|string|max:255',
        ]);

        $attendance = Attendance::updateOrCreate(
            ['employee_id' => $request->employee_id, 'attendance_date' => $request->attendance_date],
            [
                'check_in'    => $request->check_in,
                'check_out'   => $request->check_out,
                'status'      => $request->status,
                'notes'       => $request->notes,
                'source'      => 'manual',
                'recorded_by' => auth()->id(),
                'sync_status' => 'synced',
                'synced_at'   => now(),
            ]
        );

        $employee = Employee::find($request->employee_id);

        AuditService::log('created', Attendance::class, $attendance->id,
            [], $attendance->toArray(),
            "Manually logged attendance for employee: {$employee->full_name} on {$request->attendance_date} (Status: {$request->status})"
        );

        return back()->with('success', "Attendance record for {$employee->full_name} updated successfully.");
    }
}
