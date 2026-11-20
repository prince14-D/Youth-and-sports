<?php

namespace App\Livewire\Employees;

use Livewire\Component;
use App\Models\Employee;
use App\Models\AuditLog;

class Show extends Component
{
    public Employee $employee;
    public $activeTab = 'overview';

    public function mount(Employee $employee)
    {
        $this->employee = $employee->load([
            'department:id,name',
            'supervisor:id,first_name,last_name,designation',
            'education',
            'certifications',
            'documents',
        ]);
    }

    public function selectTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        // Fetch audit logs for this employee record
        $audits = AuditLog::where('model_type', Employee::class)
            ->where('model_id', $this->employee->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.employees.show', compact('audits'));
    }
}
