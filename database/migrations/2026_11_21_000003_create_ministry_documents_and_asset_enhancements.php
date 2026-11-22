<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Enhance assets with department & location ownership
        Schema::table('assets', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('category')->constrained('departments')->nullOnDelete();
            $table->string('location', 150)->nullable()->after('department_id');
            $table->date('warranty_expiry')->nullable()->after('purchase_date');
        });

        // 2. Departmental Asset Requisitions (routed to DMA for approval)
        Schema::create('asset_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_no', 40)->unique();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->string('item_name', 160);
            $table->string('category', 60);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('estimated_cost_usd', 15, 2)->nullable();
            $table->string('priority', 20)->default('Normal'); // Normal, High, Urgent
            $table->text('justification');
            $table->string('status', 30)->default('Pending DMA'); // Pending DMA, Approved, Fulfilled, Rejected
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->unsignedBigInteger('ministry_document_id')->nullable();
            $table->timestamps();

            $table->index(['department_id', 'status']);
        });

        // 3. Paperless Ministry Document & Request Workflow
        Schema::create('ministry_documents', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_number', 40)->unique();
            $table->string('subject', 200);
            $table->string('document_type', 80);
            $table->string('priority', 20)->default('Normal'); // Normal, High, Urgent
            $table->string('confidentiality', 40)->default('Restricted'); // Restricted, Confidential, Internal
            $table->foreignId('origin_department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('current_department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('destination_department_id')->constrained('departments')->cascadeOnDelete();
            $table->json('allowed_department_ids')->nullable();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('current_handler_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('amount_lrd', 15, 2)->nullable();
            $table->decimal('amount_usd', 15, 2)->nullable();
            $table->text('summary');
            $table->string('attachment_path')->nullable();
            $table->string('status', 30)->default('In Transit'); // In Transit, Received, Under Review, Approved, Returned, Rejected, Completed
            $table->timestamp('received_at_current_office_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['current_department_id', 'status']);
            $table->index(['origin_department_id', 'destination_department_id']);
        });

        // 4. Immutable Office-to-Office Routing & Minute Trail
        Schema::create('document_routing_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ministry_document_id')->constrained('ministry_documents')->cascadeOnDelete();
            $table->foreignId('from_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('to_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('action', 30); // dispatched, received, forwarded, approved, returned, rejected, completed
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_name', 120);
            $table->string('actor_role', 80)->nullable();
            $table->text('minutes_note')->nullable();
            $table->string('digital_signature', 64)->nullable();
            $table->timestamp('acted_at')->useCurrent();
            $table->timestamps();

            $table->index(['ministry_document_id', 'acted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_routing_logs');
        Schema::dropIfExists('ministry_documents');
        Schema::dropIfExists('asset_requests');
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn(['department_id', 'location', 'warranty_expiry']);
        });
    }
};
