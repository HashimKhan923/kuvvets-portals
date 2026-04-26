<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('applicants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_posting_id')->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('cnic')->nullable();
            $table->integer('total_experience_years')->default(0);
            $table->string('current_employer')->nullable();
            $table->string('current_designation')->nullable();
            $table->decimal('current_salary', 12, 2)->nullable();
            $table->decimal('expected_salary', 12, 2)->nullable();
            $table->integer('notice_period_days')->default(0);
            $table->string('cv_path')->nullable();
            $table->string('cover_letter_path')->nullable();
            $table->string('city')->nullable();
            $table->string('source')->nullable()
                ->comment('LinkedIn, Rozee.pk, Referral, Walk-in, etc.');
            $table->string('referred_by')->nullable();
            $table->enum('stage', [
                'applied',
                'screening',
                'shortlisted',
                'interview_scheduled',
                'interviewed',
                'assessment',
                'offer_sent',
                'offer_accepted',
                'offer_declined',
                'hired',
                'rejected',
                'withdrawn',
            ])->default('applied');
            $table->integer('rating')->nullable()->comment('1–5 HR rating');
            $table->text('notes')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['job_posting_id','stage']);
            $table->index(['company_id','stage']);
        });
    }
    public function down(): void { Schema::dropIfExists('applicants'); }
};