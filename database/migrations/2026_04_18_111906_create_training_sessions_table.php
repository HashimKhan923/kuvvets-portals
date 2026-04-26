<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('training_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('session_code')->unique();
            $table->string('title');
            $table->date('start_date');
            $table->date('end_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('venue')->nullable();
            $table->string('trainer_name')->nullable();
            $table->string('trainer_email')->nullable();
            $table->integer('max_participants')->default(20);
            $table->integer('enrolled_count')->default(0);
            $table->enum('status', [
                'scheduled', 'ongoing', 'completed', 'cancelled', 'postponed'
            ])->default('scheduled');
            $table->decimal('actual_cost', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->text('feedback_summary')->nullable();
            $table->decimal('average_rating', 3, 1)->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['training_program_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('training_sessions'); }
};