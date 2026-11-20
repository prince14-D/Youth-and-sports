<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_communications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('recipient_scope', 30);
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('subject');
            $table->longText('body');
            $table->unsignedInteger('recipient_count')->default(0);
            $table->enum('status', ['Queued', 'Sent', 'Failed'])->default('Queued');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_communications');
    }
};
