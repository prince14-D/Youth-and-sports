<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Training;
use App\Models\EmployeeTraining;
use App\Models\Employee;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TrainingController extends Controller
{
    public function index(): View
    {
        $courses = Training::withCount('enrollments')
            ->orderBy('created_at', 'desc')
            ->get();

        $enrollments = EmployeeTraining::with(['employee.department', 'training'])
            ->orderBy('created_at', 'desc')
            ->get();

        $employees = Employee::active()->orderBy('first_name')->get();

        return view('training.index', compact('courses', 'enrollments', 'employees'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'       => 'required|string|max:100',
            'type'        => 'required|string|max:50',
            'provider'    => 'required|string|max:100',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'location'    => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        $data['is_active'] = true;

        $course = Training::create($data);

        AuditService::log('created', Training::class, $course->id,
            [], $course->toArray(),
            "Created training course: {$course->title}"
        );

        return redirect()->route('training.index')
            ->with('success', "Training course '{$course->title}' created successfully.");
    }

    public function enroll(Request $request): RedirectResponse
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'training_id' => 'required|exists:trainings,id',
        ]);

        // Check if already enrolled
        $exists = EmployeeTraining::where('employee_id', $request->employee_id)
            ->where('training_id', $request->training_id)
            ->exists();

        if ($exists) {
            return back()->with('error', "Employee is already enrolled in this training course.");
        }

        $enroll = EmployeeTraining::create([
            'employee_id' => $request->employee_id,
            'training_id' => $request->training_id,
            'status'      => 'Registered',
        ]);

        $employee = Employee::find($request->employee_id);
        $course = Training::find($request->training_id);

        AuditService::log('created', EmployeeTraining::class, $enroll->id,
            [], $enroll->toArray(),
            "Enrolled employee {$employee->full_name} in course: {$course->title}"
        );

        return redirect()->route('training.index')
            ->with('success', "Enrolled {$employee->full_name} in '{$course->title}' successfully.");
    }

    public function complete(Request $request, $id): RedirectResponse
    {
        $enroll = EmployeeTraining::findOrFail($id);
        $old = $enroll->toArray();

        $enroll->update([
            'status' => 'Completed',
            'completion_date' => now(),
        ]);

        AuditService::log('updated', EmployeeTraining::class, $enroll->id,
            $old, $enroll->fresh()->toArray(),
            "Marked training complete for: {$enroll->employee->full_name} ({$enroll->training->title})"
        );

        return redirect()->route('training.index')
            ->with('success', "Training course for {$enroll->employee->full_name} marked as Completed.");
    }

    public function destroyEnrollment($id): RedirectResponse
    {
        $enroll = EmployeeTraining::findOrFail($id);
        $name = $enroll->employee->full_name;
        $title = $enroll->training->title;
        $enroll->delete();

        AuditService::log('deleted', EmployeeTraining::class, $id,
            [], [],
            "Removed enrollment for {$name} in {$title}"
        );

        return redirect()->route('training.index')
            ->with('success', "Enrollment record removed.");
    }
}
