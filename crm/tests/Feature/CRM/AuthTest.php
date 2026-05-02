<?php

namespace Tests\Feature\CRM;

use App\Models\IpWhitelistEntry;
use App\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed a whitelist entry for localhost so IP middleware allows test requests
        IpWhitelistEntry::create([
            'ip_address' => '127.0.0.1',
            'label'      => 'Test',
            'active'     => true,
        ]);
        Cache::forget('crm:ip_whitelist');
    }

    // ── Login ─────────────────────────────────────────────────────────────────

    /** @test */
    public function login_page_loads()
    {
        $this->get('/login')->assertOk()->assertViewIs('auth.login');
    }

    /** @test */
    public function valid_credentials_redirect_to_2fa_challenge()
    {
        $staff = $this->makeActiveStaff();

        $this->post('/login', [
            'email'    => $staff->email,
            'password' => 'Password1!',
        ])->assertRedirect();
    }

    /** @test */
    public function wrong_password_stays_on_login()
    {
        $staff = $this->makeActiveStaff();

        $this->post('/login', [
            'email'    => $staff->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors();
    }

    /** @test */
    public function inactive_account_cannot_login()
    {
        $staff = $this->makeStaff(['active' => false]);

        $this->post('/login', [
            'email'    => $staff->email,
            'password' => 'Password1!',
        ])->assertSessionHasErrors();
    }

    // ── IP Whitelist ──────────────────────────────────────────────────────────

    /** @test */
    public function unlisted_ip_receives_403()
    {
        // Remove localhost from whitelist
        IpWhitelistEntry::where('ip_address', '127.0.0.1')->delete();
        Cache::forget('crm:ip_whitelist');

        $this->get('/login')->assertStatus(403);
    }

    /** @test */
    public function listed_ip_passes_whitelist_middleware()
    {
        // 127.0.0.1 seeded in setUp
        $this->get('/login')->assertOk();
    }

    /** @test */
    public function cidr_range_whitelist_allows_matching_ip()
    {
        IpWhitelistEntry::create(['ip_address' => '10.0.0.0/8', 'label' => 'Office', 'active' => true]);
        Cache::forget('crm:ip_whitelist');

        $this->withServerVariables(['REMOTE_ADDR' => '10.1.2.3'])
             ->get('/login')
             ->assertOk();
    }

    // ── Dashboard requires auth ────────────────────────────────────────────────

    /** @test */
    public function unauthenticated_dashboard_redirects_to_login()
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    /** @test */
    public function authenticated_staff_without_2fa_redirected_to_setup()
    {
        $staff = $this->makeActiveStaff(['two_factor_confirmed' => false]);

        $this->actingAs($staff, 'staff')
             ->get('/dashboard')
             ->assertRedirect(route('two-factor.enable'));
    }

    /** @test */
    public function authenticated_staff_with_2fa_sees_dashboard()
    {
        $staff = $this->makeActiveStaffWith2fa();

        $this->actingAs($staff, 'staff')
             ->get('/dashboard')
             ->assertOk()
             ->assertViewIs('dashboard');
    }

    // ── Role middleware ───────────────────────────────────────────────────────

    /** @test */
    public function dispenser_cannot_access_prescriptions()
    {
        $staff = $this->makeActiveStaffWith2fa(['role' => Staff::ROLE_DISPENSER]);

        $this->actingAs($staff, 'staff')
             ->get('/prescriptions')
             ->assertForbidden();
    }

    /** @test */
    public function prescriber_can_access_prescriptions()
    {
        $staff = $this->makeActiveStaffWith2fa(['role' => Staff::ROLE_PRESCRIBER]);

        $this->actingAs($staff, 'staff')
             ->get('/prescriptions')
             ->assertOk();
    }

    /** @test */
    public function finance_cannot_access_patients()
    {
        $staff = $this->makeActiveStaffWith2fa(['role' => Staff::ROLE_FINANCE]);

        $this->actingAs($staff, 'staff')
             ->get('/patients')
             ->assertForbidden();
    }

    /** @test */
    public function only_super_admin_can_access_staff_management()
    {
        foreach ([Staff::ROLE_PRESCRIBER, Staff::ROLE_DISPENSER, Staff::ROLE_FINANCE] as $role) {
            $staff = $this->makeActiveStaffWith2fa(['role' => $role]);
            $this->actingAs($staff, 'staff')
                 ->get('/staff')
                 ->assertForbidden();
        }

        $admin = $this->makeActiveStaffWith2fa(['role' => Staff::ROLE_SUPER_ADMIN]);
        $this->actingAs($admin, 'staff')
             ->get('/staff')
             ->assertOk();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeStaff(array $overrides = []): Staff
    {
        return Staff::create(array_merge([
            'first_name' => 'Test',
            'last_name'  => 'Staff',
            'email'      => 'test' . uniqid() . '@prescribeandco.co.uk',
            'password'   => Hash::make('Password1!'),
            'role'       => Staff::ROLE_PRESCRIBER,
            'active'     => true,
        ], $overrides));
    }

    private function makeActiveStaff(array $overrides = []): Staff
    {
        return $this->makeStaff(array_merge(['active' => true], $overrides));
    }

    private function makeActiveStaffWith2fa(array $overrides = []): Staff
    {
        return $this->makeStaff(array_merge([
            'active'              => true,
            'two_factor_confirmed' => true,
            'two_factor_secret'   => encrypt('JBSWY3DPEHPK3PXP'), // dummy TOTP secret
            'welcome_completed_at' => now(),
        ], $overrides));
    }
}
