<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->unique();
            $table->string('name');
            $table->string('device_type')->default('tablet');
            $table->string('location')->nullable();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->string('token_hash');
            $table->string('software_version')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->ipAddress('last_ip_address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'department_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_devices');
    }
};
