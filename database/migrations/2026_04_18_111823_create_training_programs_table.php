<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('training_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->text('objectives')->nullable();
            $table->enum('category', [
                'safety', 'technical', 'soft_skills', 'compliance',
                'leadership', 'equipment', 'onboarding', 'other'
            ])->default('other');
            $table->enum('delivery_method', [
                'classroom', 'online', 'on_job', 'workshop',
                'seminar', 'mentoring', 'blended'
            ])->default('classroom');
            $table->integer('duration_hours')->default(1);
            $table->decimal('cost_per_person', 10, 2)->default(0);
            $table->string('provider')->nullable()
                ->comment('Internal or external training provider');
            $table->string('certificate_name')->nullable();
            $table->integer('certificate_validity_months')->nullable()
                ->comment('0 = no expiry');
            $table->boolean('is_mandatory')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('training_programs'); }
};