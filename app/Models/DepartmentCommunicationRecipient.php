<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class DepartmentCommunicationRecipient extends Model { protected $fillable=['department_communication_id','employee_id','email','sent_at','read_at']; protected $casts=['sent_at'=>'datetime','read_at'=>'datetime']; public function communication():BelongsTo{return $this->belongsTo(DepartmentCommunication::class,'department_communication_id');} public function employee():BelongsTo{return $this->belongsTo(Employee::class);} }