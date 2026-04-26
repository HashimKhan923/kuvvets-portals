<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('user_type', ['admin', 'employee', 'super_admin'])
                ->default('employee')
                ->after('username');
            $table->string('portal_access', 20)
                ->default('employee')
                ->after('user_type')
                ->comment('admin | employee | both');
            $table->timestamp('last_password_changed_at')->nullable()->after('password_changed_at');
            $table->integer('login_count')->default(0)->after('failed_login_attempts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['user_type','portal_access','last_password_changed_at','login_count']);
        });
    }
};
