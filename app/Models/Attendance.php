<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Attendance extends Model {
    protected $table = 'attendance';
    protected $fillable=['employee_id','attendance_date','check_in','clock_in_photo_path','check_out','clock_out_photo_path','status','overtime_hours','notes','source','recorded_by','latitude','longitude','device_id','verification_method','terminal_location','attendance_method','sync_status','synced_at','submission_uuid'];
    protected $casts=['attendance_date'=>'date','synced_at'=>'datetime'];
    public function employee() { return $this->belongsTo(Employee::class); }
    public function device() { return $this->belongsTo(AttendanceDevice::class, 'device_id', 'device_id'); }
}
