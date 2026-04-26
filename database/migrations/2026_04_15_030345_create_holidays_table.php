<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->date('date');
            $table->date('date_to')->nullable()->comment('For multi-day holidays like Eid');
            $table->enum('type', ['national', 'religious', 'company', 'optional'])
                ->default('national');
            $table->boolean('is_recurring')->default(true)
                ->comment('Repeats every year');
            $table->text('description')->nullable();
            $table->year('year');
            $table->timestamps();

            $table->index(['company_id', 'year']);
        });
    }
    public function down(): void { Schema::dropIfExists('holidays'); }
};