<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->string('action'); // created, updated, deleted, login, logout, etc.
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('description')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->string('method', 10)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'created_at']);
            $table->index(['model_type', 'model_id']);
        });

        // Phase 2 stub tables (scaffolded now, populated in Phase 2)
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 10)->unique();
            $table->integer('days_allowed')->default(0);
            $table->boolean('is_paid')->default(true);
            $table->boolean('requires_approval')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('leave_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('days_requested');
            $table->text('reason')->nullable();
            $table->enum('status', ['Pending', 'Approved', 'Rejected', 'Cancelled'])->default('Pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('attendance_date');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->enum('status', ['Present', 'Absent', 'Late', 'Half Day', 'On Leave', 'Holiday'])->default('Present');
            $table->decimal('overtime_hours', 5, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('source')->default('manual'); // manual, biometric, gps
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->timestamps();
            $table->unique(['employee_id', 'attendance_date']);
        });

        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->date('payment_date')->nullable();
            $table->enum('status', ['Draft', 'Processing', 'Approved', 'Paid'])->default('Draft');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('payroll_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_period_id')->constrained('payroll_periods')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->decimal('basic_salary_lrd', 15, 2)->default(0);
            $table->decimal('basic_salary_usd', 15, 2)->default(0);
            $table->decimal('allowances_lrd', 15, 2)->default(0);
            $table->decimal('allowances_usd', 15, 2)->default(0);
            $table->decimal('overtime_lrd', 15, 2)->default(0);
            $table->decimal('deductions_lrd', 15, 2)->default(0);
            $table->decimal('tax_lrd', 15, 2)->default(0);
            $table->decimal('pension_lrd', 15, 2)->default(0);
            $table->decimal('net_pay_lrd', 15, 2)->default(0);
            $table->decimal('net_pay_usd', 15, 2)->default(0);
            $table->enum('status', ['Draft', 'Approved', 'Paid'])->default('Draft');
            $table->timestamps();
            $table->unique(['payroll_period_id', 'employee_id']);
        });

        Schema::create('performance_appraisals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->unsignedBigInteger('appraiser_id')->nullable();
            $table->year('appraisal_year');
            $table->string('appraisal_period')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->string('rating')->nullable(); // Excellent, Good, Satisfactory, Poor
            $table->text('comments')->nullable();
            $table->text('recommendations')->nullable();
            $table->enum('status', ['Draft', 'Submitted', 'Reviewed', 'Approved'])->default('Draft');
            $table->timestamps();
        });

        Schema::create('trainings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type'); // workshop, seminar, conference, online
            $table->text('description')->nullable();
            $table->string('provider')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('location')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('employee_trainings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('training_id')->constrained('trainings')->cascadeOnDelete();
            $table->enum('status', ['Registered', 'Attending', 'Completed', 'Dropped'])->default('Registered');
            $table->string('certificate_path')->nullable();
            $table->date('completion_date')->nullable();
            $table->timestamps();
        });

        Schema::create('vacancies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('department_id')->constrained('departments');
            $table->text('description')->nullable();
            $table->text('requirements')->nullable();
            $table->integer('positions')->default(1);
            $table->date('closing_date')->nullable();
            $table->enum('status', ['Draft', 'Open', 'Closed', 'Filled'])->default('Draft');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacancy_id')->constrained('vacancies')->cascadeOnDelete();
            $table->string('applicant_name');
            $table->string('email');
            $table->string('phone', 20)->nullable();
            $table->string('resume_path')->nullable();
            $table->enum('status', ['Applied', 'Shortlisted', 'Interviewed', 'Offered', 'Hired', 'Rejected'])->default('Applied');
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('vacancies');
        Schema::dropIfExists('employee_trainings');
        Schema::dropIfExists('trainings');
        Schema::dropIfExists('performance_appraisals');
        Schema::dropIfExists('payroll_records');
        Schema::dropIfExists('payroll_periods');
        Schema::dropIfExists('attendance');
        Schema::dropIfExists('leave_applications');
        Schema::dropIfExists('leave_types');
        Schema::dropIfExists('audit_logs');
    }
};
