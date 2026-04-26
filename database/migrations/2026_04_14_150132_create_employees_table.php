<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('designation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('employee_id')->unique()->comment('e.g. KVT-001');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('father_name')->nullable();
            $table->string('cnic')->unique()->nullable()->comment('13-digit Pakistani CNIC');
            $table->string('cnic_expiry')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable();
            $table->string('religion')->nullable();
            $table->string('nationality')->default('Pakistani');
            $table->string('personal_email')->nullable();
            $table->string('work_email')->nullable();
            $table->string('personal_phone')->nullable();
            $table->string('work_phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->text('current_address')->nullable();
            $table->string('current_city')->nullable();
            $table->string('permanent_address')->nullable();
            $table->string('permanent_city')->nullable();
            $table->string('province')->nullable();
            $table->date('joining_date')->nullable();
            $table->date('confirmation_date')->nullable();
            $table->date('resignation_date')->nullable();
            $table->date('termination_date')->nullable();
            $table->date('last_working_day')->nullable();
            $table->enum('employment_type', ['permanent', 'contract', 'probationary', 'part_time', 'internship', 'daily_wages'])->default('permanent');
            $table->enum('employment_status', ['active', 'resigned', 'terminated', 'retired', 'absconded', 'on_leave'])->default('active');
            $table->enum('probation_status', ['on_probation', 'confirmed', 'extended', 'terminated'])->default('on_probation');
            $table->date('probation_end_date')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_no')->nullable();
            $table->string('bank_iban')->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('eobi_number')->nullable()->comment('EOBI registration number');
            $table->string('pessi_number')->nullable()->comment('PESSI/SESSI number');
            $table->string('nssf_number')->nullable();
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->string('avatar')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_relation')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['employment_status', 'company_id']);
            $table->index(['department_id', 'company_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('employees'); }
};