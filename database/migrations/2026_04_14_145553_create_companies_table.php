<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('ntn')->nullable()->comment('National Tax Number (Pakistan)');
            $table->string('strn')->nullable()->comment('Sales Tax Registration Number');
            $table->string('registration_no')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('country')->default('Pakistan');
            $table->string('logo')->nullable();
            $table->string('currency')->default('PKR');
            $table->string('timezone')->default('Asia/Karachi');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('companies'); }
};