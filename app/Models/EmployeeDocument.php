<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class EmployeeDocument extends Model {
    use SoftDeletes;
    protected $fillable=['employee_id','document_type','title','file_path','file_size','mime_type','expiry_date','uploaded_by'];
    protected $casts=['expiry_date'=>'date'];
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function getFileUrlAttribute(): string { return asset("storage/{$this->file_path}"); }
}
