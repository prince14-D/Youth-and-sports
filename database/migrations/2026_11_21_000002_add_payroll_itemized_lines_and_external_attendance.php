<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_record_id')->constrained('payroll_records')->cascadeOnDelete();
            $table->string('type', 20); // allowance, deduction
            $table->string('category', 80);
            $table->string('description', 200)->nullable();
            $table->string('currency', 5)->default('LRD'); // LRD, USD
            $table->decimal('amount', 15, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['payroll_record_id', 'type']);
        });

        Schema::create('external_attendance_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_period_id')->nullable()->constrained('payroll_periods')->nullOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('period_month', 7); // YYYY-MM
            $table->unsignedSmallInteger('working_days')->default(22);
            $table->unsignedSmallInteger('days_present')->default(0);
            $table->unsignedSmallInteger('days_absent')->default(0);
            $table->unsignedSmallInteger('days_late')->default(0);
            $table->decimal('overtime_hours', 6, 2)->default(0);
            $table->string('source_system', 100)->default('External Attendance System');
            $table->string('notes', 255)->nullable();
            $table->foreignId('imported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['employee_id', 'period_month'], 'ext_att_emp_month_unique');
            $table->index('period_month');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_attendance_summaries');
        Schema::dropIfExists('payroll_line_items');
    }
};
