<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeCertification extends Model {
    protected $fillable=['employee_id','name','issuing_body','issue_date','expiry_date','certificate_path'];
    protected $casts=['issue_date'=>'date','expiry_date'=>'date'];
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
}
