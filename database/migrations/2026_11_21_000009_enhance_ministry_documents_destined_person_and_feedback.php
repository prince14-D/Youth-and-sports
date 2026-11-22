<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ministry_documents') && !Schema::hasColumn('ministry_documents', 'destination_user_id')) {
            Schema::table('ministry_documents', function (Blueprint $table) {
                $table->foreignId('destination_user_id')->nullable()->after('destination_department_id')->constrained('users')->nullOnDelete();
                $table->string('attachment_original_name', 255)->nullable()->after('attachment_path');
                $table->text('latest_feedback')->nullable()->after('summary');
                $table->foreignId('latest_feedback_by')->nullable()->after('latest_feedback')->constrained('users')->nullOnDelete();
                $table->timestamp('latest_feedback_at')->nullable()->after('latest_feedback_by');
                $table->unsignedSmallInteger('revision_count')->default(0)->after('status');
            });
        }

        if (Schema::hasTable('document_routing_logs') && !Schema::hasColumn('document_routing_logs', 'attachment_path')) {
            Schema::table('document_routing_logs', function (Blueprint $table) {
                $table->foreignId('target_user_id')->nullable()->after('to_department_id')->constrained('users')->nullOnDelete();
                $table->string('attachment_path')->nullable()->after('minutes_note');
                $table->string('attachment_original_name', 255)->nullable()->after('attachment_path');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('document_routing_logs') && Schema::hasColumn('document_routing_logs', 'attachment_path')) {
            Schema::table('document_routing_logs', function (Blueprint $table) {
                $table->dropConstrainedForeignId('target_user_id');
                $table->dropColumn(['attachment_path', 'attachment_original_name']);
            });
        }

        if (Schema::hasTable('ministry_documents') && Schema::hasColumn('ministry_documents', 'destination_user_id')) {
            Schema::table('ministry_documents', function (Blueprint $table) {
                $table->dropConstrainedForeignId('destination_user_id');
                $table->dropConstrainedForeignId('latest_feedback_by');
                $table->dropColumn(['attachment_original_name', 'latest_feedback', 'latest_feedback_at', 'revision_count']);
            });
        }
    }
};
