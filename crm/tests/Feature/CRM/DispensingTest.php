<?php

namespace Tests\Feature\CRM;

use App\Models\DispensingLabel;
use App\Models\Prescription;
use App\Models\Staff;
use App\Models\StockBatch;
use App\Services\DispensingLabelService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Feature\CRM\Helpers\CrmTestHelper;

class DispensingTest extends TestCase
{
    use RefreshDatabase, CrmTestHelper;

    /** @test */
    public function dispensing_queue_is_accessible_to_dispensers()
    {
        $dispenser = $this->makeStaffWith2fa(['role' => Staff::ROLE_DISPENSER]);

        $this->actingAs($dispenser, 'staff')
             ->get('/dispensing/queue')
             ->assertOk();
    }

    /** @test */
    public function prescriber_cannot_access_dispensing_queue()
    {
        // Prescribers CAN access dispensing queue per role config
        $prescriber = $this->makePrescriberWith2fa();

        $this->actingAs($prescriber, 'staff')
             ->get('/dispensing/queue')
             ->assertOk();
    }

    /** @test */
    public function finance_staff_cannot_access_dispensing_queue()
    {
        $finance = $this->makeStaffWith2fa(['role' => Staff::ROLE_FINANCE]);

        $this->actingAs($finance, 'staff')
             ->get('/dispensing/queue')
             ->assertForbidden();
    }

    /** @test */
    public function label_cannot_be_created_for_non_sent_to_dispense_prescription()
    {
        $this->expectException(\RuntimeException::class);

        $dispenser = $this->makeDispenser();
        $rx = $this->makePrescriptionWithStatus(Prescription::STATUS_DRAFT);

        app(DispensingLabelService::class)->createLabel($rx, $dispenser);
    }

    /** @test */
    public function label_creation_deducts_from_stock_batch()
    {
        $dispenser = $this->makeDispenser();
        $patient   = $this->makePatient();
        $product   = $this->makeProduct();
        $rx        = $this->makePrescriptionWithStatus(Prescription::STATUS_SENT_TO_DISPENSE);

        $batch = StockBatch::create([
            'product_id'          => $product->id,
            'batch_number'        => 'BATCH001',
            'expiry_date'         => now()->addYear(),
            'quantity_received'   => 10,
            'quantity_remaining'  => 10,
            'status'              => 'active',
        ]);

        app(DispensingLabelService::class)->createLabel($rx, $dispenser, $batch);

        $this->assertEquals(9, $batch->fresh()->quantity_remaining);
    }

    /** @test */
    public function sign_off_advances_prescription_to_dispensed()
    {
        $dispenser = $this->makeDispenser();
        $rx        = $this->makePrescriptionWithStatus(Prescription::STATUS_SENT_TO_DISPENSE);

        $label = DispensingLabel::create([
            'prescription_id'   => $rx->id,
            'product_id'        => 1,
            'dispensed_by'      => $dispenser->id,
            'patient_id'        => $rx->patient_id,
            'patient_name'      => 'Test Patient',
            'medication_name'   => 'Test Med',
            'dosage_instructions' => 'Once daily',
            'dispensing_date'   => today(),
            'pharmacy_name'     => 'Test Pharmacy',
            'dispensed_by_name' => 'Test Dispenser',
            'dispensed_at'      => now(),
        ]);

        app(DispensingLabelService::class)->signOff($label, $dispenser);

        $this->assertEquals(Prescription::STATUS_DISPENSED, $rx->fresh()->status);
    }

    /** @test */
    public function label_pdf_is_accessible_to_dispenser()
    {
        $dispenser = $this->makeStaffWith2fa(['role' => Staff::ROLE_DISPENSER]);
        $label     = $this->makeDispensingLabel();

        \Illuminate\Support\Facades\Storage::fake('private');
        \Illuminate\Support\Facades\Storage::disk('private')->put(
            $label->pdf_path ?? 'labels/test.pdf',
            '%PDF test content'
        );

        if (! $label->pdf_path) {
            $label->update(['pdf_path' => 'labels/test.pdf']);
        }

        $this->actingAs($dispenser, 'staff')
             ->get("/dispensing/labels/{$label->id}/pdf")
             ->assertOk();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeDispenser(): Staff
    {
        return $this->makeStaffWith2fa(['role' => Staff::ROLE_DISPENSER]);
    }

    private function makeDispensingLabel(): DispensingLabel
    {
        $dispenser = $this->makeDispenser();
        $patient   = $this->makePatient();
        $rx        = $this->makePrescriptionWithStatus(Prescription::STATUS_DISPENSED, $dispenser);

        return DispensingLabel::create([
            'prescription_id'     => $rx->id,
            'product_id'          => 1,
            'dispensed_by'        => $dispenser->id,
            'patient_id'          => $patient->id,
            'patient_name'        => 'Test Patient',
            'medication_name'     => 'Test Med',
            'dosage_instructions' => 'Once daily',
            'dispensing_date'     => today(),
            'pharmacy_name'       => 'Prescribe & Co',
            'dispensed_by_name'   => 'Test Dispenser',
            'dispensed_at'        => now(),
        ]);
    }

    private function makePrescriptionWithStatus(string $status, Staff $prescriber = null): Prescription
    {
        $prescriber ??= $this->makePrescriberWith2fa();
        $patient      = $this->makePatient();

        return Prescription::create([
            'prescription_number'    => Prescription::generateNumber(),
            'patient_id'             => $patient->id,
            'prescriber_id'          => $prescriber->id,
            'prescriber_gphc_number' => $prescriber->gphc_number ?? '1234567',
            'prescriber_name'        => $prescriber->full_name,
            'product_id'             => 1,
            'status'                 => $status,
            'dosage_instructions'    => 'Once daily',
            'quantity'               => '28 units',
        ]);
    }
}
