<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveApplication;
use Carbon\Carbon;

class DashboardService
{
    public function getStats(): array
    {
        $totalEmployees  = Employee::count();
        $activeEmployees = Employee::active()->count();
        $onLeave         = $this->getEmployeesOnLeaveCount();
        $newHires        = Employee::where('date_joined', '>=', now()->startOfMonth())->count();
        $retiring        = Employee::retiringSoon(2)->count();

        return compact(
            'totalEmployees', 'activeEmployees', 'onLeave', 'newHires', 'retiring'
        );
    }

    public function getGenderDistribution(): array
    {
        $data = Employee::active()
            ->selectRaw('gender, COUNT(*) as count')
            ->groupBy('gender')
            ->pluck('count', 'gender')
            ->toArray();

        return [
            'labels' => array_keys($data),
            'data'   => array_values($data),
            'colors' => ['#000a60', '#d2143a', '#6c757d'],
        ];
    }

    public function getDepartmentStats(): array
    {
        $stats = Department::active()
            ->withCount(['employees as active_count' => function ($q) {
                $q->where('employment_status', 'Active');
            }])
            ->having('active_count', '>', 0)
            ->orderByDesc('active_count')
            ->get(['name', 'active_count']);

        return [
            'labels' => $stats->pluck('name')->toArray(),
            'data'   => $stats->pluck('active_count')->toArray(),
        ];
    }

    public function getEmploymentTypeBreakdown(): array
    {
        return Employee::active()
            ->selectRaw('employment_type, COUNT(*) as count')
            ->groupBy('employment_type')
            ->pluck('count', 'employment_type')
            ->toArray();
    }

    public function getUpcomingBirthdays(int $days = 14): \Illuminate\Support\Collection
    {
        return Employee::active()
            ->select('id', 'first_name', 'last_name', 'date_of_birth', 'photo_path', 'designation', 'department_id')
            ->with('department:id,name')
            ->get()
            ->filter(function ($emp) use ($days) {
                if (!$emp->date_of_birth) return false;
                $next = Carbon::parse($emp->date_of_birth->format('Y-m-d'))
                    ->setYear(now()->year);
                if ($next->isPast()) {
                    $next->addYear();
                }
                return $next->diffInDays(now()) <= $days;
            })
            ->sortBy(function ($emp) {
                $next = Carbon::parse($emp->date_of_birth->format('Y-m-d'))
                    ->setYear(now()->year);
                if ($next->isPast()) $next->addYear();
                return $next->diffInDays(now());
            })
            ->take(10)
            ->values();
    }

    public function getRetirementForecast(): \Illuminate\Support\Collection
    {
        return Employee::active()
            ->select('id', 'first_name', 'last_name', 'date_of_birth', 'designation', 'department_id', 'date_joined')
            ->with('department:id,name')
            ->get()
            ->filter(fn($e) => $e->date_of_birth && $e->date_of_birth->age >= 58)
            ->sortBy(fn($e) => $e->date_of_birth->age * -1)
            ->take(10)
            ->values();
    }

    public function getMonthlyHeadcountTrend(): array
    {
        $months = [];
        $counts = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M Y');
            $counts[]  = Employee::where('date_joined', '<=', $date->endOfMonth())
                ->where(function ($q) use ($date) {
                    $q->whereNull('deleted_at')
                      ->orWhere('deleted_at', '>', $date->endOfMonth());
                })
                ->where('employment_status', '!=', 'Terminated')
                ->count();
        }
        return ['labels' => $months, 'data' => $counts];
    }

    private function getEmployeesOnLeaveCount(): int
    {
        return LeaveApplication::where('status', 'Approved')
            ->where('start_date', '<=', today())
            ->where('end_date', '>=', today())
            ->distinct('employee_id')
            ->count('employee_id');
    }
}
