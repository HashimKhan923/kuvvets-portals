<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('training_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('enrolled_by')->constrained('users')->cascadeOnDelete();
            $table->enum('status', [
                'enrolled', 'attended', 'absent', 'cancelled', 'waitlisted'
            ])->default('enrolled');
            $table->boolean('completed')->default(false);
            $table->integer('score')->nullable()->comment('Post-training assessment score');
            $table->boolean('passed')->nullable();
            $table->integer('feedback_rating')->nullable()->comment('1-5 employee feedback');
            $table->text('feedback_comments')->nullable();
            $table->date('completion_date')->nullable();
            $table->string('certificate_number')->nullable();
            $table->date('certificate_expiry')->nullable();
            $table->timestamps();

            $table->unique(['training_session_id', 'employee_id']);
            $table->index(['employee_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('training_enrollments'); }
};