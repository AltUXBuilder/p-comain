<?php

namespace Database\Seeders;

use App\Models\Staff;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\StaffWelcomeMail;

class StaffSeeder extends Seeder
{
    /**
     * Seed the initial Super Admin account.
     *
     * The Super Admin is the only account that exists before any CRM
     * usage. All other staff are created through the CRM itself.
     *
     * IMPORTANT: Change the email/password before deploying to production.
     * The seeder sets an initial password directly (bypassing the welcome
     * flow) so the Super Admin can log in immediately and then create
     * other staff accounts.
     *
     * The Super Admin will still need to complete 2FA enrolment on first login.
     */
    public function run(): void
    {
        $superAdmin = Staff::firstOrCreate(
            ['email' => env('SUPER_ADMIN_EMAIL', 'admin@prescribeandco.co.uk')],
            [
                'first_name'              => 'Super',
                'last_name'               => 'Admin',
                'role'                    => Staff::ROLE_SUPER_ADMIN,
                'password'                => Hash::make(env('SUPER_ADMIN_PASSWORD', 'ChangeMe!2024#')),
                'active'                  => true,
                'welcome_completed_at'    => now(),
                // 2FA not yet set — will be prompted on first login
                'two_factor_confirmed'    => false,
            ]
        );

        $this->command->info("Super Admin: {$superAdmin->email}");

        // Seed a localhost IP whitelist entry
        \App\Models\IpWhitelistEntry::firstOrCreate(
            ['ip_address' => '127.0.0.1'],
            ['label' => 'Localhost (development)', 'active' => true]
        );

        $this->command->info('IP whitelist seeded (127.0.0.1).');
        $this->command->warn('Remember to add your production office/VPN IPs in Settings → IP Whitelist.');
    }
}
