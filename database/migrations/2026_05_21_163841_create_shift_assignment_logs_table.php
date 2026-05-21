<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Add shift columns to employees if not already there ──
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'shift_id')) {
                $table->foreignId('shift_id')
                      ->nullable()
                      ->constrained('shifts')
                      ->nullOnDelete()
                      ->after('department_id');
            }
            if (!Schema::hasColumn('employees', 'shift_effective_from')) {
                $table->date('shift_effective_from')->nullable()->after('shift_id');
            }
        });

        // ── Shift assignment history log ──────────────────────────
        Schema::create('shift_assignment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('old_shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('effective_from');
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_assignment_logs');

        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Shift::class);
            $table->dropColumn(['shift_id', 'shift_effective_from']);
        });
    }
};