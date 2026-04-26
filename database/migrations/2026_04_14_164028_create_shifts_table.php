<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('grace_minutes')->default(10)->comment('Late arrival grace period');
            $table->integer('break_minutes')->default(60)->comment('Allowed break time');
            $table->integer('working_hours')->default(8);
            $table->boolean('is_night_shift')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('working_days')->nullable()->comment('["Mon","Tue","Wed","Thu","Fri"]');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('shifts'); }
};