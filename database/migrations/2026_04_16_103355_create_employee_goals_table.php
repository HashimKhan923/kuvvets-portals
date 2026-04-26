<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('employee_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('performance_cycle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kpi_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('category', [
                'productivity', 'quality', 'attendance',
                'customer', 'financial', 'learning', 'leadership', 'other'
            ])->default('productivity');
            $table->decimal('target_value', 10, 2)->nullable();
            $table->decimal('achieved_value', 10, 2)->nullable();
            $table->string('unit')->nullable();
            $table->integer('weight')->default(10);
            $table->date('due_date')->nullable();
            $table->enum('status', ['not_started', 'in_progress', 'completed', 'cancelled'])
                ->default('not_started');
            $table->integer('progress')->default(0)->comment('0-100%');
            $table->text('employee_comments')->nullable();
            $table->text('manager_comments')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'performance_cycle_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('employee_goals'); }
};