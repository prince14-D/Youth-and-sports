<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Grievance extends Model { protected $fillable=['employee_id','submitted_by','subject','description','category','evidence_path','status','investigation_notes','decision','assigned_to']; public function employee():BelongsTo{return $this->belongsTo(Employee::class);} public function submitter():BelongsTo{return $this->belongsTo(User::class,'submitted_by');} public function assignee():BelongsTo{return $this->belongsTo(User::class,'assigned_to');} }