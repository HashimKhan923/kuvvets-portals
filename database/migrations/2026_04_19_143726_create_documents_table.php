<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete()
                ->comment('If linked to an employee');
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('document_number')->nullable();
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type')->nullable()->comment('pdf, docx, xlsx, etc.');
            $table->bigInteger('file_size')->default(0)->comment('Bytes');
            $table->enum('type', [
                'policy', 'procedure', 'contract', 'certificate',
                'compliance', 'hr_document', 'legal', 'financial',
                'training', 'other'
            ])->default('other');
            $table->enum('access_level', ['public', 'hr_only', 'management', 'private'])
                ->default('hr_only');
            $table->enum('status', ['active', 'expired', 'archived', 'draft'])
                ->default('active');
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->integer('version')->default(1);
            $table->foreignId('parent_document_id')->nullable()
                ->constrained('documents')->nullOnDelete()
                ->comment('For version tracking');
            $table->boolean('is_latest_version')->default(true);
            $table->integer('download_count')->default(0);
            $table->integer('view_count')->default(0);
            $table->text('tags')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'type']);
            $table->index(['company_id', 'status']);
            $table->index(['employee_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('documents'); }
};