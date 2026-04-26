<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->timestamp('check_in')->nullable();
            $table->timestamp('check_out')->nullable();
            $table->integer('working_minutes')->default(0);
            $table->integer('overtime_minutes')->default(0);
            $table->integer('late_minutes')->default(0);
            $table->integer('early_leave_minutes')->default(0);
            $table->integer('break_minutes')->default(0);
            $table->enum('status', [
                'present', 'absent', 'late', 'half_day',
                'on_leave', 'holiday', 'weekend', 'work_from_home'
            ])->default('present');
            $table->enum('source', ['manual', 'biometric', 'web', 'mobile'])->default('web');
            $table->string('check_in_ip')->nullable();
            $table->string('check_out_ip')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->boolean('override')->default(false)->comment('Manually overridden by HR');
            $table->timestamps();

            $table->unique(['employee_id', 'date']);
            $table->index(['company_id', 'date']);
            $table->index(['employee_id', 'date']);
        });
    }
    public function down(): void { Schema::dropIfExists('attendances'); }
};