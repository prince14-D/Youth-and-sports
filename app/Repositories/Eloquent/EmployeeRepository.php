<?php

namespace App\Repositories\Eloquent;

use App\Models\Employee;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use Illuminate\Support\Collection;

class EmployeeRepository implements EmployeeRepositoryInterface
{
    public function all(): Collection
    {
        return Employee::with(['department', 'supervisor'])->get();
    }

    public function getFiltered(array $filters): Collection
    {
        $query = Employee::with(['department:id,name', 'supervisor:id,first_name,last_name']);

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }
        if (!empty($filters['employment_status'])) {
            $query->where('employment_status', $filters['employment_status']);
        }
        if (!empty($filters['employment_type'])) {
            $query->where('employment_type', $filters['employment_type']);
        }
        if (!empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_no', 'like', "%{$search}%")
                  ->orWhere('official_email', 'like', "%{$search}%")
                  ->orWhere('designation', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('first_name')->get();
    }

    public function findById(int $id): ?Employee
    {
        return Employee::with([
            'department',
            'supervisor:id,first_name,last_name,designation',
            'education',
            'certifications',
            'nextOfKin',
            'emergencyContacts',
            'documents',
        ])->find($id);
    }

    public function create(array $data): Employee
    {
        return Employee::create($data);
    }

    public function update(int $id, array $data): bool
    {
        return Employee::where('id', $id)->update($data);
    }

    public function delete(int $id): bool
    {
        return (bool) Employee::destroy($id);
    }
}
