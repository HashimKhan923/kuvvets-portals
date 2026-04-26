<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('kpis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('category', [
                'productivity', 'quality', 'attendance',
                'customer', 'financial', 'learning', 'leadership', 'other'
            ])->default('productivity');
            $table->enum('measurement_type', ['number', 'percentage', 'boolean', 'rating'])
                ->default('number');
            $table->string('unit')->nullable()->comment('e.g. shipments, %, PKR');
            $table->decimal('target_value', 10, 2)->nullable();
            $table->decimal('min_value', 10, 2)->nullable();
            $table->decimal('max_value', 10, 2)->nullable();
            $table->integer('weight')->default(10)->comment('Weight % in overall score');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('kpis'); }
};