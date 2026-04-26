<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('feedback_360', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appraisal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->enum('relationship', ['peer', 'subordinate', 'manager', 'cross_department']);
            $table->integer('score')->nullable()->comment('1-5');
            $table->text('feedback')->nullable();
            $table->boolean('is_anonymous')->default(true);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('feedback_360'); }
};