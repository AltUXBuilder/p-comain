<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplianceLog extends Model
{
    protected $table    = 'compliance_logs';
    public    $timestamps = false;

    protected $fillable = [
        'type',
        'user_id',
        'staff_id',
        'notes',
        'meta',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'meta'       => 'array',
        'created_at' => 'datetime',
    ];

    // Prevent updates/deletes on compliance records (append-only)
    protected static function boot(): void
    {
        parent::boot();
        static::updating(fn () => false);
        static::deleting(fn () => false);
    }

    const TYPES = [
        'gdpr_consent'            => 'GDPR Consent Given',
        'gdpr_consent_withdrawn'  => 'GDPR Consent Withdrawn',
        'age_verification'        => 'Age Verification',
        'identity_verification'   => 'Identity Verification',
        'data_breach_incident'    => 'Data Breach Incident',
        'dsar_request'            => 'DSAR Request',
        'right_to_erasure'        => 'Right to Erasure Request',
        'mhra_yellow_card'        => 'MHRA Yellow Card Report',
        'superintendent_sign_off' => 'Superintendent Sign-off',
        'gphc_inspection'         => 'GPhC Inspection Record',
    ];

    const TYPES_GROUPED = [
        'Patient' => ['gdpr_consent', 'gdpr_consent_withdrawn', 'age_verification', 'identity_verification', 'dsar_request', 'right_to_erasure'],
        'Incidents' => ['data_breach_incident', 'mhra_yellow_card'],
        'Governance' => ['superintendent_sign_off', 'gphc_inspection'],
    ];

    public static function record(
        string  $type,
        ?int    $userId   = null,
        ?int    $staffId  = null,
        ?string $notes    = null,
        array   $meta     = [],
        ?string $ip       = null
    ): self {
        return static::create([
            'type'       => $type,
            'user_id'    => $userId,
            'staff_id'   => $staffId,
            'notes'      => $notes,
            'meta'       => $meta,
            'ip_address' => $ip ?? request()->ip(),
            'created_at' => now(),
        ]);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'user_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }
}
