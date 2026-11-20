<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('excuse_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('attendance_id')->nullable()->constrained('attendance')->nullOnDelete();
            $table->date('excuse_date');
            $table->string('excuse_type', 50);
            $table->text('reason');
            $table->string('attachment_path')->nullable();
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->unsignedBigInteger('submitted_by')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['employee_id', 'excuse_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('excuse_applications');
    }
};