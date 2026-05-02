<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkflowRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            [
                'name'          => 'Consultation Approved → Move to Dispensing Queue',
                'description'   => 'When a prescription is approved, automatically mark it as sent to dispense.',
                'trigger_event' => 'prescription.approved',
                'conditions'    => null,
                'actions'       => json_encode([
                    ['type' => 'update_prescription_status', 'config' => ['status' => 'sent_to_dispense']],
                    ['type' => 'notify_staff_role', 'config' => ['role' => 'dispenser', 'message' => 'New prescription ready for dispensing.']],
                ]),
                'is_system'     => true,
                'active'        => true,
                'sort_order'    => 1,
            ],
            [
                'name'          => 'Order Dispatched → Send Tracking Email to Patient',
                'description'   => 'When an order is marked as dispatched, send the patient a tracking email.',
                'trigger_event' => 'order.dispatched',
                'conditions'    => null,
                'actions'       => json_encode([
                    ['type' => 'send_email', 'config' => ['template' => 'order_dispatched', 'recipient' => 'patient']],
                ]),
                'is_system'     => true,
                'active'        => true,
                'sort_order'    => 2,
            ],
            [
                'name'          => 'Subscription Renewal Due in 7 Days → Renewal Reminder',
                'description'   => 'Send the patient a renewal reminder 7 days before subscription renews.',
                'trigger_event' => 'subscription.renewal_due_7_days',
                'conditions'    => null,
                'actions'       => json_encode([
                    ['type' => 'send_email', 'config' => ['template' => 'renewal_reminder', 'recipient' => 'patient']],
                ]),
                'is_system'     => true,
                'active'        => true,
                'sort_order'    => 3,
            ],
            [
                'name'          => 'Questionnaire Contraindication Flagged → Notify Superintendent',
                'description'   => 'When a consultation is auto-flagged for contraindication, alert the superintendent pharmacist.',
                'trigger_event' => 'consultation.contraindication_flagged',
                'conditions'    => null,
                'actions'       => json_encode([
                    ['type' => 'notify_staff_role', 'config' => ['role' => 'superintendent_pharmacist', 'severity' => 'critical', 'message' => 'Consultation flagged for contraindication — immediate review required.']],
                ]),
                'is_system'     => true,
                'active'        => true,
                'sort_order'    => 4,
            ],
            [
                'name'          => 'Stock Below Minimum Threshold → Alert Staff',
                'description'   => 'When a product stock level falls below its minimum threshold, alert relevant staff.',
                'trigger_event' => 'stock.below_minimum',
                'conditions'    => null,
                'actions'       => json_encode([
                    ['type' => 'notify_staff_role', 'config' => ['role' => 'super_admin', 'severity' => 'warning', 'message' => 'Stock level is below minimum threshold.']],
                    ['type' => 'notify_staff_role', 'config' => ['role' => 'superintendent_pharmacist', 'severity' => 'warning', 'message' => 'Stock level is below minimum threshold.']],
                ]),
                'is_system'     => true,
                'active'        => true,
                'sort_order'    => 5,
            ],
            [
                'name'          => 'Cold Chain Order Not Dispatched Within 24 Hours → Escalation',
                'description'   => 'If a cold chain order has not been dispatched within 24 hours of being packed, escalate.',
                'trigger_event' => 'order.cold_chain_dispatch_overdue',
                'conditions'    => null,
                'actions'       => json_encode([
                    ['type' => 'notify_staff_role', 'config' => ['role' => 'super_admin', 'severity' => 'critical', 'message' => 'Cold chain order has not been dispatched — immediate action required.']],
                    ['type' => 'notify_staff_role', 'config' => ['role' => 'superintendent_pharmacist', 'severity' => 'critical', 'message' => 'Cold chain order overdue for dispatch.']],
                ]),
                'is_system'     => true,
                'active'        => true,
                'sort_order'    => 6,
            ],
            [
                'name'          => 'Consultation Waiting Over 4 Hours → Escalation Alert',
                'description'   => 'If a submitted consultation has not been reviewed after 4 hours, alert prescribers.',
                'trigger_event' => 'consultation.waiting_too_long',
                'conditions'    => json_encode([
                    ['field' => 'waiting_hours', 'operator' => '>=', 'value' => 4],
                ]),
                'actions'       => json_encode([
                    ['type' => 'notify_staff_role', 'config' => ['role' => 'prescriber', 'severity' => 'warning', 'message' => 'Consultation has been waiting for review for over 4 hours.']],
                    ['type' => 'notify_staff_role', 'config' => ['role' => 'superintendent_pharmacist', 'severity' => 'warning', 'message' => 'Consultation queue alert: consultation waiting over 4 hours.']],
                ]),
                'is_system'     => true,
                'active'        => true,
                'sort_order'    => 7,
            ],
            [
                'name'          => 'Failed Subscription Payment → Dunning Email',
                'description'   => 'When a subscription payment fails, send the patient a payment failure notification.',
                'trigger_event' => 'subscription.payment_failed',
                'conditions'    => null,
                'actions'       => json_encode([
                    ['type' => 'send_email', 'config' => ['template' => 'payment_failed', 'recipient' => 'patient']],
                ]),
                'is_system'     => true,
                'active'        => true,
                'sort_order'    => 8,
            ],
        ];

        foreach ($rules as $rule) {
            DB::table('workflow_rules')->updateOrInsert(
                ['name' => $rule['name']],
                array_merge($rule, [
                    'created_by' => null,
                    'updated_by' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
