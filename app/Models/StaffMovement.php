<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class StaffMovement extends Model { protected $fillable=['employee_id','movement_type','from_department_id','to_department_id','from_designation','to_designation','effective_date','reason','status','requested_by','reviewed_by','reviewed_at']; protected $casts=['effective_date'=>'date','reviewed_at'=>'datetime']; public function employee(): BelongsTo { return $this->belongsTo(Employee::class); } public function fromDepartment(): BelongsTo { return $this->belongsTo(Department::class,'from_department_id'); } public function toDepartment(): BelongsTo { return $this->belongsTo(Department::class,'to_department_id'); } }
