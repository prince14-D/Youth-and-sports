<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_trails', function (Blueprint $table) {
            $table->id();
            $table->string('approvable_type');
            $table->unsignedBigInteger('approvable_id');
            $table->unsignedTinyInteger('stage')->default(1);
            $table->string('stage_label', 120);
            $table->string('required_role', 80)->nullable();
            $table->string('action', 30); // submitted, approved, rejected
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_name', 120)->nullable();
            $table->string('actor_role', 80)->nullable();
            $table->text('remarks')->nullable();
            $table->string('digital_signature', 64)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('acted_at')->useCurrent();
            $table->timestamps();

            $table->index(['approvable_type', 'approvable_id'], 'approval_trails_morph_idx');
            $table->index(['stage', 'action']);
        });

        Schema::table('leave_applications', function (Blueprint $table) {
            $table->unsignedTinyInteger('approval_stage')->default(1)->after('status');
        });

        Schema::table('contract_renewals', function (Blueprint $table) {
            $table->unsignedTinyInteger('approval_stage')->default(1)->after('status');
            $table->text('rejection_reason')->nullable()->after('reviewed_at');
        });

        Schema::table('staff_movements', function (Blueprint $table) {
            $table->unsignedTinyInteger('approval_stage')->default(1)->after('status');
            $table->text('rejection_reason')->nullable()->after('reviewed_at');
        });

        Schema::table('payroll_periods', function (Blueprint $table) {
            $table->unsignedTinyInteger('approval_stage')->default(0)->after('status');
            $table->text('rejection_reason')->nullable()->after('reviewed_at');
        });

        // Backfill completed historical records so they show as finalized
        DB::table('leave_applications')->where('status', 'Approved')->update(['approval_stage' => 4]);
        DB::table('contract_renewals')->where('status', 'Approved')->update(['approval_stage' => 4]);
        DB::table('staff_movements')->where('status', 'Approved')->update(['approval_stage' => 4]);
        DB::table('payroll_periods')->whereIn('status', ['Approved', 'Paid'])->update(['approval_stage' => 4]);
        DB::table('payroll_periods')->where('status', 'Processing')->update(['approval_stage' => 1]);
    }

    public function down(): void
    {
        Schema::table('payroll_periods', function (Blueprint $table) {
            $table->dropColumn(['approval_stage', 'rejection_reason']);
        });
        Schema::table('staff_movements', function (Blueprint $table) {
            $table->dropColumn(['approval_stage', 'rejection_reason']);
        });
        Schema::table('contract_renewals', function (Blueprint $table) {
            $table->dropColumn(['approval_stage', 'rejection_reason']);
        });
        Schema::table('leave_applications', function (Blueprint $table) {
            $table->dropColumn('approval_stage');
        });
        Schema::dropIfExists('approval_trails');
    }
};
