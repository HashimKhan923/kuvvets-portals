<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // In DatabaseSeeder::run() after SuperAdminSeeder
$company = \App\Models\Company::first();
if ($company) {
    $shifts = [
        ['name'=>'Morning Shift', 'start_time'=>'08:00', 'end_time'=>'17:00', 'working_hours'=>8, 'is_night_shift'=>false],
        ['name'=>'Evening Shift', 'start_time'=>'14:00', 'end_time'=>'23:00', 'working_hours'=>8, 'is_night_shift'=>false],
        ['name'=>'Night Shift',   'start_time'=>'22:00', 'end_time'=>'07:00', 'working_hours'=>8, 'is_night_shift'=>true],
        ['name'=>'General Shift', 'start_time'=>'09:00', 'end_time'=>'18:00', 'working_hours'=>8, 'is_night_shift'=>false],
    ];
    foreach ($shifts as $s) {
        \App\Models\Shift::firstOrCreate(['company_id'=>$company->id,'name'=>$s['name']], array_merge($s, [
            'company_id'    => $company->id,
            'grace_minutes' => 10,
            'break_minutes' => 60,
            'working_days'  => ['Mon','Tue','Wed','Thu','Fri'],
        ]));
    }
}
    }
}
