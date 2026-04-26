<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('employee_certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('training_enrollment_id')->nullable()
                ->constrained()->nullOnDelete();
            $table->string('certificate_name');
            $table->string('issued_by');
            $table->string('certificate_number')->nullable();
            $table->date('issue_date');
            $table->date('expiry_date')->nullable();
            $table->string('document_path')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->foreignId('verified_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['employee_id', 'expiry_date']);
        });
    }
    public function down(): void { Schema::dropIfExists('employee_certifications'); }
};