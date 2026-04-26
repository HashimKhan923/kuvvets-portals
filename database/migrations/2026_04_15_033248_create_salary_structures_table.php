<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('salary_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('structure_name')->default('Standard');
            $table->decimal('basic_salary',     12, 2)->default(0);
            // Allowances
            $table->decimal('house_rent',        12, 2)->default(0)->comment('HRA');
            $table->decimal('medical',           12, 2)->default(0);
            $table->decimal('conveyance',        12, 2)->default(0);
            $table->decimal('fuel',              12, 2)->default(0);
            $table->decimal('utility',           12, 2)->default(0);
            $table->decimal('meal',              12, 2)->default(0);
            $table->decimal('special_allowance', 12, 2)->default(0);
            $table->decimal('other_allowance',   12, 2)->default(0);
            // Deductions
            $table->decimal('eobi_employee',     12, 2)->default(0)->comment('Employee EOBI: 1% of basic');
            $table->decimal('eobi_employer',     12, 2)->default(0)->comment('Employer EOBI: 5% of min wage');
            $table->decimal('pessi_employee',    12, 2)->default(0);
            $table->decimal('pessi_employer',    12, 2)->default(0);
            $table->decimal('loan_deduction',    12, 2)->default(0);
            $table->decimal('other_deduction',   12, 2)->default(0);
            // Tax
            $table->boolean('tax_exempt')->default(false);
            $table->decimal('tax_rebate',        12, 2)->default(0);
            // Effective date
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->boolean('is_current')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'is_current']);
        });
    }
    public function down(): void { Schema::dropIfExists('salary_structures'); }
};