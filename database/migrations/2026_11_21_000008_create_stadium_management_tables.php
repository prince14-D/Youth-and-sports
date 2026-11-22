<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('stadium_facilities')) {
            Schema::create('stadium_facilities', function (Blueprint $table) {
                $table->id();
                $table->string('facility_code', 40)->unique();
                $table->string('name', 200);
                $table->string('facility_type', 80);
                $table->string('location', 180);
                $table->string('county', 80)->default('Montserrado');
                $table->unsignedInteger('capacity')->default(0);
                $table->string('surface_type', 80)->default('Natural Grass');
                $table->string('floodlights_status', 40)->default('Operational');
                $table->string('operational_status', 40)->default('Operational'); // Operational, Maintenance Mode, Restricted Access, Closed / Unsafe
                $table->date('safety_certificate_expiry')->nullable();
                $table->string('manager_name', 120)->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index('operational_status');
            });
        }

        if (!Schema::hasTable('stadium_events')) {
            Schema::create('stadium_events', function (Blueprint $table) {
                $table->id();
                $table->string('event_code', 40)->unique();
                $table->foreignId('stadium_facility_id')->constrained('stadium_facilities')->cascadeOnDelete();
                $table->string('title', 220);
                $table->string('event_type', 80);
                $table->string('organizer', 180);
                $table->string('organizer_contact', 100)->nullable();
                $table->date('event_date');
                $table->string('start_time', 10)->nullable();
                $table->string('end_time', 10)->nullable();
                $table->unsignedInteger('expected_attendance')->default(0);
                $table->decimal('revenue_fee_usd', 15, 2)->default(0);
                $table->string('status', 40)->default('Scheduled'); // Scheduled, Preparation In Progress, Safety Cleared, Completed, Postponed, Cancelled
                $table->boolean('checklist_pitch_ready')->default(false);
                $table->boolean('checklist_lighting_power')->default(false);
                $table->boolean('checklist_medical_ems')->default(false);
                $table->boolean('checklist_security_crowd')->default(false);
                $table->boolean('checklist_sanitation_water')->default(false);
                $table->boolean('checklist_sound_scoreboard')->default(false);
                $table->foreignId('safety_cleared_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('safety_cleared_at')->nullable();
                $table->unsignedBigInteger('ministry_document_id')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['event_date', 'status']);
            });
        }

        if (!Schema::hasTable('stadium_maintenance_logs')) {
            Schema::create('stadium_maintenance_logs', function (Blueprint $table) {
                $table->id();
                $table->string('work_order_no', 40)->unique();
                $table->foreignId('stadium_facility_id')->constrained('stadium_facilities')->cascadeOnDelete();
                $table->string('zone_area', 100);
                $table->string('title', 200);
                $table->string('maintenance_type', 60)->default('Corrective / Repair');
                $table->string('priority', 30)->default('Medium'); // Low, Medium, High, Critical
                $table->string('status', 40)->default('Open'); // Open, In Progress, Awaiting Parts / Funding, Completed, Cancelled
                $table->date('scheduled_date')->nullable();
                $table->date('completed_date')->nullable();
                $table->decimal('estimated_cost_usd', 15, 2)->default(0);
                $table->decimal('actual_cost_usd', 15, 2)->nullable();
                $table->string('assigned_contractor', 160)->nullable();
                $table->text('description');
                $table->text('completion_notes')->nullable();
                $table->unsignedBigInteger('ministry_document_id')->nullable();
                $table->foreignId('reported_by')->constrained('users')->cascadeOnDelete();
                $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['status', 'priority']);
            });
        }

        if (!Schema::hasTable('stadium_safety_inspections')) {
            Schema::create('stadium_safety_inspections', function (Blueprint $table) {
                $table->id();
                $table->string('inspection_no', 40)->unique();
                $table->foreignId('stadium_facility_id')->constrained('stadium_facilities')->cascadeOnDelete();
                $table->foreignId('stadium_event_id')->nullable()->constrained('stadium_events')->nullOnDelete();
                $table->string('inspection_type', 80);
                $table->date('inspection_date');
                $table->string('overall_rating', 40)->default('Passed'); // Passed, Passed with Conditions, Failed / Hold Event
                $table->unsignedTinyInteger('safety_score')->default(85);
                $table->boolean('fire_exits_clear')->default(true);
                $table->boolean('structural_ok')->default(true);
                $table->boolean('medical_station_ready')->default(true);
                $table->boolean('security_briefed')->default(true);
                $table->text('hazards_identified')->nullable();
                $table->text('corrective_actions')->nullable();
                $table->string('inspector_name', 120);
                $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();
                $table->timestamps();

                $table->index(['stadium_facility_id', 'inspection_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stadium_safety_inspections');
        Schema::dropIfExists('stadium_maintenance_logs');
        Schema::dropIfExists('stadium_events');
        Schema::dropIfExists('stadium_facilities');
    }
};
