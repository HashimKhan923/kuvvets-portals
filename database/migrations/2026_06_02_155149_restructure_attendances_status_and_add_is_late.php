<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add is_late flag
        Schema::table('attendances', function (Blueprint $table) {
            $table->boolean('is_late')->default(false)->after('late_minutes');
        });

        // 2. Populate is_late from existing late_minutes data
        DB::statement("UPDATE attendances SET is_late = 1 WHERE late_minutes > 0");

        // 3. Remap old statuses to new ones before changing the ENUM
        //    present, late, early_leave, late_early_leave → completed
        //    half_day stays as half_day
        //    on_leave, holiday, weekend, work_from_home stay as-is
        //    absent stays as absent
        DB::statement("UPDATE attendances SET status = 'completed'
            WHERE status IN ('present', 'late', 'early_leave', 'late_early_leave')");

        // 4. Replace the ENUM with the new value set
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM(
            'absent','short_day','half_day','three_quarter_day','completed',
            'on_leave','holiday','weekend','work_from_home'
        ) NOT NULL DEFAULT 'absent'");
    }

    public function down(): void
    {
        // Restore old ENUM (data loss on reversal is acceptable)
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM(
            'present','absent','late','half_day',
            'on_leave','holiday','weekend','work_from_home',
            'early_leave','late_early_leave'
        ) NOT NULL DEFAULT 'present'");

        // Restore old status values best-effort
        DB::statement("UPDATE attendances SET status = 'present' WHERE status = 'completed'");
        DB::statement("UPDATE attendances SET status = 'late' WHERE is_late = 1 AND status = 'present'");

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('is_late');
        });
    }
};
