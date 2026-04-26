<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('employee_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->enum('level', ['beginner', 'intermediate', 'advanced', 'expert'])
                ->default('beginner');
            $table->integer('rating')->default(1)->comment('1-5');
            $table->date('last_assessed')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'skill_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('employee_skills');
        Schema::dropIfExists('skills');
    }
};