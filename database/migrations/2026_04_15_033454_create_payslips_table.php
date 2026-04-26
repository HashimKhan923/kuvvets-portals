<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('payslip_number')->unique();
            // Earnings
            $table->decimal('basic_salary',      12, 2)->default(0);
            $table->decimal('house_rent',         12, 2)->default(0);
            $table->decimal('medical',            12, 2)->default(0);
            $table->decimal('conveyance',         12, 2)->default(0);
            $table->decimal('fuel',               12, 2)->default(0);
            $table->decimal('utility',            12, 2)->default(0);
            $table->decimal('meal',               12, 2)->default(0);
            $table->decimal('special_allowance',  12, 2)->default(0);
            $table->decimal('other_allowance',    12, 2)->default(0);
            $table->decimal('overtime_amount',    12, 2)->default(0);
            $table->decimal('bonus',              12, 2)->default(0);
            $table->decimal('arrears',            12, 2)->default(0);
            $table->decimal('gross_salary',       12, 2)->default(0);
            // Attendance adjustments
            $table->integer('working_days')->default(0);
            $table->integer('present_days')->default(0);
            $table->integer('absent_days')->default(0);
            $table->integer('leave_days')->default(0);
            $table->integer('overtime_hours')->default(0);
            $table->decimal('absent_deduction',   12, 2)->default(0);
            // Deductions
            $table->decimal('income_tax',         12, 2)->default(0);
            $table->decimal('eobi_employee',      12, 2)->default(0);
            $table->decimal('eobi_employer',      12, 2)->default(0);
            $table->decimal('pessi_employee',     12, 2)->default(0);
            $table->decimal('loan_deduction',     12, 2)->default(0);
            $table->decimal('other_deduction',    12, 2)->default(0);
            $table->decimal('total_deductions',   12, 2)->default(0);
            // Net
            $table->decimal('net_salary',         12, 2)->default(0);
            // Tax details
            $table->decimal('annual_taxable_income', 14, 2)->default(0);
            $table->decimal('annual_tax',            14, 2)->default(0);
            $table->decimal('monthly_tax',           12, 2)->default(0);
            $table->string('tax_slab')->nullable();
            // Meta
            $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
            $table->string('pdf_path')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['payroll_period_id', 'employee_id']);
            $table->index(['employee_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('payslips'); }
};