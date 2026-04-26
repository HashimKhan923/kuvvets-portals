<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('group')->default('general')
                ->comment('general, hr, payroll, leave, attendance, notification');
            $table->string('key')->index();
            $table->text('value')->nullable();
            $table->string('type')->default('string')
                ->comment('string, boolean, integer, json, date');
            $table->string('label')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();

            $table->unique(['company_id', 'key']);
        });
    }
    public function down(): void { Schema::dropIfExists('settings'); }
};