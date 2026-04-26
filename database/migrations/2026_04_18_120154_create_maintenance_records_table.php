<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('maintenance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('reference_number')->unique();
            $table->enum('type', [
                'routine', 'preventive', 'corrective',
                'emergency', 'inspection', 'calibration'
            ])->default('routine');
            $table->enum('status', [
                'scheduled', 'in_progress', 'completed', 'cancelled'
            ])->default('scheduled');
            $table->date('scheduled_date');
            $table->date('completed_date')->nullable();
            $table->string('performed_by')->nullable();
            $table->string('vendor')->nullable();
            $table->decimal('cost', 10, 2)->default(0);
            $table->integer('downtime_hours')->default(0);
            $table->text('description');
            $table->text('work_done')->nullable();
            $table->text('parts_replaced')->nullable();
            $table->date('next_service_date')->nullable();
            $table->integer('odometer_reading')->nullable();
            $table->integer('operating_hours')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['asset_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('maintenance_records'); }
};