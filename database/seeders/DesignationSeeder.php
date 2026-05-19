<?php

namespace Database\Seeders;

use App\Models\Designation;
use Illuminate\Database\Seeder;

class DesignationSeeder extends Seeder
{
    public function run(): void
    {
        $companyId = 1; // Change this to your company ID

        $designations = [

            // ── EXECUTIVE / C-SUITE ──────────────────────────────────
            ['title' => 'Chief Executive Officer',       'code' => 'CEO',   'department' => 'Executive'],
            ['title' => 'Chief Operating Officer',       'code' => 'COO',   'department' => 'Executive'],
            ['title' => 'Chief Financial Officer',       'code' => 'CFO',   'department' => 'Executive'],
            ['title' => 'Chief Technology Officer',      'code' => 'CTO',   'department' => 'Executive'],
            ['title' => 'Chief Human Resources Officer', 'code' => 'CHRO',  'department' => 'Executive'],
            ['title' => 'Chief Marketing Officer',       'code' => 'CMO',   'department' => 'Executive'],
            ['title' => 'Chief Information Officer',     'code' => 'CIO',   'department' => 'Executive'],
            ['title' => 'Managing Director',             'code' => 'MD',    'department' => 'Executive'],
            ['title' => 'Executive Director',            'code' => 'ED',    'department' => 'Executive'],
            ['title' => 'General Manager',               'code' => 'GM',    'department' => 'Executive'],
            ['title' => 'Deputy General Manager',        'code' => 'DGM',   'department' => 'Executive'],
            ['title' => 'Assistant General Manager',     'code' => 'AGM',   'department' => 'Executive'],

            // ── HUMAN RESOURCES ──────────────────────────────────────
            ['title' => 'HR Director',                   'code' => 'HRD',   'department' => 'Human Resources'],
            ['title' => 'HR Manager',                    'code' => 'HRM',   'department' => 'Human Resources'],
            ['title' => 'Assistant HR Manager',          'code' => 'AHRM',  'department' => 'Human Resources'],
            ['title' => 'HR Business Partner',           'code' => 'HRBP',  'department' => 'Human Resources'],
            ['title' => 'HR Executive',                  'code' => 'HRE',   'department' => 'Human Resources'],
            ['title' => 'HR Officer',                    'code' => 'HRO',   'department' => 'Human Resources'],
            ['title' => 'HR Assistant',                  'code' => 'HRA',   'department' => 'Human Resources'],
            ['title' => 'Recruitment Manager',           'code' => 'RM',    'department' => 'Human Resources'],
            ['title' => 'Recruitment Officer',           'code' => 'RO',    'department' => 'Human Resources'],
            ['title' => 'Talent Acquisition Specialist', 'code' => 'TAS',   'department' => 'Human Resources'],
            ['title' => 'Training & Development Manager','code' => 'TDM',   'department' => 'Human Resources'],
            ['title' => 'Training Officer',              'code' => 'TO',    'department' => 'Human Resources'],
            ['title' => 'Payroll Manager',               'code' => 'PM',    'department' => 'Human Resources'],
            ['title' => 'Payroll Officer',               'code' => 'PO',    'department' => 'Human Resources'],
            ['title' => 'Compensation & Benefits Manager','code' => 'CBM',  'department' => 'Human Resources'],

            // ── FINANCE & ACCOUNTS ───────────────────────────────────
            ['title' => 'Finance Director',              'code' => 'FD',    'department' => 'Finance'],
            ['title' => 'Finance Manager',               'code' => 'FM',    'department' => 'Finance'],
            ['title' => 'Assistant Finance Manager',     'code' => 'AFM',   'department' => 'Finance'],
            ['title' => 'Senior Accountant',             'code' => 'SA',    'department' => 'Finance'],
            ['title' => 'Accountant',                    'code' => 'ACC',   'department' => 'Finance'],
            ['title' => 'Junior Accountant',             'code' => 'JACC',  'department' => 'Finance'],
            ['title' => 'Accounts Officer',              'code' => 'AO',    'department' => 'Finance'],
            ['title' => 'Accounts Assistant',            'code' => 'AA',    'department' => 'Finance'],
            ['title' => 'Financial Analyst',             'code' => 'FA',    'department' => 'Finance'],
            ['title' => 'Budget Analyst',                'code' => 'BA',    'department' => 'Finance'],
            ['title' => 'Tax Consultant',                'code' => 'TC',    'department' => 'Finance'],
            ['title' => 'Internal Auditor',              'code' => 'IA',    'department' => 'Finance'],
            ['title' => 'Cost Accountant',               'code' => 'CA',    'department' => 'Finance'],
            ['title' => 'Cashier',                       'code' => 'CSH',   'department' => 'Finance'],

            // ── OPERATIONS ───────────────────────────────────────────
            ['title' => 'Operations Director',           'code' => 'OD',    'department' => 'Operations'],
            ['title' => 'Operations Manager',            'code' => 'OM',    'department' => 'Operations'],
            ['title' => 'Assistant Operations Manager',  'code' => 'AOM',   'department' => 'Operations'],
            ['title' => 'Operations Supervisor',         'code' => 'OS',    'department' => 'Operations'],
            ['title' => 'Operations Executive',          'code' => 'OE',    'department' => 'Operations'],
            ['title' => 'Operations Officer',            'code' => 'OO',    'department' => 'Operations'],
            ['title' => 'Operations Coordinator',        'code' => 'OC',    'department' => 'Operations'],
            ['title' => 'Process Improvement Manager',   'code' => 'PIM',   'department' => 'Operations'],
            ['title' => 'Quality Assurance Manager',     'code' => 'QAM',   'department' => 'Operations'],
            ['title' => 'Quality Assurance Officer',     'code' => 'QAO',   'department' => 'Operations'],
            ['title' => 'Quality Control Inspector',     'code' => 'QCI',   'department' => 'Operations'],

            // ── WAREHOUSE / LOGISTICS ────────────────────────────────
            ['title' => 'Warehouse Manager',             'code' => 'WM',    'department' => 'Warehouse'],
            ['title' => 'Assistant Warehouse Manager',   'code' => 'AWM',   'department' => 'Warehouse'],
            ['title' => 'Warehouse Supervisor',          'code' => 'WS',    'department' => 'Warehouse'],
            ['title' => 'Warehouse Incharge',            'code' => 'WI',    'department' => 'Warehouse'],
            ['title' => 'Warehouse Keeper',              'code' => 'WK',    'department' => 'Warehouse'],
            ['title' => 'Stock Controller',              'code' => 'SC',    'department' => 'Warehouse'],
            ['title' => 'Inventory Manager',             'code' => 'INM',   'department' => 'Warehouse'],
            ['title' => 'Inventory Officer',             'code' => 'INO',   'department' => 'Warehouse'],
            ['title' => 'Logistics Manager',             'code' => 'LM',    'department' => 'Warehouse'],
            ['title' => 'Logistics Coordinator',         'code' => 'LC',    'department' => 'Warehouse'],
            ['title' => 'Dispatch Officer',              'code' => 'DO',    'department' => 'Warehouse'],
            ['title' => 'Dispatch Clerk',                'code' => 'DC',    'department' => 'Warehouse'],
            ['title' => 'Forklift Operator',             'code' => 'FO',    'department' => 'Warehouse'],
            ['title' => 'Loader / Unloader',             'code' => 'LU',    'department' => 'Warehouse'],
            ['title' => 'Packing Supervisor',            'code' => 'PS',    'department' => 'Warehouse'],
            ['title' => 'Packing Staff',                 'code' => 'PKS',   'department' => 'Warehouse'],

            // ── SUPPLY CHAIN / PROCUREMENT ───────────────────────────
            ['title' => 'Supply Chain Manager',          'code' => 'SCM',   'department' => 'Supply Chain'],
            ['title' => 'Procurement Manager',           'code' => 'PCM',   'department' => 'Supply Chain'],
            ['title' => 'Procurement Officer',           'code' => 'PCO',   'department' => 'Supply Chain'],
            ['title' => 'Purchase Officer',              'code' => 'PUR',   'department' => 'Supply Chain'],
            ['title' => 'Vendor Manager',                'code' => 'VM',    'department' => 'Supply Chain'],
            ['title' => 'Import / Export Manager',       'code' => 'IEM',   'department' => 'Supply Chain'],
            ['title' => 'Customs Clearing Agent',        'code' => 'CCA',   'department' => 'Supply Chain'],

            // ── SALES & BUSINESS DEVELOPMENT ─────────────────────────
            ['title' => 'Sales Director',                'code' => 'SD',    'department' => 'Sales'],
            ['title' => 'Sales Manager',                 'code' => 'SM',    'department' => 'Sales'],
            ['title' => 'Assistant Sales Manager',       'code' => 'ASM',   'department' => 'Sales'],
            ['title' => 'Regional Sales Manager',        'code' => 'RSM',   'department' => 'Sales'],
            ['title' => 'Area Sales Manager',            'code' => 'ARSM',  'department' => 'Sales'],
            ['title' => 'Senior Sales Executive',        'code' => 'SSE',   'department' => 'Sales'],
            ['title' => 'Sales Executive',               'code' => 'SE',    'department' => 'Sales'],
            ['title' => 'Sales Officer',                 'code' => 'SO',    'department' => 'Sales'],
            ['title' => 'Sales Representative',          'code' => 'SR',    'department' => 'Sales'],
            ['title' => 'Business Development Manager',  'code' => 'BDM',   'department' => 'Sales'],
            ['title' => 'Business Development Officer',  'code' => 'BDO',   'department' => 'Sales'],
            ['title' => 'Key Account Manager',           'code' => 'KAM',   'department' => 'Sales'],
            ['title' => 'Pre-Sales Engineer',            'code' => 'PSE',   'department' => 'Sales'],

            // ── MARKETING ────────────────────────────────────────────
            ['title' => 'Marketing Director',            'code' => 'MKD',   'department' => 'Marketing'],
            ['title' => 'Marketing Manager',             'code' => 'MKM',   'department' => 'Marketing'],
            ['title' => 'Assistant Marketing Manager',   'code' => 'AMM',   'department' => 'Marketing'],
            ['title' => 'Brand Manager',                 'code' => 'BM',    'department' => 'Marketing'],
            ['title' => 'Digital Marketing Manager',     'code' => 'DMM',   'department' => 'Marketing'],
            ['title' => 'Digital Marketing Executive',   'code' => 'DME',   'department' => 'Marketing'],
            ['title' => 'Social Media Manager',          'code' => 'SMM',   'department' => 'Marketing'],
            ['title' => 'Social Media Executive',        'code' => 'SME',   'department' => 'Marketing'],
            ['title' => 'Content Writer',                'code' => 'CW',    'department' => 'Marketing'],
            ['title' => 'Graphic Designer',              'code' => 'GD',    'department' => 'Marketing'],
            ['title' => 'SEO Specialist',                'code' => 'SEO',   'department' => 'Marketing'],
            ['title' => 'Marketing Coordinator',         'code' => 'MC',    'department' => 'Marketing'],

            // ── INFORMATION TECHNOLOGY ───────────────────────────────
            ['title' => 'IT Director',                   'code' => 'ITD',   'department' => 'IT'],
            ['title' => 'IT Manager',                    'code' => 'ITM',   'department' => 'IT'],
            ['title' => 'Software Engineer',             'code' => 'SWE',   'department' => 'IT'],
            ['title' => 'Senior Software Engineer',      'code' => 'SSWE',  'department' => 'IT'],
            ['title' => 'Lead Software Engineer',        'code' => 'LSWE',  'department' => 'IT'],
            ['title' => 'Full Stack Developer',          'code' => 'FSD',   'department' => 'IT'],
            ['title' => 'Frontend Developer',            'code' => 'FED',   'department' => 'IT'],
            ['title' => 'Backend Developer',             'code' => 'BED',   'department' => 'IT'],
            ['title' => 'Mobile App Developer',          'code' => 'MAD',   'department' => 'IT'],
            ['title' => 'Database Administrator',        'code' => 'DBA',   'department' => 'IT'],
            ['title' => 'System Administrator',          'code' => 'SYSA',  'department' => 'IT'],
            ['title' => 'Network Engineer',              'code' => 'NE',    'department' => 'IT'],
            ['title' => 'IT Support Engineer',           'code' => 'ITSE',  'department' => 'IT'],
            ['title' => 'IT Support Officer',            'code' => 'ITSO',  'department' => 'IT'],
            ['title' => 'DevOps Engineer',               'code' => 'DVOE',  'department' => 'IT'],
            ['title' => 'UI/UX Designer',                'code' => 'UXD',   'department' => 'IT'],
            ['title' => 'QA Engineer',                   'code' => 'QAE',   'department' => 'IT'],
            ['title' => 'Project Manager',               'code' => 'PJM',   'department' => 'IT'],
            ['title' => 'Business Analyst',              'code' => 'BAN',   'department' => 'IT'],
            ['title' => 'ERP Consultant',                'code' => 'ERP',   'department' => 'IT'],

            // ── CUSTOMER SERVICE ─────────────────────────────────────
            ['title' => 'Customer Service Manager',      'code' => 'CSM',   'department' => 'Customer Service'],
            ['title' => 'Customer Service Supervisor',   'code' => 'CSS',   'department' => 'Customer Service'],
            ['title' => 'Customer Service Executive',    'code' => 'CSE',   'department' => 'Customer Service'],
            ['title' => 'Customer Service Officer',      'code' => 'CSO',   'department' => 'Customer Service'],
            ['title' => 'Customer Service Representative','code' => 'CSR',  'department' => 'Customer Service'],
            ['title' => 'Call Center Agent',             'code' => 'CCA',   'department' => 'Customer Service'],
            ['title' => 'Help Desk Officer',             'code' => 'HDO',   'department' => 'Customer Service'],
            ['title' => 'After Sales Executive',         'code' => 'ASE',   'department' => 'Customer Service'],

            // ── ADMINISTRATION ───────────────────────────────────────
            ['title' => 'Admin Manager',                 'code' => 'ADM',   'department' => 'Administration'],
            ['title' => 'Admin Officer',                 'code' => 'ADO',   'department' => 'Administration'],
            ['title' => 'Admin Assistant',               'code' => 'ADA',   'department' => 'Administration'],
            ['title' => 'Executive Assistant',           'code' => 'EA',    'department' => 'Administration'],
            ['title' => 'Personal Assistant',            'code' => 'PA',    'department' => 'Administration'],
            ['title' => 'Office Manager',                'code' => 'OFM',   'department' => 'Administration'],
            ['title' => 'Front Desk Officer',            'code' => 'FDO',   'department' => 'Administration'],
            ['title' => 'Receptionist',                  'code' => 'REC',   'department' => 'Administration'],
            ['title' => 'Office Boy',                    'code' => 'OB',    'department' => 'Administration'],
            ['title' => 'Peon',                          'code' => 'PEO',   'department' => 'Administration'],
            ['title' => 'Courier / Rider',               'code' => 'CRD',   'department' => 'Administration'],
            ['title' => 'Driver',                        'code' => 'DRV',   'department' => 'Administration'],

            // ── SECURITY ─────────────────────────────────────────────
            ['title' => 'Security Manager',              'code' => 'SECM',  'department' => 'Security'],
            ['title' => 'Security Supervisor',           'code' => 'SECS',  'department' => 'Security'],
            ['title' => 'Security Guard',                'code' => 'SG',    'department' => 'Security'],
            ['title' => 'CCTV Operator',                 'code' => 'CCTV',  'department' => 'Security'],

            // ── ENGINEERING / TECHNICAL ──────────────────────────────
            ['title' => 'Chief Engineer',                'code' => 'CE',    'department' => 'Engineering'],
            ['title' => 'Senior Engineer',               'code' => 'SENG',  'department' => 'Engineering'],
            ['title' => 'Engineer',                      'code' => 'ENG',   'department' => 'Engineering'],
            ['title' => 'Junior Engineer',               'code' => 'JENG',  'department' => 'Engineering'],
            ['title' => 'Electrical Engineer',           'code' => 'ELE',   'department' => 'Engineering'],
            ['title' => 'Mechanical Engineer',           'code' => 'MECH',  'department' => 'Engineering'],
            ['title' => 'Civil Engineer',                'code' => 'CIVE',  'department' => 'Engineering'],
            ['title' => 'Production Engineer',           'code' => 'PRDE',  'department' => 'Engineering'],
            ['title' => 'Maintenance Engineer',          'code' => 'MNTE',  'department' => 'Engineering'],
            ['title' => 'Maintenance Supervisor',        'code' => 'MNTS',  'department' => 'Engineering'],
            ['title' => 'Maintenance Technician',        'code' => 'MNTT',  'department' => 'Engineering'],
            ['title' => 'Electrician',                   'code' => 'ELEC',  'department' => 'Engineering'],
            ['title' => 'Plumber',                       'code' => 'PLB',   'department' => 'Engineering'],
            ['title' => 'Welder',                        'code' => 'WLD',   'department' => 'Engineering'],
            ['title' => 'Foreman',                       'code' => 'FORE',  'department' => 'Engineering'],
            ['title' => 'Technician',                    'code' => 'TECH',  'department' => 'Engineering'],
            ['title' => 'Helper / Labour',               'code' => 'HLP',   'department' => 'Engineering'],

            // ── PRODUCTION / MANUFACTURING ───────────────────────────
            ['title' => 'Production Manager',            'code' => 'PRDM',  'department' => 'Production'],
            ['title' => 'Production Supervisor',         'code' => 'PRDS',  'department' => 'Production'],
            ['title' => 'Production Incharge',           'code' => 'PRDI',  'department' => 'Production'],
            ['title' => 'Production Operator',           'code' => 'PRDO',  'department' => 'Production'],
            ['title' => 'Machine Operator',              'code' => 'MOP',   'department' => 'Production'],
            ['title' => 'Line Leader',                   'code' => 'LL',    'department' => 'Production'],
            ['title' => 'Shift Incharge',                'code' => 'SHI',   'department' => 'Production'],
            ['title' => 'Plant Manager',                 'code' => 'PLM',   'department' => 'Production'],

            // ── HEALTH, SAFETY & ENVIRONMENT ─────────────────────────
            ['title' => 'HSE Manager',                   'code' => 'HSEM',  'department' => 'HSE'],
            ['title' => 'HSE Officer',                   'code' => 'HSEO',  'department' => 'HSE'],
            ['title' => 'Safety Officer',                'code' => 'SAFO',  'department' => 'HSE'],
            ['title' => 'Environment Officer',           'code' => 'ENVO',  'department' => 'HSE'],
            ['title' => 'Fire & Safety Officer',         'code' => 'FSO',   'department' => 'HSE'],

            // ── LEGAL & COMPLIANCE ───────────────────────────────────
            ['title' => 'Legal Manager',                 'code' => 'LGL',   'department' => 'Legal'],
            ['title' => 'Legal Officer',                 'code' => 'LGLO',  'department' => 'Legal'],
            ['title' => 'Compliance Manager',            'code' => 'COM',   'department' => 'Legal'],
            ['title' => 'Compliance Officer',            'code' => 'COMO',  'department' => 'Legal'],
            ['title' => 'Company Secretary',             'code' => 'CSEC',  'department' => 'Legal'],

            // ── RESEARCH & DEVELOPMENT ───────────────────────────────
            ['title' => 'R&D Manager',                   'code' => 'RDM',   'department' => 'R&D'],
            ['title' => 'R&D Engineer',                  'code' => 'RDE',   'department' => 'R&D'],
            ['title' => 'Product Manager',               'code' => 'PRDMG', 'department' => 'R&D'],
            ['title' => 'Product Designer',              'code' => 'PRDDS', 'department' => 'R&D'],
            ['title' => 'Research Analyst',              'code' => 'RA',    'department' => 'R&D'],

            // ── MEDICAL / HEALTH ─────────────────────────────────────
            ['title' => 'Medical Officer',               'code' => 'MO',    'department' => 'Medical'],
            ['title' => 'Nurse',                         'code' => 'NRS',   'department' => 'Medical'],
            ['title' => 'Paramedic',                     'code' => 'PARA',  'department' => 'Medical'],
            ['title' => 'First Aider',                   'code' => 'FAID',  'department' => 'Medical'],

            // ── GENERAL / OTHERS ─────────────────────────────────────
            ['title' => 'Intern',                        'code' => 'INT',   'department' => 'General'],
            ['title' => 'Trainee',                       'code' => 'TRN',   'department' => 'General'],
            ['title' => 'Apprentice',                    'code' => 'APR',   'department' => 'General'],
            ['title' => 'Consultant',                    'code' => 'CONS',  'department' => 'General'],
            ['title' => 'Coordinator',                   'code' => 'CORD',  'department' => 'General'],
            ['title' => 'Supervisor',                    'code' => 'SUP',   'department' => 'General'],
            ['title' => 'Team Lead',                     'code' => 'TL',    'department' => 'General'],
            ['title' => 'Senior Officer',                'code' => 'SOFC',  'department' => 'General'],
            ['title' => 'Officer',                       'code' => 'OFC',   'department' => 'General'],
            ['title' => 'Assistant',                     'code' => 'ASST',  'department' => 'General'],
            ['title' => 'Clerk',                         'code' => 'CLK',   'department' => 'General'],
            ['title' => 'Data Entry Operator',           'code' => 'DEO',   'department' => 'General'],
            ['title' => 'Storekeeper',                   'code' => 'STK',   'department' => 'General'],

        ];

        foreach ($designations as $designation) {
            Designation::firstOrCreate(
                [
                    'company_id' => $companyId,
                    'code'       => $designation['code'],
                ],
                [
                    'title'      => $designation['title'],
                    'department' => $designation['department'],
                    'is_active'  => true,
                ]
            );
        }

        $this->command->info('✅ ' . count($designations) . ' designations seeded successfully.');
    }
}