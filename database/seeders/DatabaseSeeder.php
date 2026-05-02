<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════');
        $this->command->info('  Prescribe & Co — Database Seeder');
        $this->command->info('═══════════════════════════════════════');
        $this->command->info('');

        $this->call([
            // Order matters — respect FK dependencies
            TreatmentCategorySeeder::class,
            TreatmentSeeder::class,
            ProductSeeder::class,
            GpSurgerySeeder::class,
            StaffSeeder::class,
            WorkflowRuleSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('✓ All seeders complete.');
        $this->command->info('');
        $this->command->warn('IMPORTANT POST-SEED STEPS:');
        $this->command->warn('1. Log into CRM and complete Super Admin 2FA setup immediately.');
        $this->command->warn('2. Change the Super Admin password (set via SUPER_ADMIN_PASSWORD in .env).');
        $this->command->warn('3. Configure pharmacy details in config/pharmacy.php.');
        $this->command->warn('4. Add real stock batches and supplier records via the CRM Inventory module.');
        $this->command->info('');
    }
}
