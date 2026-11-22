<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $table) {
                $table->id();
                $table->string('supplier_no', 40)->unique();
                $table->string('name', 200);
                $table->string('category', 80);
                $table->string('business_reg_no', 80)->nullable();
                $table->string('tin_number', 60)->nullable();
                $table->string('contact_person', 120)->nullable();
                $table->string('phone', 40)->nullable();
                $table->string('email', 150)->nullable();
                $table->text('address')->nullable();
                $table->string('status', 30)->default('Active'); // Active, Under Review, Suspended, Blacklisted
                $table->date('tax_clearance_expiry')->nullable();
                $table->date('business_license_expiry')->nullable();
                $table->decimal('performance_rating', 3, 2)->nullable();
                $table->text('status_reason')->nullable();
                $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index('status');
            });
        }

        if (!Schema::hasTable('procurement_requests')) {
            Schema::create('procurement_requests', function (Blueprint $table) {
                $table->id();
                $table->string('tender_no', 40)->unique();
                $table->string('title', 220);
                $table->text('description')->nullable();
                $table->foreignId('requesting_department_id')->nullable()->constrained('departments')->nullOnDelete();
                $table->string('category', 60);
                $table->string('method', 60);
                $table->decimal('estimated_cost_usd', 15, 2)->default(0);
                $table->foreignId('budget_allocation_id')->nullable()->constrained('budget_allocations')->nullOnDelete();
                $table->string('status', 40)->default('Draft');
                $table->date('advertised_date')->nullable();
                $table->date('bid_deadline')->nullable();
                $table->text('justification')->nullable();
                $table->foreignId('awarded_supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
                $table->unsignedBigInteger('awarded_bid_id')->nullable();
                $table->decimal('awarded_amount_usd', 15, 2)->nullable();
                $table->timestamp('awarded_at')->nullable();
                $table->text('award_note')->nullable();
                $table->date('contract_signed_date')->nullable();
                $table->date('delivery_due_date')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->text('cancellation_reason')->nullable();
                $table->unsignedBigInteger('ministry_document_id')->nullable();
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->foreignId('awarded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index('status');
                $table->index('bid_deadline');
            });
        }

        if (!Schema::hasTable('procurement_bids')) {
            Schema::create('procurement_bids', function (Blueprint $table) {
                $table->id();
                $table->foreignId('procurement_request_id')->constrained('procurement_requests')->cascadeOnDelete();
                $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
                $table->decimal('bid_amount_usd', 15, 2);
                $table->date('submitted_date');
                $table->unsignedSmallInteger('delivery_days')->nullable();
                $table->boolean('is_responsive')->default(true);
                $table->unsignedTinyInteger('technical_score')->nullable();
                $table->decimal('financial_score', 5, 2)->nullable();
                $table->decimal('total_score', 5, 2)->nullable();
                $table->string('status', 30)->default('Submitted'); // Submitted, Evaluated, Awarded, Rejected
                $table->text('notes')->nullable();
                $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['procurement_request_id', 'supplier_id']);
            });
        }

        if (!Schema::hasTable('supplier_evaluations')) {
            Schema::create('supplier_evaluations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
                $table->foreignId('procurement_request_id')->nullable()->constrained('procurement_requests')->nullOnDelete();
                $table->unsignedTinyInteger('quality_score');       // 1-5
                $table->unsignedTinyInteger('delivery_score');      // 1-5
                $table->unsignedTinyInteger('compliance_score');    // 1-5
                $table->decimal('overall_score', 3, 2);
                $table->text('comments')->nullable();
                $table->foreignId('evaluated_by')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_evaluations');
        Schema::dropIfExists('procurement_bids');
        Schema::dropIfExists('procurement_requests');
        Schema::dropIfExists('suppliers');
    }
};
