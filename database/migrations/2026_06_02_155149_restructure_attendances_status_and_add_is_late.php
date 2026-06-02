<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add is_late flag (guard against partial prior run)
        if (!Schema::hasColumn('attendances', 'is_late')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->boolean('is_late')->default(false)->after('late_minutes');
            });
        }

        // 2. Populate is_late from existing late_minutes data
        DB::statement("UPDATE attendances SET is_late = 1 WHERE late_minutes > 0");

        // 3. Expand ENUM to include both old and new values so the UPDATE in step 4 is valid
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM(
            'present','absent','late','half_day',
            'on_leave','holiday','weekend','work_from_home',
            'early_leave','late_early_leave',
            'completed','short_day','three_quarter_day'
        ) NOT NULL DEFAULT 'present'");

        // 4. Remap old statuses → new ones
        DB::statement("UPDATE attendances SET status = 'completed'
            WHERE status IN ('present', 'late', 'early_leave', 'late_early_leave')");

        // 5. Now shrink ENUM to only the final value set
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
