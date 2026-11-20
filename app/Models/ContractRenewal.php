<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ContractRenewal extends Model { protected $fillable=['employee_id','current_end_date','proposed_end_date','reason','document_path','status','requested_by','reviewed_by','reviewed_at']; protected $casts=['current_end_date'=>'date','proposed_end_date'=>'date','reviewed_at'=>'datetime']; public function employee():BelongsTo{return $this->belongsTo(Employee::class);} }
