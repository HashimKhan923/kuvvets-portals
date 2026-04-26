<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('asset_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_by')->constrained('users')->cascadeOnDelete();
            $table->date('assigned_date');
            $table->date('expected_return_date')->nullable();
            $table->date('actual_return_date')->nullable();
            $table->enum('status', ['active', 'returned', 'overdue'])->default('active');
            $table->string('purpose')->nullable();
            $table->text('condition_on_issue')->nullable();
            $table->text('condition_on_return')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('returned_to')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['asset_id', 'status']);
            $table->index(['employee_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('asset_assignments'); }
};