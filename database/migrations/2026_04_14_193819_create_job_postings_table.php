<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('designation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('reference_no')->unique();
            $table->text('description')->nullable();
            $table->text('requirements')->nullable();
            $table->text('responsibilities')->nullable();
            $table->enum('type', ['permanent','contract','part_time','internship','daily_wages'])->default('permanent');
            $table->enum('experience_level', ['entry','junior','mid','senior','lead','manager'])->default('junior');
            $table->integer('vacancies')->default(1);
            $table->decimal('salary_min', 12, 2)->nullable();
            $table->decimal('salary_max', 12, 2)->nullable();
            $table->boolean('salary_disclosed')->default(false);
            $table->string('location')->nullable();
            $table->date('posted_date')->nullable();
            $table->date('deadline')->nullable();
            $table->enum('status', ['draft','open','on_hold','closed','cancelled'])->default('draft');
            $table->integer('total_applications')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id','status']);
        });
    }
    public function down(): void { Schema::dropIfExists('job_postings'); }
};