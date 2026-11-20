<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class LeaveApplication extends Model {
    use \Illuminate\Database\Eloquent\SoftDeletes;
    protected $fillable=['employee_id','leave_type_id','start_date','end_date','days_requested','reason','status','approved_by','approved_at','rejection_reason'];
    protected $casts=['start_date'=>'date','end_date'=>'date','approved_at'=>'datetime'];
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function leaveType(): BelongsTo { return $this->belongsTo(LeaveType::class); }
}
