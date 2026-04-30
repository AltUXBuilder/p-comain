<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Staff;
use Illuminate\Http\Request;

class AuditService
{
    /**
     * Log an action to the append-only audit trail.
     * Always call this for any clinically significant action.
     */
    public function log(
        ?Staff $staff,
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        array $context = [],
        ?string $ip = null,
        ?string $userAgent = null
    ): AuditLog {
        return AuditLog::create([
            'staff_id'           => $staff?->id,
            'staff_name'         => $staff ? $staff->full_name : 'System',
            'staff_role'         => $staff?->role,
            'staff_gphc_number'  => $staff?->gphc_number,
            'action'             => $action,
            'entity_type'        => $entityType,
            'entity_id'          => $entityId,
            'context'            => $context ?: null,
            'ip_address'         => $ip,
            'user_agent'         => $userAgent,
            'created_at'         => now(),
        ]);
    }

    /**
     * Log from a request object (most common usage in controllers).
     */
    public function logFromRequest(
        Request $request,
        ?Staff $staff,
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        array $context = []
    ): AuditLog {
        return $this->log(
            $staff,
            $action,
            $entityType,
            $entityId,
            $context,
            $request->ip(),
            $request->userAgent()
        );
    }

    // ── Prescription-specific helpers ─────────────────────────────────────

    public function logPrescriptionApproved(Staff $staff, int $prescriptionId, Request $request): AuditLog
    {
        return $this->logFromRequest($request, $staff, 'prescription.approved', 'Prescription', $prescriptionId, [
            'gphc_number' => $staff->gphc_number,
            'role'        => $staff->role,
        ]);
    }

    public function logPrescriptionRejected(Staff $staff, int $consultationId, string $reason, Request $request): AuditLog
    {
        return $this->logFromRequest($request, $staff, 'prescription.rejected', 'Consultation', $consultationId, [
            'gphc_number' => $staff->gphc_number,
            'reason'      => $reason,
        ]);
    }

    public function logPrescriptionSigned(Staff $staff, int $prescriptionId, bool $overridden, Request $request): AuditLog
    {
        return $this->logFromRequest($request, $staff, 'prescription.signed', 'Prescription', $prescriptionId, [
            'gphc_number'          => $staff->gphc_number,
            'signature_overridden' => $overridden,
        ]);
    }

    public function logSignatureSaved(Staff $staff, string $type, Request $request): AuditLog
    {
        // type: 'initial' | 'override' | 'updated'
        return $this->logFromRequest($request, $staff, "signature.{$type}", 'Staff', $staff->id);
    }

    public function logDispensingSignOff(Staff $staff, int $labelId, Request $request): AuditLog
    {
        return $this->logFromRequest($request, $staff, 'dispensing.signed_off', 'DispensingLabel', $labelId);
    }
}
