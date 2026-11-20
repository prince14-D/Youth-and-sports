<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Employee Education
        Schema::create('employee_education', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('institution');
            $table->string('qualification');
            $table->string('field_of_study')->nullable();
            $table->year('year_obtained')->nullable();
            $table->string('certificate_path')->nullable();
            $table->timestamps();
        });

        // Employee Certifications
        Schema::create('employee_certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('name');
            $table->string('issuing_body')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('certificate_path')->nullable();
            $table->timestamps();
        });

        // Next of Kin
        Schema::create('employee_next_of_kin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('name');
            $table->string('relationship');
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        // Emergency Contacts
        Schema::create('employee_emergency_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('name');
            $table->string('relationship');
            $table->string('phone', 20);
            $table->string('email')->nullable();
            $table->timestamps();
        });

        // Employee Documents
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('document_type'); // appointment_letter, contract, id_card, certificate, etc.
            $table->string('title');
            $table->string('file_path');
            $table->string('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->date('expiry_date')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
        Schema::dropIfExists('employee_emergency_contacts');
        Schema::dropIfExists('employee_next_of_kin');
        Schema::dropIfExists('employee_certifications');
        Schema::dropIfExists('employee_education');
    }
};
