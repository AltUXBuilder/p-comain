<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * LocalTestSeeder
 *
 * Creates a full set of test data for local development and QA testing.
 * No Stripe account, SendGrid or real API keys required.
 *
 * Run with:
 *   php artisan db:seed --class=LocalTestSeeder
 *
 * Created accounts:
 * ─────────────────────────────────────────────────────────────────────────────
 *  WEBSITE (patient)
 *   Email:    test@prescribeandco.test
 *   Password: Password1!
 *   2FA:      disabled (bypassed for local testing)
 *
 *  CRM (staff — one per role)
 *   super@crm.test           / Password1!  — Super Admin
 *   super@crm.test           / Password1!  — Superintendent Pharmacist (GPhC: 1234567)
 *   prescriber@crm.test      / Password1!  — Prescriber (GPhC: 7654321)
 *   dispenser@crm.test       / Password1!  — Dispenser
 *   support@crm.test         / Password1!  — Customer Support
 *   finance@crm.test         / Password1!  — Finance
 * ─────────────────────────────────────────────────────────────────────────────
 */
class LocalTestSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('🧪  LocalTestSeeder — creating test data...');
        $this->command->info('');

        // ── 1. Test patient ───────────────────────────────────────────────────
        $patientId = DB::table('users')->insertGetId([
            'first_name'          => 'Test',
            'last_name'           => 'Patient',
            'email'               => 'test@prescribeandco.test',
            'email_verified_at'   => now(),
            'password'            => Hash::make('Password1!'),
            'date_of_birth'       => '1990-05-15',
            'mobile'              => '07700900000',
            'address_line_1'      => '1 Test Street',
            'city'                => 'London',
            'postcode'            => 'SW1A 1AA',
            'country'             => 'GB',
            'terms_accepted'      => true,
            'terms_accepted_at'   => now(),
            'gdpr_consent_at'     => now(),
            'two_factor_enabled'  => false, // disabled so you can log straight in locally
            'stripe_id'           => 'cus_test_' . uniqid(),
            'acquisition_channel' => 'direct',
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);
        $this->command->info("  ✓ Patient: test@prescribeandco.test / Password1!");

        // ── 2. CRM staff ──────────────────────────────────────────────────────
        $staffAccounts = [
            ['email' => 'super@crm.test',        'role' => 'super_admin',                 'first' => 'Super',        'last' => 'Admin',   'gphc' => null],
            ['email' => 'super.pharm@crm.test',  'role' => 'superintendent_pharmacist',   'first' => 'Superintendent','last' => 'Pharm',  'gphc' => '1234567'],
            ['email' => 'prescriber@crm.test',   'role' => 'prescriber',                  'first' => 'Test',         'last' => 'Prescriber','gphc' => '7654321'],
            ['email' => 'dispenser@crm.test',    'role' => 'dispenser',                   'first' => 'Test',         'last' => 'Dispenser','gphc' => null],
            ['email' => 'support@crm.test',      'role' => 'customer_support',            'first' => 'Test',         'last' => 'Support', 'gphc' => null],
            ['email' => 'finance@crm.test',      'role' => 'finance',                     'first' => 'Test',         'last' => 'Finance', 'gphc' => null],
        ];

        $prescriberStaffId = null;
        foreach ($staffAccounts as $s) {
            $id = DB::table('staff')->insertGetId([
                'first_name'            => $s['first'],
                'last_name'             => $s['last'],
                'email'                 => $s['email'],
                'password'              => Hash::make('Password1!'),
                'role'                  => $s['role'],
                'gphc_number'           => $s['gphc'],
                'active'                => true,
                'two_factor_confirmed'  => false, // disabled for local testing
                'welcome_completed_at'  => now(),
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);
            if ($s['role'] === 'prescriber') $prescriberStaffId = $id;
            $this->command->info("  ✓ Staff ({$s['role']}): {$s['email']} / Password1!");
        }

        // ── 3. Add localhost to CRM IP whitelist ──────────────────────────────
        DB::table('ip_whitelist_entries')->insertOrIgnore([
            ['ip_address' => '127.0.0.1', 'label' => 'Localhost',   'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['ip_address' => '::1',       'label' => 'Localhost v6', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
        $this->command->info("  ✓ CRM IP whitelist: 127.0.0.1 and ::1 added");

        // ── 4. Grab a product to attach consultations/orders to ───────────────
        $product = DB::table('products')->where('active', true)->first();
        if (! $product) {
            $this->command->warn('  ⚠ No products found — run ProductSeeder first.');
            return;
        }

        // ── 5. Test consultation (awaiting review) ────────────────────────────
        $consultationId = DB::table('consultations')->insertGetId([
            'patient_id'    => $patientId,
            'product_id'    => $product->id,
            'status'        => 'awaiting_review',
            'answers'       => json_encode([
                ['question' => 'Do you have any known allergies?', 'answer' => 'No'],
                ['question' => 'Are you currently taking any medications?', 'answer' => 'No'],
                ['question' => 'What is your BMI?', 'answer' => '27.5'],
            ]),
            'submitted_at'  => now()->subHours(2),
            'created_at'    => now()->subHours(2),
            'updated_at'    => now()->subHours(2),
        ]);
        $this->command->info("  ✓ Consultation #{$consultationId} (awaiting_review) created");

        // ── 6. Approved consultation + signed prescription ────────────────────
        $approvedConsultId = DB::table('consultations')->insertGetId([
            'patient_id'    => $patientId,
            'product_id'    => $product->id,
            'status'        => 'approved',
            'prescriber_id' => $prescriberStaffId,
            'answers'       => json_encode([
                ['question' => 'Do you have any known allergies?', 'answer' => 'No'],
                ['question' => 'Are you currently taking any medications?', 'answer' => 'No'],
            ]),
            'submitted_at'  => now()->subDays(3),
            'reviewed_at'   => now()->subDays(2),
            'created_at'    => now()->subDays(3),
            'updated_at'    => now()->subDays(2),
        ]);

        $rxNumber = 'RX-' . now()->format('Ymd') . '-TEST1';
        $prescriptionId = DB::table('prescriptions')->insertGetId([
            'prescription_number'    => $rxNumber,
            'consultation_id'        => $approvedConsultId,
            'patient_id'             => $patientId,
            'prescriber_id'          => $prescriberStaffId,
            'prescriber_gphc_number' => '7654321',
            'prescriber_name'        => 'Test Prescriber',
            'product_id'             => $product->id,
            'status'                 => 'approved',
            'dosage_instructions'    => $product->dosage_instructions ?? 'As directed',
            'quantity'               => '1 x 4 weeks supply',
            'signed_at'              => now()->subDays(2),
            'created_at'             => now()->subDays(3),
            'updated_at'             => now()->subDays(2),
        ]);
        $this->command->info("  ✓ Prescription {$rxNumber} (approved) created");

        // ── 7. Test order (processing — ready to dispatch) ────────────────────
        $price = 149.00;
        $orderId = DB::table('orders')->insertGetId([
            'order_number'           => 'ORD-' . now()->format('Y') . '-00001',
            'user_id'                => $patientId,
            'prescription_id'        => $prescriptionId,
            'status'                 => 'processing',
            'subtotal'               => $price,
            'vat_amount'             => 0.00,
            'shipping_cost'          => 0.00,
            'total'                  => $price,
            'currency'               => 'GBP',
            'payment_method'         => 'card',
            'stripe_payment_intent_id' => 'pi_test_' . uniqid(),
            'stripe_charge_id'       => 'ch_test_' . uniqid(),
            'carrier'                => null,
            'requires_cold_chain'    => (bool) $product->requires_cold_chain,
            'delivery_name'          => 'Test Patient',
            'delivery_address_line_1' => '1 Test Street',
            'delivery_city'          => 'London',
            'delivery_postcode'      => 'SW1A 1AA',
            'delivery_country'       => 'GB',
            'created_at'             => now()->subDay(),
            'updated_at'             => now()->subDay(),
        ]);

        DB::table('order_items')->insert([
            'order_id'        => $orderId,
            'product_id'      => $product->id,
            'product_name'    => $product->name,
            'product_strength'=> $product->strength ?? '',
            'product_form'    => $product->form ?? '',
            'quantity'        => 1,
            'unit_price'      => $price,
            'line_total'      => $price,
            'vat_rate'        => 0.00,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
        $this->command->info("  ✓ Order ORD-{$orderId} (processing) created — ready to dispatch in CRM");

        // ── 8. Dispatched order (already sent) ───────────────────────────────
        $dispatchedOrderId = DB::table('orders')->insertGetId([
            'order_number'           => 'ORD-' . now()->format('Y') . '-00002',
            'user_id'                => $patientId,
            'prescription_id'        => null,
            'status'                 => 'dispatched',
            'subtotal'               => 79.99,
            'vat_amount'             => 0.00,
            'shipping_cost'          => 0.00,
            'total'                  => 79.99,
            'currency'               => 'GBP',
            'payment_method'         => 'card',
            'stripe_payment_intent_id' => 'pi_test_' . uniqid(),
            'stripe_charge_id'       => 'ch_test_' . uniqid(),
            'carrier'                => 'royal_mail',
            'tracking_number'        => 'AB123456789GB',
            'requires_cold_chain'    => false,
            'delivery_name'          => 'Test Patient',
            'delivery_address_line_1' => '1 Test Street',
            'delivery_city'          => 'London',
            'delivery_postcode'      => 'SW1A 1AA',
            'delivery_country'       => 'GB',
            'dispatched_at'          => now()->subHours(6),
            'created_at'             => now()->subDays(2),
            'updated_at'             => now()->subHours(6),
        ]);
        $this->command->info("  ✓ Order ORD-{$dispatchedOrderId} (dispatched, Royal Mail) created");

        // ── 9. Invoice for the dispatched order ───────────────────────────────
        DB::table('invoices')->insert([
            'invoice_number'    => 'INV-' . now()->format('Y') . '-00001',
            'user_id'           => $patientId,
            'order_id'          => $dispatchedOrderId,
            'subtotal'          => 79.99,
            'vat_amount'        => 0.00,
            'total'             => 79.99,
            'currency'          => 'GBP',
            'stripe_invoice_id' => 'in_test_' . uniqid(),
            'status'            => 'paid',
            'issued_at'         => now()->subDays(2),
            'paid_at'           => now()->subDays(2),
            'created_at'        => now()->subDays(2),
            'updated_at'        => now()->subDays(2),
        ]);
        $this->command->info("  ✓ Invoice INV-{$dispatchedOrderId} (paid) created");

        // ── 10. Stock batch for the product ──────────────────────────────────
        $supplierId = DB::table('suppliers')->insertGetId([
            'name'       => 'Test Supplier Ltd',
            'email'      => 'orders@testsupplier.test',
            'active'     => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('stock_batches')->insert([
            'product_id'          => $product->id,
            'supplier_id'         => $supplierId,
            'batch_number'        => 'BATCH-TEST-001',
            'expiry_date'         => now()->addYear()->format('Y-m-d'),
            'received_date'       => now()->subWeek()->format('Y-m-d'),
            'quantity_received'   => 100,
            'quantity_remaining'  => 97, // 3 used in orders above
            'unit_cost'           => 85.00,
            'cold_chain_maintained' => true,
            'status'              => 'active',
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        DB::table('stock')->updateOrInsert(
            ['product_id' => $product->id],
            ['quantity_on_hand' => 97, 'minimum_threshold' => 10, 'updated_at' => now()]
        );
        $this->command->info("  ✓ Stock batch (100 units, expires in 1 year) created");

        // ── 11. Test message ──────────────────────────────────────────────────
        DB::table('messages')->insert([
            'consultation_id' => $approvedConsultId,
            'user_id'         => $patientId,
            'sender_type'     => 'patient',
            'sender_id'       => $patientId,
            'body'            => 'Hi, when will my order be dispatched?',
            'internal_only'   => false,
            'created_at'      => now()->subHour(),
            'updated_at'      => now()->subHour(),
        ]);
        $this->command->info("  ✓ Test message (unread) from patient created");

        // Summary
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info('  Test data created. Login credentials:');
        $this->command->info('');
        $this->command->info('  WEBSITE  → test@prescribeandco.test / Password1!');
        $this->command->info('  CRM Admin→ super@crm.test / Password1!');
        $this->command->info('  CRM Rx   → prescriber@crm.test / Password1!');
        $this->command->info('  CRM Disp → dispenser@crm.test / Password1!');
        $this->command->info('');
        $this->command->info('  NOTE: 2FA is DISABLED on all test accounts.');
        $this->command->info('  NOTE: CRM IP whitelist allows 127.0.0.1 + ::1.');
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info('');
    }
}
