<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('appraisals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('performance_cycle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appraiser_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('appraisal_number')->unique();
            $table->enum('type', ['self', 'manager', 'peer', 'subordinate', 'hr'])->default('manager');
            $table->enum('status', ['pending', 'self_review', 'manager_review', 'hr_review', 'completed'])
                ->default('pending');
            // Scores (1-5 scale)
            $table->decimal('job_knowledge_score',     3, 1)->nullable();
            $table->decimal('work_quality_score',      3, 1)->nullable();
            $table->decimal('productivity_score',      3, 1)->nullable();
            $table->decimal('communication_score',     3, 1)->nullable();
            $table->decimal('teamwork_score',          3, 1)->nullable();
            $table->decimal('initiative_score',        3, 1)->nullable();
            $table->decimal('attendance_score',        3, 1)->nullable();
            $table->decimal('leadership_score',        3, 1)->nullable();
            $table->decimal('goal_achievement_score',  3, 1)->nullable();
            $table->decimal('overall_score',           4, 2)->nullable();
            // Text feedback
            $table->text('strengths')->nullable();
            $table->text('improvements')->nullable();
            $table->text('achievements')->nullable();
            $table->text('training_needs')->nullable();
            $table->text('manager_comments')->nullable();
            $table->text('hr_comments')->nullable();
            $table->text('employee_response')->nullable();
            // Rating
            $table->enum('overall_rating', [
                'outstanding', 'exceeds_expectations', 'meets_expectations',
                'needs_improvement', 'unsatisfactory'
            ])->nullable();
            $table->decimal('increment_recommended', 5, 2)->nullable()
                ->comment('% increment recommended');
            $table->boolean('promotion_recommended')->default(false);
            $table->text('promotion_notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'performance_cycle_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('appraisals'); }
};