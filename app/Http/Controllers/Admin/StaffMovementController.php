<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\StaffMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
class StaffMovementController extends Controller { public function index(): View { $movements=StaffMovement::with(['employee','fromDepartment','toDepartment'])->latest()->get(); $employees=Employee::active()->with('department')->orderBy('first_name')->get(); $departments=Department::active()->orderBy('name')->get(); return view('staff-movements.index',compact('movements','employees','departments')); } public function store(Request $r): RedirectResponse { $d=$r->validate(['employee_id'=>'required|exists:employees,id','movement_type'=>'required|in:Transfer,Promotion,Acting Appointment,Department Change','to_department_id'=>'nullable|exists:departments,id','to_designation'=>'nullable|string|max:150','effective_date'=>'required|date','reason'=>'required|string|max:1000']); $e=Employee::findOrFail($d['employee_id']); StaffMovement::create($d+['from_department_id'=>$e->department_id,'from_designation'=>$e->designation,'requested_by'=>$r->user()->id]); return back()->with('success','Staff movement submitted for approval.'); } public function approve(Request $r, StaffMovement $movement): RedirectResponse { $movement->update(['status'=>'Approved','reviewed_by'=>$r->user()->id,'reviewed_at'=>now()]); $movement->employee->update(['department_id'=>$movement->to_department_id ?: $movement->employee->department_id,'designation'=>$movement->to_designation ?: $movement->employee->designation]); return back()->with('success','Staff movement approved.'); } public function reject(Request $r, StaffMovement $movement): RedirectResponse { $movement->update(['status'=>'Rejected','reviewed_by'=>$r->user()->id,'reviewed_at'=>now()]); return back()->with('success','Staff movement rejected.'); } }
