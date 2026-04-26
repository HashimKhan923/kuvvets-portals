<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_posting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('scheduled_by')->constrained('users')->cascadeOnDelete();
            $table->integer('round')->default(1)->comment('Interview round number');
            $table->enum('type', ['phone','video','in_person','technical','hr','panel'])->default('in_person');
            $table->dateTime('scheduled_at');
            $table->integer('duration_minutes')->default(60);
            $table->string('location')->nullable()->comment('Room / Zoom link / Address');
            $table->json('interviewers')->nullable()->comment('Array of user IDs');
            $table->enum('status', ['scheduled','completed','cancelled','no_show','rescheduled'])->default('scheduled');
            $table->integer('score')->nullable()->comment('Score out of 10');
            $table->text('feedback')->nullable();
            $table->enum('recommendation', ['strong_hire','hire','maybe','no_hire'])->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('interviews'); }
};