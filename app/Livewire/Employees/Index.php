<?php

namespace App\Livewire\Employees;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Employee;
use App\Models\Department;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $departmentId = '';
    public $status = '';
    public $sortField = 'first_name';
    public $sortDirection = 'asc';

    protected $queryString = ['search', 'departmentId', 'status', 'sortField', 'sortDirection'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function deleteEmployee($id)
    {
        $employee = Employee::findOrFail($id);
        $name = $employee->full_name;
        $employee->delete();

        session()->flash('success', "Employee {$name} removed successfully.");
    }

    public function render()
    {
        $query = Employee::with('department')
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('first_name', 'like', '%' . $this->search . '%')
                        ->orWhere('last_name', 'like', '%' . $this->search . '%')
                        ->orWhere('employee_no', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->departmentId, function ($q) {
                $q->where('department_id', $this->departmentId);
            })
            ->when($this->status, function ($q) {
                $q->where('employment_status', $this->status);
            });

        $employees = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        $departments = Department::active()->orderBy('name')->get();

        return view('livewire.employees.index', compact('employees', 'departments'));
    }
}
