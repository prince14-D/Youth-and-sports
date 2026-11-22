<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('financial_transactions') && !Schema::hasColumn('financial_transactions', 'audit_status')) {
            Schema::table('financial_transactions', function (Blueprint $table) {
                $table->string('audit_status', 40)->default('Pending Review')->after('rejection_reason');
                $table->foreignId('audited_by')->nullable()->after('audit_status')->constrained('users')->nullOnDelete();
                $table->timestamp('audited_at')->nullable()->after('audited_by');
                $table->text('audit_notes')->nullable()->after('audited_at');
            });
        }

        if (!Schema::hasTable('audit_findings')) {
            Schema::create('audit_findings', function (Blueprint $table) {
                $table->id();
                $table->string('finding_no', 40)->unique();
                $table->string('title', 200);
                $table->string('audit_area', 80);
                $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
                $table->string('severity', 30)->default('Medium'); // Low, Medium, High, Critical
                $table->string('status', 40)->default('Open'); // Open, Under Investigation, Remediated, Closed
                $table->decimal('financial_impact_usd', 15, 2)->nullable();
                $table->text('description');
                $table->text('recommendation');
                $table->text('management_response')->nullable();
                $table->foreignId('financial_transaction_id')->nullable()->constrained('financial_transactions')->nullOnDelete();
                $table->unsignedBigInteger('ministry_document_id')->nullable();
                $table->date('due_date')->nullable();
                $table->foreignId('reported_by')->constrained('users')->cascadeOnDelete();
                $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['status', 'severity']);
                $table->index('department_id');
            });
        }

        if (!Schema::hasTable('internal_control_assessments')) {
            Schema::create('internal_control_assessments', function (Blueprint $table) {
                $table->id();
                $table->string('assessment_no', 40)->unique();
                $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
                $table->string('control_area', 100);
                $table->date('assessment_date');
                $table->string('rating', 40)->default('Effective'); // Effective, Needs Improvement, Deficient, Critical Weakness
                $table->unsignedTinyInteger('score')->default(80);
                $table->text('observations');
                $table->text('action_plan')->nullable();
                $table->foreignId('assessed_by')->constrained('users')->cascadeOnDelete();
                $table->timestamps();

                $table->index(['department_id', 'assessment_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('internal_control_assessments');
        Schema::dropIfExists('audit_findings');

        if (Schema::hasTable('financial_transactions') && Schema::hasColumn('financial_transactions', 'audit_status')) {
            Schema::table('financial_transactions', function (Blueprint $table) {
                $table->dropConstrainedForeignId('audited_by');
                $table->dropColumn(['audit_status', 'audited_at', 'audit_notes']);
            });
        }
    }
};
