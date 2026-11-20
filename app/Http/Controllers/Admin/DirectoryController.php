<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\View\View;
class DirectoryController extends Controller { public function index(Request $r): View { $employees=Employee::with(['department','supervisor'])->active()->when($r->filled('q'),fn($q)=>$q->where(fn($x)=>$x->where('first_name','like','%'.$r->q.'%')->orWhere('last_name','like','%'.$r->q.'%')->orWhere('employee_no','like','%'.$r->q.'%')))->when($r->filled('department_id'),fn($q)=>$q->where('department_id',$r->department_id))->orderBy('first_name')->get(); $departments=Department::active()->orderBy('name')->get(); return view('directory.index',compact('employees','departments')); } }
