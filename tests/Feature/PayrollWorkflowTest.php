<?php
namespace Tests\Feature;
use App\Models\Department;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\PayrollRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
class PayrollWorkflowTest extends TestCase { use RefreshDatabase; public function test_payroll_moves_through_review_approval_and_payment():void { $d=Department::create(['name'=>'Finance','code'=>'PAY','is_active'=>true]);$e=Employee::create(['employee_no'=>'PAY-001','first_name'=>'Pay','last_name'=>'Test','gender'=>'Male','designation'=>'Officer','employment_type'=>'Permanent','employment_status'=>'Active','date_joined'=>today(),'salary_amount_lrd'=>50000,'salary_amount_usd'=>250,'department_id'=>$d->id]); Permission::firstOrCreate(['name'=>'view payroll','guard_name'=>'web']);Permission::firstOrCreate(['name'=>'create payroll','guard_name'=>'web']);Permission::firstOrCreate(['name'=>'approve payroll','guard_name'=>'web']);$r=Role::firstOrCreate(['name'=>'Payroll Officer','guard_name'=>'web']);$r->givePermissionTo(['view payroll','create payroll','approve payroll']);$u=User::create(['name'=>'Payroll','email'=>'pay@test.example','password'=>bcrypt('secret123'),'is_active'=>true]);$u->assignRole($r);$p=PayrollPeriod::create(['name'=>'Test Cycle','start_date'=>today()->startOfMonth(),'end_date'=>today()->endOfMonth(),'status'=>'Draft','created_by'=>$u->id]);PayrollRecord::create(['payroll_period_id'=>$p->id,'employee_id'=>$e->id,'basic_salary_lrd'=>50000,'basic_salary_usd'=>250,'net_pay_lrd'=>50000,'net_pay_usd'=>250,'status'=>'Draft']);$this->actingAs($u)->post(route('payroll.review',$p))->assertRedirect();$this->assertSame('Processing',$p->fresh()->status);$this->actingAs($u)->post(route('payroll.approve',$p))->assertRedirect();$this->assertSame('Approved',$p->fresh()->status);$this->actingAs($u)->post(route('payroll.pay',$p),['payment_reference'=>'BANK-001'])->assertRedirect();$this->assertSame('Paid',$p->fresh()->status);$this->assertNotNull($p->fresh()->locked_at);$this->actingAs($u)->post(route('payroll.pay',$p))->assertStatus(422); } }
