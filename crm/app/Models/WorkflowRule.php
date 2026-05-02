<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowRule extends Model
{
    protected $table = 'workflow_rules';

    protected $fillable = [
        'name',
        'description',
        'trigger_event',
        'conditions',
        'actions',
        'active',
        'is_system',
        'created_by',
        'updated_by',
        'sort_order',
    ];

    protected $casts = [
        'conditions' => 'array',
        'actions'    => 'array',
        'active'     => 'boolean',
        'is_system'  => 'boolean',
    ];

    // ── All supported trigger events ──────────────────────────────────────────

    const TRIGGERS = [
        'consultation.approved'     => 'Consultation Approved',
        'consultation.rejected'     => 'Consultation Rejected',
        'consultation.flagged'      => 'Consultation Flagged',
        'consultation.submitted'    => 'Consultation Submitted',
        'prescription.signed'       => 'Prescription Signed',
        'prescription.dispensed'    => 'Prescription Dispensed',
        'order.dispatched'          => 'Order Dispatched',
        'order.failed_delivery'     => 'Order Failed Delivery',
        'order.returned'            => 'Order Returned',
        'subscription.renewal_due'  => 'Subscription Renewal Due (7 days)',
        'stock.below_threshold'     => 'Stock Below Minimum Threshold',
        'cold_chain.dispatch_overdue' => 'Cold Chain Order Not Dispatched In Time',
    ];

    // ── All supported condition field paths ────────────────────────────────────

    const CONDITION_FIELDS = [
        'consultation.product.treatment.category.name' => 'Treatment Category',
        'consultation.patient.risk_flagged'            => 'Patient Risk Flagged',
        'order.requires_cold_chain'                    => 'Order Requires Cold Chain',
        'order.carrier'                                => 'Order Carrier',
        'stock.quantity_on_hand'                       => 'Stock Quantity',
        'patient.do_not_treat'                         => 'Patient Do Not Treat',
    ];

    const CONDITION_OPERATORS = ['equals', 'not_equals', 'greater_than', 'less_than', 'contains', 'is_true', 'is_false'];

    // ── All supported action types ─────────────────────────────────────────────

    const ACTION_TYPES = [
        'send_email'           => 'Send Email to Patient',
        'notify_staff'         => 'Send In-App Notification to Staff Role',
        'advance_prescription' => 'Advance Prescription to Dispensing Queue',
        'create_staff_notification' => 'Create Staff Notification',
        'log_audit'            => 'Write Audit Log Entry',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function creator()
    {
        return $this->belongsTo(Staff::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(Staff::class, 'updated_by');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeForTrigger($query, string $event)
    {
        return $query->where('trigger_event', $event)->where('active', true)->orderBy('sort_order');
    }
}
