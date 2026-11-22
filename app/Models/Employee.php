<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_no', 'personnel_number', 'first_name', 'middle_name', 'last_name',
        'gender', 'date_of_birth', 'nationality', 'county_of_birth', 'marital_status',
        'address', 'phone', 'personal_email', 'official_email', 'photo_path',
        'department_id', 'designation', 'job_title', 'employment_type',
        'employment_status', 'date_joined', 'contract_end_date',
        'salary_grade', 'salary_amount_lrd', 'salary_amount_usd',
        'bank_name', 'bank_account', 'tin_number', 'social_security_no',
        'supervisor_id', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'date_of_birth'     => 'date',
        'date_joined'       => 'date',
        'contract_end_date' => 'date',
        'salary_amount_lrd' => 'decimal:2',
        'salary_amount_usd' => 'decimal:2',
    ];

    protected function salaryAmountLrd(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            set: fn ($value) => (is_numeric($value) && $value !== '') ? (float)$value : 0.00,
        );
    }

    protected function salaryAmountUsd(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            set: fn ($value) => (is_numeric($value) && $value !== '') ? (float)$value : 0.00,
        );
    }

    // ─── Relationships ───────────────────────────────────────────────────────

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'supervisor_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(Employee::class, 'supervisor_id');
    }

    public function education(): HasMany
    {
        return $this->hasMany(EmployeeEducation::class);
    }

    public function certifications(): HasMany
    {
        return $this->hasMany(EmployeeCertification::class);
    }

    public function nextOfKin(): HasMany
    {
        return $this->hasMany(EmployeeNextOfKin::class);
    }

    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(EmployeeEmergencyContact::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function leaveApplications(): HasMany
    {
        return $this->hasMany(LeaveApplication::class);
    }

    public function excuseApplications(): HasMany
    {
        return $this->hasMany(ExcuseApplication::class);
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function attendanceEnrollment(): HasOne
    {
        return $this->hasOne(AttendanceEnrollment::class);
    }

    public function payrollRecords(): HasMany
    {
        return $this->hasMany(PayrollRecord::class);
    }

    public function assetAssignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class);
    }

    public function contractRenewals(): HasMany
    {
        return $this->hasMany(ContractRenewal::class);
    }

    public function trainings(): HasMany
    {
        return $this->hasMany(EmployeeTraining::class);
    }

    public function appraisals(): HasMany
    {
        return $this->hasMany(PerformanceAppraisal::class);
    }

    public function grievances(): HasMany
    {
        return $this->hasMany(Grievance::class);
    }

    public function isContractor(): bool
    {
        return $this->employment_type === 'Contract';
    }

    public function getContractDaysRemainingAttribute(): ?int
    {
        if (!$this->contract_end_date) {
            return null;
        }
        return (int) today()->diffInDays($this->contract_end_date, false);
    }

    public function getContractProgressPercentAttribute(): ?int
    {
        if (!$this->date_joined || !$this->contract_end_date) {
            return null;
        }
        $totalDays = max(1, $this->date_joined->diffInDays($this->contract_end_date));
        $elapsed = max(0, min($totalDays, $this->date_joined->diffInDays(today(), false)));
        return (int) round(($elapsed / $totalDays) * 100);
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('employment_status', 'Active');
    }

    public function scopeByDepartment($query, int $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeOnLeave($query)
    {
        return $query->whereHas('leaveApplications', function ($q) {
            $q->where('status', 'Approved')
              ->where('start_date', '<=', now())
              ->where('end_date', '>=', now());
        });
    }

    public function scopeUpcomingBirthdays($query, int $days = 30)
    {
        // substr() works the same in MySQL and SQLite, unlike DATE_FORMAT/DATE_ADD
        $today = now();
        $range = collect(range(0, $days))->map(fn ($d) => $today->copy()->addDays($d)->format('m-d'));

        return $query->whereIn(DB::raw('substr(date_of_birth, 6, 5)'), $range);
    }

    public function scopeRetiringSoon($query, int $years = 2)
    {
        // Retirement age 60 in Liberia; avoid MySQL-only TIMESTAMPDIFF for SQLite compatibility
        $maxBirthDate = now()->subYears(60 - $years)->toDateString();
        $minBirthDate = now()->subYears(60)->toDateString();

        return $query->whereBetween('date_of_birth', [$minBirthDate, $maxBirthDate]);
    }

    // ─── Accessors ───────────────────────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth?->age;
    }

    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo_path && file_exists(public_path("storage/{$this->photo_path}"))) {
            return asset("storage/{$this->photo_path}");
        }
        return asset("images/default-avatar.png");
    }

    public function getYearsOfServiceAttribute(): ?string
    {
        if (!$this->date_joined) return null;
        $diff = $this->date_joined->diff(now());
        return "{$diff->y}y {$diff->m}m";
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->employment_status) {
            'Active'     => '<span class="badge bg-success">Active</span>',
            'Inactive'   => '<span class="badge bg-secondary">Inactive</span>',
            'Suspended'  => '<span class="badge bg-warning text-dark">Suspended</span>',
            'Retired'    => '<span class="badge bg-info">Retired</span>',
            'Resigned'   => '<span class="badge bg-dark">Resigned</span>',
            'Terminated' => '<span class="badge bg-danger">Terminated</span>',
            default      => '<span class="badge bg-secondary">'.$this->employment_status.'</span>',
        };
    }
}
