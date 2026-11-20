<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\View\View;
class ContractController extends Controller { public function index(): View { $contracts=Employee::with('department')->where('employment_type','Contract')->where('employment_status','Active')->orderBy('contract_end_date')->get(); return view('contracts.index',compact('contracts')); } }
