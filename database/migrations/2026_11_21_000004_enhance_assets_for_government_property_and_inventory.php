<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->string('gsa_code', 60)->nullable()->after('asset_tag');
            $table->string('plate_number', 40)->nullable()->after('serial_number');
            $table->string('chassis_number', 80)->nullable()->after('plate_number');
            $table->unsignedInteger('quantity')->default(1)->after('condition');
            $table->string('unit', 30)->default('Unit')->after('quantity');
            $table->string('supplier', 150)->nullable()->after('purchase_value');
            $table->string('funding_source', 100)->nullable()->after('supplier');
            $table->date('last_maintenance_date')->nullable()->after('warranty_expiry');
            $table->date('next_maintenance_date')->nullable()->after('last_maintenance_date');
        });

        Schema::create('asset_maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->string('maintenance_type', 60); // Routine Service, Repair, Inspection / Audit, Fuel / Fleet Log, Upgrade
            $table->date('service_date');
            $table->decimal('cost_usd', 15, 2)->default(0);
            $table->string('vendor', 150)->nullable();
            $table->text('notes')->nullable();
            $table->date('next_service_date')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['asset_id', 'service_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_maintenance_logs');
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn([
                'gsa_code', 'plate_number', 'chassis_number', 'quantity', 'unit',
                'supplier', 'funding_source', 'last_maintenance_date', 'next_maintenance_date',
            ]);
        });
    }
};
