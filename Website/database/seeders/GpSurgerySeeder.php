<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GpSurgerySeeder extends Seeder
{
    public function run(): void
    {
        // Sample GP surgeries — in production this would be imported
        // from the NHS ODS (Organisation Data Service) API
        $surgeries = [
            ['name' => 'The Beeches Medical Centre',   'ods_code' => 'A81001', 'city' => 'Birmingham',  'postcode' => 'B1 1AA',  'phone' => '0121 000 0001'],
            ['name' => 'Riverside Surgery',             'ods_code' => 'A81002', 'city' => 'London',      'postcode' => 'SW1A 1AA', 'phone' => '020 0000 0001'],
            ['name' => 'Parkview Health Centre',        'ods_code' => 'A81003', 'city' => 'Manchester',  'postcode' => 'M1 1AA',  'phone' => '0161 000 0001'],
            ['name' => 'The Elms Surgery',              'ods_code' => 'A81004', 'city' => 'Leeds',       'postcode' => 'LS1 1AA', 'phone' => '0113 000 0001'],
            ['name' => 'Northgate Medical Practice',    'ods_code' => 'A81005', 'city' => 'Bristol',     'postcode' => 'BS1 1AA', 'phone' => '0117 000 0001'],
            ['name' => 'Central Surgery',               'ods_code' => 'A81006', 'city' => 'Edinburgh',   'postcode' => 'EH1 1AA', 'phone' => '0131 000 0001'],
            ['name' => 'The Oaks Medical Centre',       'ods_code' => 'A81007', 'city' => 'Cardiff',     'postcode' => 'CF1 1AA', 'phone' => '029 0000 0001'],
            ['name' => 'Meadowside Surgery',            'ods_code' => 'A81008', 'city' => 'Sheffield',   'postcode' => 'S1 1AA',  'phone' => '0114 000 0001'],
        ];

        foreach ($surgeries as $surgery) {
            DB::table('gp_surgeries')->updateOrInsert(
                ['ods_code' => $surgery['ods_code']],
                array_merge($surgery, [
                    'active'     => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('Sample GP surgeries seeded. Import full NHS ODS data via php artisan pando:import-gp-surgeries for production.');
    }
}
