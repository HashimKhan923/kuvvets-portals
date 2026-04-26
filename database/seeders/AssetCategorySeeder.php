<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AssetCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = \App\Models\Company::first();
        if ($company) {
            $cats = [
                ['Forklifts',         'fa-forklift',      '#2B6CB0'],
                ['Cranes & Hoists',   'fa-person-digging','#C53030'],
                ['Warehouse Trucks',  'fa-truck',         '#B7791F'],
                ['Safety Equipment',  'fa-helmet-safety', '#2D7A4F'],
                ['IT Equipment',      'fa-computer',      '#6B46C1'],
                ['Office Furniture',  'fa-chair',         '#718096'],
            ];
            foreach ($cats as [$name,$icon,$color]) {
                \App\Models\AssetCategory::firstOrCreate(
                    ['company_id'=>$company->id,'name'=>$name],
                    ['code'=>strtoupper(substr($name,0,4)).rand(10,99),'icon'=>$icon,'color'=>$color]
                );
            }
        }
    }
}
