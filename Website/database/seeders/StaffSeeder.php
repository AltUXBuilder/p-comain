<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin — created directly (no welcome email flow for initial setup)
        DB::table('staff')->updateOrInsert(
            ['email' => 'admin@prescribeandco.co.uk'],
            [
                'first_name'              => 'Super',
                'last_name'               => 'Admin',
                'email'                   => 'admin@prescribeandco.co.uk',
                'password'                => Hash::make(env('SUPER_ADMIN_PASSWORD', 'ChangeMe123!')),
                'role'                    => 'super_admin',
                'gphc_number'             => null,
                'two_factor_confirmed'    => false, // must complete 2FA on first login
                'active'                  => true,
                'welcome_completed_at'    => now(),
                'created_at'              => now(),
                'updated_at'              => now(),
            ]
        );

        $this->command->info('Super Admin seeded. IMPORTANT: Change password and complete 2FA setup on first login.');
        $this->command->warn('Default password is set via SUPER_ADMIN_PASSWORD in .env');
    }
}
