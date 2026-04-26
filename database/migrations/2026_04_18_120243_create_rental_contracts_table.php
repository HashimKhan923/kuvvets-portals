<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rental_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('contract_number')->unique();
            $table->enum('rental_type', ['inbound', 'outbound'])
                ->comment('inbound = we rent from others, outbound = we rent to clients');
            $table->string('party_name')
                ->comment('Vendor name (inbound) or Client name (outbound)');
            $table->string('party_contact')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('rate_per_day', 10, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('deposit_amount', 10, 2)->default(0);
            $table->enum('status', [
                'active', 'completed', 'cancelled', 'overdue'
            ])->default('active');
            $table->text('terms')->nullable();
            $table->string('document_path')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('rental_contracts'); }
};