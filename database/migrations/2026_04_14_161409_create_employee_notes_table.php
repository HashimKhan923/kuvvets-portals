<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('employee_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->text('body');
            $table->enum('type', ['general', 'warning', 'commendation', 'hr_note', 'performance'])->default('general');
            $table->boolean('is_private')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('employee_notes'); }
};