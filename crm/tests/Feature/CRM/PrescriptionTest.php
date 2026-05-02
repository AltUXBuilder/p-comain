<?php

namespace Tests\Feature\CRM;

use App\Models\Consultation;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Staff;
use App\Services\PrescriptionService;
use App\Services\PrescriptionPdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Feature\CRM\Helpers\CrmTestHelper;

class PrescriptionTest extends TestCase
{
    use RefreshDatabase, CrmTestHelper;

    /** @test */
    public function prescription_number_format_is_correct()
    {
        $rx = new Prescription();
        $number = Prescription::generateNumber();

        $this->assertMatchesRegularExpression('/^RX-\d{8}-[A-Z0-9]{5}$/', $number);
    }

    /** @test */
    public function prescription_auto_snapshots_prescriber_gphc_on_create()
    {
        $prescriber = $this->makePrescriber(['gphc_number' => '1234567']);
        $patient    = $this->makePatient();

        $rx = Prescription::create([
            'patient_id'    => $patient->id,
            'prescriber_id' => $prescriber->id,
            'product_id'    => 1,
            'status'        => Prescription::STATUS_DRAFT,
        ]);

        $this->assertEquals('1234567', $rx->prescriber_gphc_number);
        $this->assertEquals($prescriber->full_name, $rx->prescriber_name);
    }

    /** @test */
    public function prescription_status_transitions_are_enforced()
    {
        $rx = $this->makeDraftPrescription();

        // Valid: draft → pending_review
        $this->assertTrue($rx->canTransitionTo(Prescription::STATUS_PENDING_REVIEW));

        // Invalid: draft → dispensed (skipping steps)
        $this->assertFalse($rx->canTransitionTo(Prescription::STATUS_DISPENSED));

        // Invalid: draft → approved (skipping pending_review)
        $this->assertFalse($rx->canTransitionTo(Prescription::STATUS_APPROVED));
    }

    /** @test */
    public function prescription_cannot_transition_backwards()
    {
        $rx = $this->makePrescriptionWithStatus(Prescription::STATUS_APPROVED);

        $this->assertFalse($rx->canTransitionTo(Prescription::STATUS_DRAFT));
        $this->assertFalse($rx->canTransitionTo(Prescription::STATUS_PENDING_REVIEW));
    }

    /** @test */
    public function prescription_index_only_shows_own_prescriptions_for_prescriber()
    {
        $prescriber1 = $this->makePrescriberWith2fa();
        $prescriber2 = $this->makePrescriberWith2fa();
        $patient     = $this->makePatient();

        Prescription::create([
            'prescription_number' => 'RX-TEST-00001',
            'patient_id'    => $patient->id,
            'prescriber_id' => $prescriber1->id,
            'product_id'    => 1,
            'status'        => Prescription::STATUS_DRAFT,
        ]);

        $response = $this->actingAs($prescriber2, 'staff')
            ->get('/prescriptions');

        $response->assertOk();
        $response->assertDontSee('RX-TEST-00001');
    }

    /** @test */
    public function super_admin_sees_all_prescriptions()
    {
        $admin      = $this->makeSuperAdminWith2fa();
        $prescriber = $this->makePrescriberWith2fa();
        $patient    = $this->makePatient();

        Prescription::create([
            'prescription_number' => 'RX-TEST-ADMIN',
            'patient_id'    => $patient->id,
            'prescriber_id' => $prescriber->id,
            'product_id'    => 1,
            'status'        => Prescription::STATUS_APPROVED,
        ]);

        $this->actingAs($admin, 'staff')
             ->get('/prescriptions')
             ->assertOk()
             ->assertSee('RX-TEST-ADMIN');
    }

    /** @test */
    public function prescription_sign_fails_without_signature_on_file()
    {
        $prescriber = $this->makePrescriberWith2fa(['signature_path' => null]);
        $rx = $this->makePrescriptionWithStatus(Prescription::STATUS_PENDING_REVIEW, $prescriber);

        $this->actingAs($prescriber, 'staff')
             ->post("/prescriptions/{$rx->id}/sign")
             ->assertSessionHasErrors('signature');
    }

    /** @test */
    public function archived_prescription_cannot_be_signed()
    {
        $prescriber = $this->makePrescriberWith2fa();
        $rx = $this->makePrescriptionWithStatus(Prescription::STATUS_ARCHIVED, $prescriber);

        $this->actingAs($prescriber, 'staff')
             ->post("/prescriptions/{$rx->id}/sign")
             ->assertStatus(422);
    }

    /** @test */
    public function dispenser_cannot_sign_prescriptions()
    {
        $dispenser = $this->makeStaffWith2fa(['role' => Staff::ROLE_DISPENSER]);
        $rx = $this->makePrescriptionWithStatus(Prescription::STATUS_PENDING_REVIEW);

        $this->actingAs($dispenser, 'staff')
             ->post("/prescriptions/{$rx->id}/sign")
             ->assertForbidden();
    }

    /** @test */
    public function register_export_produces_csv()
    {
        $admin = $this->makeSuperAdminWith2fa();

        $response = $this->actingAs($admin, 'staff')
            ->get('/prescriptions/register/export');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeDraftPrescription(Staff $prescriber = null): Prescription
    {
        return $this->makePrescriptionWithStatus(Prescription::STATUS_DRAFT, $prescriber);
    }

    private function makePrescriptionWithStatus(string $status, Staff $prescriber = null): Prescription
    {
        $prescriber ??= $this->makePrescriber();
        $patient      = $this->makePatient();

        return Prescription::create([
            'prescription_number'   => Prescription::generateNumber(),
            'patient_id'            => $patient->id,
            'prescriber_id'         => $prescriber->id,
            'prescriber_gphc_number' => $prescriber->gphc_number,
            'prescriber_name'        => $prescriber->full_name,
            'product_id'             => 1,
            'status'                 => $status,
            'dosage_instructions'    => 'Once daily',
            'quantity'               => '28 tablets',
        ]);
    }
}
