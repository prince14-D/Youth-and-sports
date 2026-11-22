<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_allocations', function (Blueprint $table) {
            $table->id();
            $table->string('fiscal_year', 10)->default('2026');
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->string('budget_code', 50);
            $table->string('category', 80);
            $table->decimal('allocated_usd', 15, 2)->default(0);
            $table->decimal('allocated_lrd', 15, 2)->default(0);
            $table->string('funding_source', 100)->default('GoL National Budget');
            $table->string('notes', 255)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['fiscal_year', 'department_id']);
        });

        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no', 40)->unique();
            $table->string('transaction_type', 50); // Expenditure / Payment, Budget Release / Receipt, Payroll Disbursement, Vendor Payment, Imprest / Petty Cash
            $table->string('category', 80);
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('budget_allocation_id')->nullable()->constrained('budget_allocations')->nullOnDelete();
            $table->string('payee_name', 160);
            $table->text('description');
            $table->date('transaction_date');
            $table->decimal('amount_lrd', 15, 2)->default(0);
            $table->decimal('amount_usd', 15, 2)->default(0);
            $table->string('payment_method', 60)->default('Bank Transfer / CBL');
            $table->string('bank_reference', 100)->nullable();
            $table->string('status', 30)->default('Pending DMA'); // Pending DMA, Approved, Paid, Rejected
            $table->string('attachment_path')->nullable();
            $table->unsignedBigInteger('ministry_document_id')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['transaction_date', 'status']);
            $table->index(['department_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
        Schema::dropIfExists('budget_allocations');
    }
};
