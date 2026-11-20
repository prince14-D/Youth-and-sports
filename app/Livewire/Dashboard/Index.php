<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Employee;
use App\Models\Department;
use App\Models\LeaveApplication;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\HrTask;
use App\Models\Vacancy;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public $newTaskTitle = '';
    public $newTaskDescription = '';
    public $newTaskDueDate = '';
    public $showTaskModal = false;

    public function mount()
    {
        // Seed default HR tasks if none exist yet
        if (HrTask::count() == 0) {
            HrTask::create([
                'title'        => 'Review Attendance Logs',
                'description'  => 'Ensure all employee attendance records for today are verified',
                'due_date'     => now()->toDateString(),
                'is_completed' => false,
                'user_id'      => Auth::id(),
            ]);

            HrTask::create([
                'title'        => 'Verify Employee Document Submissions',
                'description'  => 'Review uploaded national IDs, TIN certificates, and appointment letters',
                'due_date'     => now()->addDay()->toDateString(),
                'is_completed' => false,
                'user_id'      => Auth::id(),
            ]);

            HrTask::create([
                'title'        => 'Prepare Monthly HRMIS Report',
                'description'  => 'Compile staff headcount and department distribution summary',
                'due_date'     => now()->addDays(2)->toDateString(),
                'is_completed' => false,
                'user_id'      => Auth::id(),
            ]);
        }
    }

    public function toggleTask($taskId)
    {
        $task = HrTask::find($taskId);
        if ($task) {
            $task->is_completed = !$task->is_completed;
            $task->completed_at = $task->is_completed ? now() : null;
            $task->save();
        }
    }

    public function openTaskModal()
    {
        $this->newTaskDueDate = now()->toDateString();
        $this->showTaskModal = true;
    }

    public function closeTaskModal()
    {
        $this->showTaskModal = false;
        $this->resetValidation();
    }

    public function createTask()
    {
        $this->validate([
            'newTaskTitle' => 'required|string|max:255',
            'newTaskDescription' => 'nullable|string|max:500',
            'newTaskDueDate' => 'nullable|date',
        ]);

        HrTask::create([
            'user_id'      => Auth::id(),
            'title'        => $this->newTaskTitle,
            'description'  => $this->newTaskDescription,
            'due_date'     => $this->newTaskDueDate ?: now()->toDateString(),
            'is_completed' => false,
        ]);

        $this->newTaskTitle = '';
        $this->newTaskDescription = '';
        $this->newTaskDueDate = '';
        $this->showTaskModal = false;

        session()->flash('success', 'New task added to Things To Do list!');
    }

    public function deleteTask($taskId)
    {
        $task = HrTask::find($taskId);
        if ($task) {
            $task->delete();
        }
    }

    public function render()
    {
        $headcount = Employee::count();
        $activeCount = Employee::active()->count();
        $departmentCount = Department::count();
        $newHiresThisMonth = Employee::whereMonth('date_joined', now()->month)->count();
        
        $retiringSoonCount = Employee::active()
            ->whereDate('date_of_birth', '<=', now()->subYears(58)->toDateString())
            ->count();

        $onLeave = LeaveApplication::where('status', 'Approved')
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->count();

        $pendingLeaveCount = LeaveApplication::where('status', 'Pending')->count();

        $presentToday = Attendance::where('attendance_date', now()->format('Y-m-d'))
            ->whereIn('status', ['Present', 'Late'])
            ->count();

        $absentRecords = Attendance::where('attendance_date', now()->format('Y-m-d'))
            ->where('status', 'Absent')
            ->count();

        $absentToday = $absentRecords > 0 
            ? $absentRecords 
            : max(0, $activeCount - $presentToday - $onLeave);

        // Avatars for KPI Cards
        $activeAvatars = Employee::active()->latest()->take(4)->get();
        $retiringAvatars = Employee::active()->whereDate('date_of_birth', '<=', now()->subYears(58)->toDateString())->take(4)->get();
        $newHireAvatars = Employee::active()->latest()->take(4)->get();

        // Monthly Work Hours Metrics (Current Month)
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth   = now()->endOfMonth()->toDateString();

        $monthAttendances = Attendance::whereBetween('attendance_date', [$startOfMonth, $endOfMonth])
            ->whereIn('status', ['Present', 'Late'])
            ->get();

        $totalMonthlyWorkHours = 0;
        foreach ($monthAttendances as $att) {
            if ($att->check_in && $att->check_out) {
                $checkIn = \Carbon\Carbon::parse($att->check_in);
                $checkOut = \Carbon\Carbon::parse($att->check_out);
                $hours = max(0, $checkOut->diffInMinutes($checkIn) / 60);
            } else {
                $hours = 8;
            }
            $totalMonthlyWorkHours += ($hours + ($att->overtime_hours ?? 0));
        }

        if ($totalMonthlyWorkHours == 0 && $activeCount > 0) {
            $workingDaysElapsed = min(22, now()->day);
            $totalMonthlyWorkHours = $activeCount * $workingDaysElapsed * 8;
        }

        $avgMonthlyWorkHoursPerEmployee = $activeCount > 0 
            ? round($totalMonthlyWorkHours / $activeCount, 1) 
            : 0;

        $totalMonthlyWorkHoursFormatted = number_format(round($totalMonthlyWorkHours));

        // Recent Employees List
        $recentEmployees = Employee::with('department')->latest()->take(4)->get();

        // Who's Away (Staff on Leave)
        $onLeaveStaff = LeaveApplication::with('employee.department')
            ->where('status', 'Approved')
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->latest()
            ->take(5)
            ->get();

        // Pending Leave Requests
        $pendingLeaveRequests = LeaveApplication::with('employee.department')
            ->where('status', 'Pending')
            ->latest()
            ->take(4)
            ->get();

        // Recent Vacancies / Recruitment
        $recentVacancies = Vacancy::withCount('applications')
            ->latest()
            ->take(4)
            ->get();

        $attendanceRate = $activeCount > 0 ? round(($presentToday / $activeCount) * 100) : 0;
        if ($attendanceRate >= 90) {
            $attendanceStatusLabel = 'Optimal';
        } elseif ($attendanceRate >= 75) {
            $attendanceStatusLabel = 'Moderate Concern';
        } else {
            $attendanceStatusLabel = 'Attention Needed';
        }

        // Things To Do tasks
        $tasks = HrTask::orderBy('is_completed', 'asc')
            ->orderBy('due_date', 'asc')
            ->take(5)
            ->get();

        // Recent Activity Logs
        $activities = AuditLog::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Birthdays in next 14 days
        $birthdays = Employee::active()
            ->upcomingBirthdays(14)
            ->limit(5)
            ->get();

        return view('livewire.dashboard.index', compact(
            'headcount', 'activeCount', 'departmentCount', 'newHiresThisMonth', 'retiringSoonCount', 'onLeave', 'pendingLeaveCount',
            'presentToday', 'absentToday', 'activeAvatars', 'retiringAvatars', 'newHireAvatars', 'totalMonthlyWorkHoursFormatted',
            'avgMonthlyWorkHoursPerEmployee', 'recentEmployees', 'onLeaveStaff', 'pendingLeaveRequests', 'recentVacancies',
            'attendanceRate', 'attendanceStatusLabel', 'tasks', 'activities', 'birthdays'
        ));
    }
}
