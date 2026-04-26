<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('document_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shared_with')->constrained('users')->cascadeOnDelete();
            $table->foreignId('shared_by')->constrained('users')->cascadeOnDelete();
            $table->enum('permission', ['view', 'download'])->default('view');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['document_id', 'shared_with']);
        });
    }
    public function down(): void { Schema::dropIfExists('document_shares'); }
};