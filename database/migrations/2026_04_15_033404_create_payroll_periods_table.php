<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->tinyInteger('month');
            $table->year('year');
            $table->date('payment_date')->nullable();
            $table->enum('status', ['draft', 'processing', 'approved', 'paid', 'cancelled'])
                ->default('draft');
            $table->decimal('total_gross',      14, 2)->default(0);
            $table->decimal('total_deductions', 14, 2)->default(0);
            $table->decimal('total_net',        14, 2)->default(0);
            $table->decimal('total_tax',        14, 2)->default(0);
            $table->decimal('total_eobi',       14, 2)->default(0);
            $table->integer('employee_count')->default(0);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'month', 'year']);
        });
    }
    public function down(): void { Schema::dropIfExists('payroll_periods'); }
};