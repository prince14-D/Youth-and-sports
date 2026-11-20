<?php

namespace App\Services;

use App\Models\Employee;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmployeeService
{
    public function __construct(
        private readonly EmployeeRepositoryInterface $repository
    ) {}

    public function create(array $data, ?UploadedFile $photo = null): Employee
    {
        return DB::transaction(function () use ($data, $photo) {
            $data['employee_no'] = $this->generateEmployeeNumber();
            $data['created_by']  = auth()->id();

            if ($photo) {
                $data['photo_path'] = $this->storePhoto($photo, $data['employee_no']);
            }
            unset($data['photo']);

            $employee = $this->repository->create($data);

            // Store related records
            if (!empty($data['education'])) {
                $this->syncEducation($employee, $data['education']);
            }
            if (!empty($data['next_of_kin'])) {
                $this->syncNextOfKin($employee, $data['next_of_kin']);
            }

            return $employee;
        });
    }

    public function update(Employee $employee, array $data, ?UploadedFile $photo = null): Employee
    {
        return DB::transaction(function () use ($employee, $data, $photo) {
            $data['updated_by'] = auth()->id();

            if ($photo) {
                // Delete old photo
                if ($employee->photo_path) {
                    \Storage::disk('public')->delete($employee->photo_path);
                }
                $data['photo_path'] = $this->storePhoto($photo, $employee->employee_no);
            }
            unset($data['photo']);

            $this->repository->update($employee->id, $data);
            return $employee->fresh();
        });
    }

    public function generateEmployeeNumber(): string
    {
        $year   = now()->format('Y');
        $prefix = "MYS-{$year}-";
        $last   = Employee::withTrashed()
            ->where('employee_no', 'like', "{$prefix}%")
            ->orderByDesc('employee_no')
            ->value('employee_no');

        $next = $last
            ? (int) substr($last, strlen($prefix)) + 1
            : 1;

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    private function storePhoto(UploadedFile $photo, string $employeeNo): string
    {
        $filename = 'employees/photos/' . Str::slug($employeeNo) . '_' . time() . '.' . $photo->getClientOriginalExtension();
        $photo->storeAs('', $filename, 'public');
        return $filename;
    }

    private function syncEducation(Employee $employee, array $records): void
    {
        $employee->education()->delete();
        foreach ($records as $edu) {
            if (!empty($edu['institution'])) {
                $employee->education()->create($edu);
            }
        }
    }

    private function syncNextOfKin(Employee $employee, array $records): void
    {
        $employee->nextOfKin()->delete();
        foreach ($records as $kin) {
            if (!empty($kin['name'])) {
                $employee->nextOfKin()->create($kin);
            }
        }
    }

    public function getForDataTable(array $filters = []): \Illuminate\Support\Collection
    {
        return $this->repository->getFiltered($filters);
    }

    public function getStats(): array
    {
        return [
            'total'      => Employee::count(),
            'active'     => Employee::where('employment_status', 'Active')->count(),
            'permanent'  => Employee::where('employment_type', 'Permanent')->count(),
            'contract'   => Employee::where('employment_type', 'Contract')->count(),
            'male'       => Employee::where('gender', 'Male')->count(),
            'female'     => Employee::where('gender', 'Female')->count(),
        ];
    }
}
