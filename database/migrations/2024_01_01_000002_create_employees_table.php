<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_no', 20)->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->enum('gender', ['Male', 'Female', 'Other']);
            $table->date('date_of_birth')->nullable();
            $table->string('nationality')->default('Liberian');
            $table->enum('marital_status', ['Single', 'Married', 'Divorced', 'Widowed'])->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('personal_email')->nullable();
            $table->string('official_email')->nullable()->unique();
            $table->string('photo_path')->nullable();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('designation');
            $table->string('job_title')->nullable();
            $table->enum('employment_type', ['Permanent', 'Contract', 'Casual', 'Intern'])->default('Permanent');
            $table->enum('employment_status', ['Active', 'Inactive', 'Suspended', 'Retired', 'Resigned', 'Terminated'])->default('Active');
            $table->date('date_joined');
            $table->date('contract_end_date')->nullable();
            $table->string('salary_grade')->nullable();
            $table->decimal('salary_amount_lrd', 15, 2)->nullable();
            $table->decimal('salary_amount_usd', 15, 2)->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account', 30)->nullable();
            $table->string('tin_number', 30)->nullable();
            $table->string('social_security_no', 30)->nullable();
            $table->foreignId('supervisor_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['employment_status', 'department_id']);
            $table->index('employee_no');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
