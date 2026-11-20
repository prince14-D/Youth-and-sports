<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $employees = Employee::with('department:id,name')
            ->when($request->department_id, fn($q) => $q->where('department_id', $request->department_id))
            ->when($request->status, fn($q) => $q->where('employment_status', $request->status))
            ->select('id','employee_no','first_name','middle_name','last_name','designation','gender','department_id','employment_status','photo_path')
            ->orderBy('first_name')
            ->paginate(50);

        return response()->json($employees);
    }

    public function show(int $id): JsonResponse
    {
        $employee = Employee::with([
            'department:id,name,code',
            'supervisor:id,first_name,last_name,designation',
            'education',
            'certifications',
        ])->findOrFail($id);

        return response()->json($employee);
    }

    public function myProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->employee) {
            return response()->json(['message' => 'No employee record linked to this account.'], 404);
        }

        $employee = $user->employee->load([
            'department:id,name',
            'supervisor:id,first_name,last_name,designation',
            'education',
            'certifications',
            'nextOfKin',
            'emergencyContacts',
        ]);

        return response()->json($employee);
    }

    public function myPayslips(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->employee_id) {
            return response()->json(['message' => 'No employee record linked.'], 404);
        }
        $payslips = \App\Models\PayrollRecord::with('period')
            ->where('employee_id', $user->employee_id)
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($payslips);
    }

    public function myLeave(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->employee_id) {
            return response()->json(['message' => 'No employee record linked.'], 404);
        }
        $leave = \App\Models\LeaveApplication::with('leaveType')
            ->where('employee_id', $user->employee_id)
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($leave);
    }

    public function myAttendance(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->employee_id) {
            return response()->json(['message' => 'No employee record linked.'], 404);
        }
        $attendance = \App\Models\Attendance::where('employee_id', $user->employee_id)
            ->orderBy('attendance_date', 'desc')
            ->limit(30)
            ->get();
        return response()->json($attendance);
    }

    public function myNotifications(Request $request): JsonResponse
    {
        return response()->json($request->user()->notifications()->latest()->paginate(30));
    }

    public function myExcuses(Request $request): JsonResponse
    {
        return response()->json(\App\Models\ExcuseApplication::where('employee_id', $request->user()->employee_id)->latest()->paginate(30));
    }

    public function myApplications(Request $request): JsonResponse
    {
        return response()->json(\App\Models\JobApplication::with('vacancy')->where('employee_id', $request->user()->employee_id)->latest()->paginate(30));
    }
}
