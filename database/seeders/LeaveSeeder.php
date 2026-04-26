<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LeaveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = \App\Models\Company::first();
        if ($company) {
            $leaveTypes = [
                ['name'=>'Annual Leave',    'code'=>'AL',  'days_per_year'=>14, 'is_paid'=>true,  'can_carry_forward'=>true,  'max_carry_forward_days'=>7,  'color'=>'#4CAF50'],
                ['name'=>'Casual Leave',    'code'=>'CL',  'days_per_year'=>10, 'is_paid'=>true,  'can_carry_forward'=>false, 'max_carry_forward_days'=>0,  'color'=>'#378ADD'],
                ['name'=>'Sick Leave',      'code'=>'SL',  'days_per_year'=>8,  'is_paid'=>true,  'requires_document'=>true,  'color'=>'#EF9F27'],
                ['name'=>'Maternity Leave', 'code'=>'ML',  'days_per_year'=>90, 'is_paid'=>true,  'applicable_to_male'=>false,'color'=>'#D4537E'],
                ['name'=>'Paternity Leave', 'code'=>'PL',  'days_per_year'=>3,  'is_paid'=>true,  'applicable_to_female'=>false,'color'=>'#7F77DD'],
                ['name'=>'Unpaid Leave',    'code'=>'UL',  'days_per_year'=>30, 'is_paid'=>false, 'color'=>'#7a6a50'],
                ['name'=>'Eid Leave',       'code'=>'EID', 'days_per_year'=>3,  'is_paid'=>true,  'color'=>'#BA7517'],
                ['name'=>'Study Leave',     'code'=>'STD', 'days_per_year'=>5,  'is_paid'=>true,  'requires_document'=>true, 'color'=>'#1D9E75'],
            ];
            foreach ($leaveTypes as $lt) {
                \App\Models\LeaveType::firstOrCreate(
                    ['company_id' => $company->id, 'code' => $lt['code']],
                    array_merge($lt, [
                        'company_id'           => $company->id,
                        'is_active'            => true,
                        'min_days_notice'      => 1,
                        'applicable_to_male'   => $lt['applicable_to_male']   ?? true,
                        'applicable_to_female' => $lt['applicable_to_female'] ?? true,
                        'requires_document'    => $lt['requires_document']    ?? false,
                        'can_carry_forward'    => $lt['can_carry_forward']     ?? false,
                        'max_carry_forward_days'=> $lt['max_carry_forward_days'] ?? 0,
                    ])
                );
            }
        }
    }
}
