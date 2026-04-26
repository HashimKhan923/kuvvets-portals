<?php
namespace App\Services;

class TaxCalculator
{
    /**
     * FBR Income Tax Slabs — Pakistan Tax Year 2024-25
     * Finance Act 2024 — Salaried Individuals
     */
    public static function getSlabs(): array
    {
        return [
            ['min' => 0,        'max' => 600000,   'rate' => 0,    'fixed' => 0,       'label' => 'Up to 600,000'],
            ['min' => 600001,   'max' => 1200000,  'rate' => 0.05, 'fixed' => 0,       'label' => '600,001 – 1,200,000'],
            ['min' => 1200001,  'max' => 2200000,  'rate' => 0.15, 'fixed' => 30000,   'label' => '1,200,001 – 2,200,000'],
            ['min' => 2200001,  'max' => 3200000,  'rate' => 0.25, 'fixed' => 180000,  'label' => '2,200,001 – 3,200,000'],
            ['min' => 3200001,  'max' => 4100000,  'rate' => 0.30, 'fixed' => 430000,  'label' => '3,200,001 – 4,100,000'],
            ['min' => 4100001,  'max' => PHP_INT_MAX, 'rate' => 0.35,'fixed' => 700000,'label' => 'Above 4,100,000'],
        ];
    }

    /**
     * Calculate annual income tax on taxable annual income
     */
    public static function calculateAnnualTax(float $annualIncome): array
    {
        $slabs = self::getSlabs();
        $tax   = 0;
        $label = '';

        foreach ($slabs as $slab) {
            if ($annualIncome >= $slab['min'] && $annualIncome <= $slab['max']) {
                $excess = $annualIncome - ($slab['min'] - 1);
                $tax    = $slab['fixed'] + ($excess * $slab['rate']);
                $label  = $slab['label'] . ' @ ' . ($slab['rate'] * 100) . '%';
                break;
            }
        }

        return [
            'annual_tax'   => round($tax, 2),
            'monthly_tax'  => round($tax / 12, 2),
            'effective_rate'=> $annualIncome > 0 ? round(($tax / $annualIncome) * 100, 2) : 0,
            'slab_label'   => $label,
        ];
    }

    /**
     * Calculate EOBI contributions
     * Employee: 1% of basic salary (min wage = PKR 37,000)
     * Employer: 5% of minimum wage (PKR 1,850/month fixed)
     */
    public static function calculateEOBI(float $basicSalary): array
    {
        $minWage         = 37000;
        $employeeContrib = round($basicSalary * 0.01, 2);
        $employerContrib = round($minWage * 0.05, 2); // 1,850 fixed

        return [
            'employee' => $employeeContrib,
            'employer' => $employerContrib,
            'total'    => $employeeContrib + $employerContrib,
        ];
    }

    /**
     * Calculate PESSI/SESSI (Sindh)
     * Employee: 0.9% of gross salary
     * Employer: 5% of gross salary (capped at PKR 400 employee)
     */
    public static function calculatePESSI(float $grossSalary): array
    {
        $employeeContrib = min(round($grossSalary * 0.009, 2), 400);
        $employerContrib = round($grossSalary * 0.05, 2);

        return [
            'employee' => $employeeContrib,
            'employer' => $employerContrib,
        ];
    }

    /**
     * Calculate overtime amount
     * Per Labour Law: OT rate = (basic/26/8) * 2 per hour
     */
    public static function calculateOvertime(float $basicSalary, int $overtimeHours): float
    {
        if ($overtimeHours <= 0) return 0;
        $hourlyRate  = $basicSalary / 26 / 8;
        $otRate      = $hourlyRate * 2;
        return round($otRate * $overtimeHours, 2);
    }

    /**
     * Convert number to Pakistani words (for payslip)
     */
    public static function amountInWords(float $amount): string
    {
        $amount  = (int) round($amount);
        $ones    = ['','One','Two','Three','Four','Five','Six','Seven','Eight','Nine',
                    'Ten','Eleven','Twelve','Thirteen','Fourteen','Fifteen',
                    'Sixteen','Seventeen','Eighteen','Nineteen'];
        $tens    = ['','','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];

        $convert = function(int $n) use (&$convert, $ones, $tens): string {
            if ($n < 20)    return $ones[$n];
            if ($n < 100)   return $tens[(int)($n/10)] . ($n%10 ? ' ' . $ones[$n%10] : '');
            if ($n < 1000)  return $ones[(int)($n/100)] . ' Hundred' . ($n%100 ? ' ' . $convert($n%100) : '');
            if ($n < 100000)return $convert((int)($n/1000)) . ' Thousand' . ($n%1000 ? ' ' . $convert($n%1000) : '');
            if ($n < 10000000) return $convert((int)($n/100000)) . ' Lakh' . ($n%100000 ? ' ' . $convert($n%100000) : '');
            return $convert((int)($n/10000000)) . ' Crore' . ($n%10000000 ? ' ' . $convert($n%10000000) : '');
        };

        return $amount === 0 ? 'Zero' : $convert($amount) . ' Rupees Only';
    }
}