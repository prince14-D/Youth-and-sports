<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->foreignId('employee_id')->nullable()->after('vacancy_id')->constrained('employees')->nullOnDelete();
            $table->index(['employee_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('employee_id');
        });
    }
};