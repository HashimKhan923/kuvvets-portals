<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('performance_cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', ['annual', 'semi_annual', 'quarterly', 'monthly', 'probation'])
                ->default('annual');
            $table->date('start_date');
            $table->date('end_date');
            $table->date('review_start_date')->nullable();
            $table->date('review_end_date')->nullable();
            $table->enum('status', ['draft', 'active', 'review', 'completed', 'cancelled'])
                ->default('draft');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('performance_cycles'); }
};