<?php

namespace Database\Seeders;

use App\Models\WorkflowRule;
use Illuminate\Database\Seeder;

class WorkflowRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            [
                'name'          => 'Consultation Approved → Auto-advance to Dispensing Queue',
                'description'   => 'When a consultation is approved and a prescription exists, automatically moves it to the dispensing queue.',
                'trigger_event' => 'consultation.approved',
                'conditions'    => [],
                'actions'       => [
                    ['type' => 'advance_prescription', 'config' => []],
                    ['type' => 'send_email', 'config' => ['template' => 'prescription_ready']],
                ],
                'is_system'  => true,
                'active'     => true,
                'sort_order' => 10,
            ],
            [
                'name'          => 'Order Dispatched → Send Tracking Email',
                'description'   => 'Sends a dispatch confirmation email with tracking link to the patient.',
                'trigger_event' => 'order.dispatched',
                'conditions'    => [],
                'actions'       => [
                    ['type' => 'send_email', 'config' => ['template' => 'order_dispatched']],
                ],
                'is_system'  => true,
                'active'     => true,
                'sort_order' => 20,
            ],
            [
                'name'          => 'Subscription Renewal Due → 7-day Reminder',
                'description'   => 'Sends a renewal reminder email to patients 7 days before their subscription renews.',
                'trigger_event' => 'subscription.renewal_due',
                'conditions'    => [],
                'actions'       => [
                    ['type' => 'send_email', 'config' => ['template' => 'renewal_reminder']],
                ],
                'is_system'  => true,
                'active'     => true,
                'sort_order' => 30,
            ],
            [
                'name'          => 'Consultation Flagged → Notify Superintendent Pharmacist',
                'description'   => 'Sends an in-app notification to the superintendent pharmacist when a consultation is flagged.',
                'trigger_event' => 'consultation.flagged',
                'conditions'    => [],
                'actions'       => [
                    ['type' => 'notify_staff', 'config' => [
                        'roles'   => ['superintendent_pharmacist', 'super_admin'],
                        'message' => 'A consultation has been flagged and requires your review.',
                    ]],
                ],
                'is_system'  => true,
                'active'     => true,
                'sort_order' => 40,
            ],
            [
                'name'          => 'Stock Below Minimum Threshold → Alert Staff',
                'description'   => 'Notifies superintendent pharmacist and super admin when any product stock falls to or below its minimum threshold.',
                'trigger_event' => 'stock.below_threshold',
                'conditions'    => [],
                'actions'       => [
                    ['type' => 'notify_staff', 'config' => [
                        'roles'   => ['super_admin', 'superintendent_pharmacist'],
                        'message' => 'Stock alert: a product has fallen below its minimum threshold.',
                    ]],
                    ['type' => 'log_audit', 'config' => []],
                ],
                'is_system'  => true,
                'active'     => true,
                'sort_order' => 50,
            ],
            [
                'name'          => 'Cold Chain Order Not Dispatched → Escalation',
                'description'   => 'Raises an alert if a cold chain order has been in "processing" status for more than 4 hours.',
                'trigger_event' => 'cold_chain.dispatch_overdue',
                'conditions'    => [
                    ['field' => 'order.requires_cold_chain', 'operator' => 'is_true', 'value' => null],
                ],
                'actions'       => [
                    ['type' => 'notify_staff', 'config' => [
                        'roles'   => ['super_admin', 'superintendent_pharmacist'],
                        'message' => 'Cold chain order has not been dispatched within the required time window.',
                    ]],
                    ['type' => 'log_audit', 'config' => []],
                ],
                'is_system'  => true,
                'active'     => true,
                'sort_order' => 60,
            ],
            [
                'name'          => 'Consultation Rejected → Send Rejection Email',
                'description'   => 'Sends a rejection notification email to the patient when a consultation is rejected.',
                'trigger_event' => 'consultation.rejected',
                'conditions'    => [],
                'actions'       => [
                    ['type' => 'send_email', 'config' => ['template' => 'consultation_rejected']],
                ],
                'is_system'  => true,
                'active'     => true,
                'sort_order' => 70,
            ],
        ];

        foreach ($rules as $rule) {
            WorkflowRule::firstOrCreate(
                ['trigger_event' => $rule['trigger_event'], 'name' => $rule['name']],
                $rule
            );
        }

        $this->command->info('Workflow rules seeded (' . count($rules) . ' system rules).');
    }
}
