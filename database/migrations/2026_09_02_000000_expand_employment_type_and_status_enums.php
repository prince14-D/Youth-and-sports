<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->enum('employment_type', ['Permanent', 'Contract', 'Casual', 'Intern', 'Probationary', 'Temporary'])
                ->default('Permanent')
                ->change();

            $table->enum('employment_status', ['Active', 'Inactive', 'On Leave', 'Suspended', 'Retired', 'Resigned', 'Terminated', 'Deceased', 'Probationary'])
                ->default('Active')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->enum('employment_type', ['Permanent', 'Contract', 'Casual', 'Intern'])
                ->default('Permanent')
                ->change();

            $table->enum('employment_status', ['Active', 'Inactive', 'Suspended', 'Retired', 'Resigned', 'Terminated'])
                ->default('Active')
                ->change();
        });
    }
};
