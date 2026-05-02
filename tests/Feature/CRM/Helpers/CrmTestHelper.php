<?php

namespace Tests\Feature\CRM\Helpers;

use App\Models\Patient;
use App\Models\Product;
use App\Models\Staff;
use Illuminate\Support\Facades\Hash;

trait CrmTestHelper
{
    // ── Staff factories ───────────────────────────────────────────────────────

    protected function makeStaff(array $overrides = []): Staff
    {
        return Staff::create(array_merge([
            'first_name'             => 'Test',
            'last_name'              => 'User',
            'email'                  => 'staff_' . uniqid() . '@test.com',
            'password'               => Hash::make('Password1!'),
            'role'                   => Staff::ROLE_PRESCRIBER,
            'active'                 => true,
            'welcome_completed_at'   => now(),
            'two_factor_confirmed'   => false,
        ], $overrides));
    }

    protected function makeStaffWith2fa(array $overrides = []): Staff
    {
        return $this->makeStaff(array_merge([
            'two_factor_confirmed' => true,
            'two_factor_secret'    => encrypt('JBSWY3DPEHPK3PXP'),
        ], $overrides));
    }

    protected function makePrescriber(array $overrides = []): Staff
    {
        return $this->makeStaff(array_merge([
            'role'        => Staff::ROLE_PRESCRIBER,
            'gphc_number' => '1234567',
        ], $overrides));
    }

    protected function makePrescriberWith2fa(array $overrides = []): Staff
    {
        return $this->makeStaffWith2fa(array_merge([
            'role'        => Staff::ROLE_PRESCRIBER,
            'gphc_number' => '1234567',
        ], $overrides));
    }

    protected function makeSuperAdminWith2fa(array $overrides = []): Staff
    {
        return $this->makeStaffWith2fa(array_merge([
            'role' => Staff::ROLE_SUPER_ADMIN,
        ], $overrides));
    }

    // ── Patient factory ───────────────────────────────────────────────────────

    protected function makePatient(array $overrides = []): Patient
    {
        return Patient::create(array_merge([
            'first_name'    => 'Test',
            'last_name'     => 'Patient',
            'email'         => 'patient_' . uniqid() . '@test.com',
            'password'      => Hash::make('Password1!'),
            'date_of_birth' => now()->subYears(30),
            'deceased'      => false,
            'do_not_treat'  => false,
            'risk_flagged'  => false,
        ], $overrides));
    }

    // ── Product factory ───────────────────────────────────────────────────────

    protected function makeProduct(array $overrides = []): Product
    {
        return Product::create(array_merge([
            'name'     => 'Test Product',
            'strength' => '10mg',
            'form'     => 'Tablet',
            'active'   => true,
        ], $overrides));
    }
}
