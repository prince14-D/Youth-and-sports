<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->unique()->constrained('employees')->cascadeOnDelete();
            $table->json('allowed_methods');
            $table->string('face_photo_path')->nullable();
            $table->string('qr_token')->nullable()->unique();
            $table->string('pin_hash')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamp('enrolled_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_enrollments');
    }
};
