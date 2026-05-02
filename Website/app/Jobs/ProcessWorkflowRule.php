<?php

namespace App\Jobs;

use App\Models\WorkflowRule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessWorkflowRule implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function __construct(
        public string $triggerEvent,
        public array  $context = []
    ) {}

    public function handle(): void
    {
        $rules = WorkflowRule::where('trigger_event', $this->triggerEvent)
            ->where('active', true)
            ->orderBy('sort_order')
            ->get();

        if ($rules->isEmpty()) {
            return;
        }

        foreach ($rules as $rule) {
            try {
                $this->executeRule($rule);
            } catch (\Throwable $e) {
                Log::error("ProcessWorkflowRule: rule [{$rule->id}] failed", [
                    'event' => $this->triggerEvent,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function executeRule(WorkflowRule $rule): void
    {
        // Evaluate conditions (AND logic)
        foreach ($rule->conditions ?? [] as $condition) {
            $field    = $condition['field']    ?? '';
            $operator = $condition['operator'] ?? 'equals';
            $value    = $condition['value']    ?? null;
            $actual   = data_get($this->context, $field);

            $pass = match($operator) {
                'equals'       => $actual == $value,
                'not_equals'   => $actual != $value,
                'greater_than' => is_numeric($actual) && $actual > $value,
                'less_than'    => is_numeric($actual) && $actual < $value,
                'is_true'      => (bool) $actual === true,
                'is_false'     => (bool) $actual === false,
                default        => false,
            };

            if (! $pass) return; // condition failed — skip rule
        }

        // Execute actions
        foreach ($rule->actions as $action) {
            match($action['type'] ?? '') {
                'send_email'       => $this->actionSendEmail($action['config'] ?? []),
                'notify_staff'     => $this->actionNotifyStaff($action['config'] ?? [], $rule),
                'log_audit'        => Log::info("Workflow rule [{$rule->name}] fired", $this->context),
                default            => null,
            };
        }
    }

    private function actionSendEmail(array $config): void
    {
        $template  = $config['template'] ?? null;
        $patientId = data_get($this->context, 'patient_id')
            ?? data_get($this->context, 'user_id');

        if (! $template || ! $patientId) return;

        $patient = \App\Models\User::find($patientId);
        if (! $patient?->email) return;

        $mailable = match($template) {
            'renewal_reminder'      => new \App\Mail\RenewalReminder($patient, null),
            'consultation_approved' => new \App\Mail\PrescriptionApproved($patient),
            'consultation_rejected' => new \App\Mail\ConsultationRejectedMail($patient),
            'order_dispatched'      => new \App\Mail\OrderDispatched($patient),
            default                 => null,
        };

        if ($mailable) {
            \Illuminate\Support\Facades\Mail::to($patient->email)->queue($mailable);
        }
    }

    private function actionNotifyStaff(array $config, WorkflowRule $rule): void
    {
        $roles   = (array) ($config['roles']   ?? ['superintendent_pharmacist']);
        $message = $config['message'] ?? "Workflow rule '{$rule->name}' triggered.";

        \App\Models\Staff::whereIn('role', $roles)
            ->where('active', true)
            ->each(function ($staff) use ($message, $rule) {
                \App\Models\StaffNotification::create([
                    'staff_id' => $staff->id,
                    'type'     => 'workflow',
                    'message'  => $message,
                ]);
            });
    }
}
