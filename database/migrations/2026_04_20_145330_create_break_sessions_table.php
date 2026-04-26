<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('break_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('duration_minutes')->default(0);
            $table->string('reason', 50)->nullable()->comment('lunch, prayer, tea, personal');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['attendance_id','ended_at']);
            $table->index(['employee_id','started_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('break_sessions'); }
};