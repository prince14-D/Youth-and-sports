<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            $table->string('terminal_location')->nullable()->after('device_id');
            $table->string('attendance_method')->default('manual')->after('verification_method');
            $table->string('sync_status')->default('synced')->after('attendance_method');
            $table->timestamp('synced_at')->nullable()->after('sync_status');
            $table->string('submission_uuid')->nullable()->unique()->after('synced_at');
        });
    }

    public function down(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            $table->dropUnique(['submission_uuid']);
            $table->dropColumn([
                'terminal_location',
                'attendance_method',
                'sync_status',
                'synced_at',
                'submission_uuid',
            ]);
        });
    }
};
