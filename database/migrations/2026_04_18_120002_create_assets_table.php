<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('asset_code')->unique();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('registration_number')->nullable()
                ->comment('For vehicles/heavy equipment');
            $table->enum('type', [
                'heavy_equipment', 'vehicle', 'forklift', 'crane',
                'warehouse_equipment', 'it_equipment', 'furniture',
                'tools', 'safety_equipment', 'other'
            ])->default('other');
            $table->enum('condition', [
                'new', 'good', 'fair', 'poor', 'under_repair', 'disposed'
            ])->default('good');
            $table->enum('status', [
                'available', 'assigned', 'under_maintenance',
                'out_of_service', 'disposed', 'rented_out'
            ])->default('available');
            $table->enum('ownership', ['owned', 'leased', 'rented'])
                ->default('owned');
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost', 12, 2)->nullable();
            $table->decimal('current_value', 12, 2)->nullable();
            $table->decimal('depreciation_rate', 5, 2)->default(0)
                ->comment('Annual % depreciation');
            $table->string('vendor')->nullable();
            $table->string('vendor_contact')->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->date('insurance_expiry')->nullable();
            $table->string('insurance_policy')->nullable();
            $table->date('license_expiry')->nullable()
                ->comment('For vehicles/operators');
            $table->integer('odometer_reading')->nullable()
                ->comment('For vehicles — km');
            $table->integer('operating_hours')->nullable()
                ->comment('For heavy equipment');
            $table->text('notes')->nullable();
            $table->string('image')->nullable();
            $table->string('location')->nullable()
                ->comment('Physical location / warehouse bay');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'type']);
        });
    }
    public function down(): void { Schema::dropIfExists('assets'); }
};