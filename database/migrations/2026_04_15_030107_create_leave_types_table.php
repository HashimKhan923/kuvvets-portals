<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 10);
            $table->text('description')->nullable();
            $table->integer('days_per_year')->default(0);
            $table->boolean('is_paid')->default(true);
            $table->boolean('requires_document')->default(false);
            $table->boolean('can_carry_forward')->default(false);
            $table->integer('max_carry_forward_days')->default(0);
            $table->integer('min_days_notice')->default(0)
                ->comment('Minimum days notice required before applying');
            $table->integer('max_consecutive_days')->nullable()
                ->comment('Max days that can be taken at once');
            $table->boolean('applicable_to_male')->default(true);
            $table->boolean('applicable_to_female')->default(true);
            $table->string('color', 7)->default('#BA7517');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });
    }
    public function down(): void { Schema::dropIfExists('leave_types'); }
};