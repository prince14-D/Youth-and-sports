<?php

namespace App\Livewire\Dashboard;

use App\Models\AuditLog;
use App\Models\ContractRenewal;
use App\Models\Department;
use App\Models\Employee;
use App\Models\ExcuseApplication;
use App\Models\HrTask;
use App\Models\LeaveApplication;
use App\Models\PayrollPeriod;
use App\Models\Vacancy;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public $newTaskTitle = '';
    public $newTaskDescription = '';
    public $newTaskDueDate = '';
    public $showTaskModal = false;

    public function mount()
    {
        if (HrTask::count() == 0) {
            HrTask::create([
                'title'        => 'Review Pending Leave & Excuse Requests',
                'description'  => 'Approve or review staff leave applications and submitted excuses',
                'due_date'     => now()->toDateString(),
                'is_completed' => false,
                'user_id'      => Auth::id(),
            ]);

            HrTask::create([
                'title'        => 'Verify Employee & Contractor Documents',
                'description'  => 'Review uploaded national IDs, TIN certificates, and signed contracts',
                'due_date'     => now()->addDay()->toDateString(),
                'is_completed' => false,
                'user_id'      => Auth::id(),
            ]);

            HrTask::create([
                'title'        => 'Prepare Monthly HRMIS Workforce Report',
                'description'  => 'Compile staff headcount, contract renewals, and department summary',
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
            'newTaskTitle'       => 'required|string|max:255',
            'newTaskDescription' => 'nullable|string|max:500',
            'newTaskDueDate'     => 'nullable|date',
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
        $newHiresThisMonth = Employee::whereMonth('date_joined', now()->month)
            ->whereYear('date_joined', now()->year)
            ->count();

        $activeContractorsCount = Employee::active()->where('employment_type', 'Contract')->count();
        $expiringContractsCount = Employee::active()
            ->where('employment_type', 'Contract')
            ->whereNotNull('contract_end_date')
            ->whereDate('contract_end_date', '<=', now()->addDays(60)->toDateString())
            ->count();

        $pendingExcusesCount = ExcuseApplication::where('status', 'Pending')->count();
        $pendingRenewalsCount = ContractRenewal::where('status', 'Pending')->count();
        $pendingPayrollCount = PayrollPeriod::whereIn('status', ['Draft', 'Processing'])->count();

        $retiringSoonCount = Employee::active()
            ->whereDate('date_of_birth', '<=', now()->subYears(58)->toDateString())
            ->count();

        $onLeave = LeaveApplication::where('status', 'Approved')
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->count();

        $pendingLeaveCount = LeaveApplication::where('status', 'Pending')->count();

        // Avatars for KPI Cards
        $activeAvatars = Employee::active()->latest()->take(4)->get();
        $retiringAvatars = Employee::active()->whereDate('date_of_birth', '<=', now()->subYears(58)->toDateString())->take(4)->get();
        $newHireAvatars = Employee::active()->latest()->take(4)->get();

        // Real Department Breakdown for Bar Chart
        $departmentStats = Department::withCount(['employees' => fn ($q) => $q->where('employment_status', 'Active')])
            ->orderByDesc('employees_count')
            ->take(8)
            ->get();
        $deptLabels = $departmentStats->pluck('name')->map(fn ($n) => \Illuminate\Support\Str::limit($n, 18))->values()->toArray();
        $deptCounts = $departmentStats->pluck('employees_count')->values()->toArray();

        // Real Employment Type Distribution for Doughnut Chart
        $permanentCount = Employee::active()->where('employment_type', 'Permanent')->count();
        $otherTypesCount = max(0, $activeCount - $permanentCount - $activeContractorsCount);
        $activeRate = $headcount > 0 ? round(($activeCount / $headcount) * 100) : 0;

        // Recent Employees List
        $recentEmployees = Employee::with('department')->latest()->take(4)->get();

        // Staff on Leave
        $onLeaveStaff = LeaveApplication::with('employee.department')
            ->where('status', 'Approved')
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->latest()
            ->take(5)
            ->get();

        $pendingLeaveRequests = LeaveApplication::with('employee.department')
            ->where('status', 'Pending')
            ->latest()
            ->take(4)
            ->get();

        $recentVacancies = Vacancy::withCount('applications')
            ->latest()
            ->take(4)
            ->get();

        $tasks = HrTask::orderBy('is_completed', 'asc')
            ->orderBy('due_date', 'asc')
            ->take(5)
            ->get();

        $activities = AuditLog::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $birthdays = Employee::active()
            ->upcomingBirthdays(14)
            ->limit(5)
            ->get();

        return view('livewire.dashboard.index', compact(
            'headcount', 'activeCount', 'departmentCount', 'newHiresThisMonth', 'retiringSoonCount',
            'onLeave', 'pendingLeaveCount', 'activeContractorsCount', 'expiringContractsCount',
            'pendingExcusesCount', 'pendingRenewalsCount', 'pendingPayrollCount',
            'activeAvatars', 'retiringAvatars', 'newHireAvatars',
            'deptLabels', 'deptCounts', 'permanentCount', 'otherTypesCount', 'activeRate',
            'recentEmployees', 'onLeaveStaff', 'pendingLeaveRequests', 'recentVacancies',
            'tasks', 'activities', 'birthdays'
        ));
    }
}
