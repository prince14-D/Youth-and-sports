<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            $table->string('clock_in_photo_path')->nullable()->after('check_in');
            $table->string('clock_out_photo_path')->nullable()->after('check_out');
        });
    }

    public function down(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            $table->dropColumn(['clock_in_photo_path', 'clock_out_photo_path']);
        });
    }
};
