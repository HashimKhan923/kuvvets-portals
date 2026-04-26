<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique()->comment('e.g. KVT-WH-LHR-01');
            $table->string('name');
            $table->enum('type', ['warehouse','office','site','branch','other'])->default('warehouse');
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->unsignedInteger('radius_meters')->default(100)
                ->comment('Geofence tolerance radius');
            $table->string('qr_token', 64)->unique()
                ->comment('Secret token embedded in QR code');
            $table->timestamp('qr_rotated_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id','is_active']);
        });
    }
    public function down(): void { Schema::dropIfExists('locations'); }
};