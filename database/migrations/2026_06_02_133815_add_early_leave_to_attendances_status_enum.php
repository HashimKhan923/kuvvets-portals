<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM(
            'present','absent','late','half_day',
            'on_leave','holiday','weekend','work_from_home',
            'early_leave','late_early_leave'
        ) NOT NULL DEFAULT 'present'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM(
            'present','absent','late','half_day',
            'on_leave','holiday','weekend','work_from_home'
        ) NOT NULL DEFAULT 'present'");
    }
};
