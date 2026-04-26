<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained()->cascadeOnDelete();
            $table->string('request_number')->unique();
            $table->date('from_date');
            $table->date('to_date');
            $table->decimal('total_days', 5, 1);
            $table->enum('day_type', ['full_day', 'half_day_morning', 'half_day_afternoon'])
                ->default('full_day');
            $table->text('reason');
            $table->string('document_path')->nullable();
            $table->enum('status', [
                'pending', 'approved', 'rejected',
                'cancelled', 'withdrawn'
            ])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('hr_notes')->nullable();
            $table->boolean('is_emergency')->default(false);
            $table->string('contact_during_leave')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index(['employee_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('leave_requests'); }
};