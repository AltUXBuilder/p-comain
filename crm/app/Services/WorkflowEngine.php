<?php

namespace App\Services;

use App\Models\WorkflowRule;
use Illuminate\Support\Facades\Log;

class WorkflowEngine
{
    /**
     * Fire all active rules for a given trigger event.
     *
     * Usage:
     *   app(WorkflowEngine::class)->fire('consultation.approved', $consultation);
     *
     * The $context is the primary model (e.g. a Consultation, Order, Stock).
     * Additional context data can be passed in $extra.
     */
    public function fire(string $event, mixed $context, array $extra = []): void
    {
        $rules = WorkflowRule::forTrigger($event)->get();

        foreach ($rules as $rule) {
            try {
                if ($this->evaluateConditions($rule->conditions ?? [], $context, $extra)) {
                    $this->executeActions($rule->actions, $context, $extra, $rule);
                }
            } catch (\Throwable $e) {
                Log::error("WorkflowEngine: rule [{$rule->id}] {$rule->name} failed", [
                    'event'   => $event,
                    'rule_id' => $rule->id,
                    'error'   => $e->getMessage(),
                ]);
            }
        }
    }

    // ── Condition evaluation ──────────────────────────────────────────────────

    /**
     * All conditions must pass (AND logic).
     * Each condition: { field: string, operator: string, value: mixed }
     */
    private function evaluateConditions(array $conditions, mixed $context, array $extra): bool
    {
        foreach ($conditions as $condition) {
            if (! $this->evaluateCondition($condition, $context, $extra)) {
                return false;
            }
        }
        return true;
    }

    private function evaluateCondition(array $condition, mixed $context, array $extra): bool
    {
        $field    = $condition['field']    ?? '';
        $operator = $condition['operator'] ?? 'equals';
        $value    = $condition['value']    ?? null;

        $actual = $this->resolveField($field, $context, $extra);

        return match($operator) {
            'equals'        => $actual == $value,
            'not_equals'    => $actual != $value,
            'greater_than'  => is_numeric($actual) && $actual > $value,
            'less_than'     => is_numeric($actual) && $actual < $value,
            'contains'      => is_string($actual) && str_contains($actual, (string) $value),
            'is_true'       => (bool) $actual === true,
            'is_false'      => (bool) $actual === false,
            default         => false,
        };
    }

    /**
     * Resolve a dot-notation field path from the context model or extra data.
     * e.g. "consultation.patient.risk_flagged" → $context->patient->risk_flagged
     */
    private function resolveField(string $field, mixed $context, array $extra): mixed
    {
        // Try extra array first
        if (array_key_exists($field, $extra)) {
            return $extra[$field];
        }

        // Walk dot-notation on the model
        $parts  = explode('.', $field);
        $cursor = $context;

        // Skip leading model name if it matches (e.g. "consultation.patient" → "patient")
        if (is_object($cursor) && class_basename($cursor) === ucfirst($parts[0])) {
            array_shift($parts);
        }

        foreach ($parts as $part) {
            if (is_object($cursor)) {
                $cursor = $cursor->{$part} ?? null;
            } elseif (is_array($cursor)) {
                $cursor = $cursor[$part] ?? null;
            } else {
                return null;
            }
        }

        return $cursor;
    }

    // ── Action execution ──────────────────────────────────────────────────────

    private function executeActions(array $actions, mixed $context, array $extra, WorkflowRule $rule): void
    {
        foreach ($actions as $action) {
            $type   = $action['type']   ?? '';
            $config = $action['config'] ?? [];

            match($type) {
                'send_email'                => $this->actionSendEmail($config, $context),
                'notify_staff'              => $this->actionNotifyStaff($config, $context, $rule),
                'advance_prescription'      => $this->actionAdvancePrescription($context),
                'create_staff_notification' => $this->actionCreateStaffNotification($config, $context, $rule),
                'log_audit'                 => $this->actionLogAudit($config, $context, $rule),
                default                     => Log::warning("WorkflowEngine: unknown action type [{$type}]"),
            };
        }
    }

    private function actionSendEmail(array $config, mixed $context): void
    {
        $templateSlug = $config['template'] ?? null;
        if (! $templateSlug) return;

        $patient = $this->resolvePatient($context);
        if (! $patient?->email) return;

        $mailable = match($templateSlug) {
            'consultation_approved' => new \App\Mail\ConsultationApprovedMail($context),
            'consultation_rejected' => new \App\Mail\ConsultationRejectedMail($context),
            'prescription_ready'    => new \App\Mail\PrescriptionReadyMail($context),
            'order_dispatched'      => new \App\Mail\OrderDispatchedMail($context),
            'renewal_reminder'      => new \App\Mail\RenewalReminderMail($context),
            default                 => null,
        };

        if ($mailable) {
            \Illuminate\Support\Facades\Mail::to($patient->email)->queue($mailable);
        }
    }

    private function actionNotifyStaff(array $config, mixed $context, WorkflowRule $rule): void
    {
        $roles   = $config['roles']   ?? ['superintendent_pharmacist'];
        $message = $config['message'] ?? "Workflow rule '{$rule->name}' triggered.";

        $staff = \App\Models\Staff::whereIn('role', (array) $roles)
            ->where('active', true)
            ->get();

        foreach ($staff as $member) {
            \App\Models\StaffNotification::create([
                'staff_id'    => $member->id,
                'type'        => 'workflow',
                'message'     => $message,
                'entity_type' => class_basename($context),
                'entity_id'   => $context?->id,
            ]);
        }
    }

    private function actionAdvancePrescription(mixed $context): void
    {
        // Context should be a Consultation model
        if (! $context instanceof \App\Models\Consultation) return;

        $rx = \App\Models\Prescription::where('consultation_id', $context->id)
            ->where('status', \App\Models\Prescription::STATUS_APPROVED)
            ->first();

        if ($rx && $rx->canTransitionTo(\App\Models\Prescription::STATUS_SENT_TO_DISPENSE)) {
            $rx->transitionTo(\App\Models\Prescription::STATUS_SENT_TO_DISPENSE);
        }
    }

    private function actionCreateStaffNotification(array $config, mixed $context, WorkflowRule $rule): void
    {
        $this->actionNotifyStaff($config, $context, $rule);
    }

    private function actionLogAudit(array $config, mixed $context, WorkflowRule $rule): void
    {
        \App\Models\AuditLog::record(
            staffId:    null,
            action:     "workflow.{$rule->trigger_event}",
            entityType: class_basename($context),
            entityId:   $context?->id,
            metadata:   ['rule_id' => $rule->id, 'rule_name' => $rule->name],
            request:    request()
        );
    }

    private function resolvePatient(mixed $context): ?\App\Models\Patient
    {
        if ($context instanceof \App\Models\Patient)      return $context;
        if (isset($context->patient))                     return $context->patient;
        if (isset($context->patient_id)) {
            return \App\Models\Patient::find($context->patient_id);
        }
        return null;
    }
}
