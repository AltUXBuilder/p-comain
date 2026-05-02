<?php

namespace Tests\Feature\CRM;

use App\Models\Patient;
use App\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Feature\CRM\Helpers\CrmTestHelper;

class PatientTest extends TestCase
{
    use RefreshDatabase, CrmTestHelper;

    /** @test */
    public function patient_index_is_accessible_to_clinical_staff()
    {
        foreach ([Staff::ROLE_SUPER_ADMIN, Staff::ROLE_SUPERINTENDENT_PHARMACIST, Staff::ROLE_PRESCRIBER] as $role) {
            $staff = $this->makeStaffWith2fa(['role' => $role]);
            $this->actingAs($staff, 'staff')
                 ->get('/patients')
                 ->assertOk();
        }
    }

    /** @test */
    public function finance_staff_cannot_access_patients()
    {
        $staff = $this->makeStaffWith2fa(['role' => Staff::ROLE_FINANCE]);
        $this->actingAs($staff, 'staff')
             ->get('/patients')
             ->assertForbidden();
    }

    /** @test */
    public function patient_search_filters_by_name()
    {
        $superAdmin = $this->makeSuperAdminWith2fa();

        Patient::create([
            'first_name' => 'Unique', 'last_name' => 'Patient',
            'email' => 'unique@test.com', 'password' => bcrypt('pass'),
        ]);

        $this->actingAs($superAdmin, 'staff')
             ->get('/patients?search=Unique')
             ->assertOk()
             ->assertSee('Unique');
    }

    /** @test */
    public function patient_can_be_flagged_as_high_risk()
    {
        $prescriber = $this->makePrescriberWith2fa();
        $patient    = $this->makePatient();

        $this->actingAs($prescriber, 'staff')
             ->post("/patients/{$patient->id}/flag", ['reason' => 'Test flag reason'])
             ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id'           => $patient->id,
            'risk_flagged' => true,
        ]);
    }

    /** @test */
    public function do_not_treat_blocks_clinical_actions()
    {
        $prescriber = $this->makePrescriberWith2fa();
        $patient    = $this->makePatient(['do_not_treat' => true]);

        $this->assertFalse($patient->isActionable());
    }

    /** @test */
    public function deceased_patient_is_not_actionable()
    {
        $patient = $this->makePatient(['deceased' => true]);
        $this->assertFalse($patient->isActionable());
    }

    /** @test */
    public function patient_flag_can_be_removed()
    {
        $prescriber = $this->makePrescriberWith2fa();
        $patient    = $this->makePatient(['risk_flagged' => true]);

        $this->actingAs($prescriber, 'staff')
             ->post("/patients/{$patient->id}/unflag")
             ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id'           => $patient->id,
            'risk_flagged' => false,
        ]);
    }

    /** @test */
    public function patient_record_view_is_audit_logged()
    {
        $prescriber = $this->makePrescriberWith2fa();
        $patient    = $this->makePatient();

        $this->actingAs($prescriber, 'staff')
             ->get("/patients/{$patient->id}");

        $this->assertDatabaseHas('audit_logs', [
            'staff_id'    => $prescriber->id,
            'action'      => 'patient_record_viewed',
            'entity_type' => 'patient',
            'entity_id'   => $patient->id,
        ]);
    }
}
