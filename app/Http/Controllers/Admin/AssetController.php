<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\Employee;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
class AssetController extends Controller { public function index(): View { $assets=Asset::with(['assignments.employee'])->latest()->get(); $employees=Employee::active()->orderBy('first_name')->get(); return view('assets.index',compact('assets','employees')); } public function store(Request $r): RedirectResponse { $d=$r->validate(['asset_tag'=>'required|string|max:50|unique:assets,asset_tag','name'=>'required|string|max:150','category'=>'required|string|max:50','serial_number'=>'nullable|string|max:100','condition'=>'required|string|max:50','purchase_date'=>'nullable|date','purchase_value'=>'nullable|numeric|min:0']); $a=Asset::create($d); AuditService::log('created',Asset::class,$a->id,[], $a->toArray(),"Created asset {$a->asset_tag}"); return back()->with('success','Asset added successfully.'); } public function assign(Request $r, Asset $asset): RedirectResponse { $d=$r->validate(['employee_id'=>'required|exists:employees,id','assigned_date'=>'required|date','condition_on_issue'=>'nullable|string|max:50','notes'=>'nullable|string|max:500']); $asset->assignments()->whereNull('returned_date')->update(['returned_date'=>today()]); $asset->assignments()->create($d+['assigned_by'=>$r->user()->id]); $asset->update(['status'=>'Assigned']); return back()->with('success','Asset assigned successfully.'); } }
