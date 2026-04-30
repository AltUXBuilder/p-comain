<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Prescription extends Model
{
    use SoftDeletes;

    protected $table = 'prescriptions';

    protected $fillable = [
        'prescription_number',
        'consultation_id',
        'patient_id',
        'prescriber_id',
        'prescriber_gphc_number',
        'prescriber_name',
        'product_id',
        'status',
        'dosage_instructions',
        'quantity',
        'legal_wording',
        'is_repeat',
        'repeat_interval_days',
        'repeat_parent_id',
        'next_repeat_due',
        'signed_at',
        'pdf_path',
        'signature_override_path',
        'sent_to_dispense_at',
        'dispensed_at',
        'archived_at',
        'prescriber_notes',
    ];

    protected $casts = [
        'signed_at'            => 'datetime',
        'sent_to_dispense_at'  => 'datetime',
        'dispensed_at'         => 'datetime',
        'archived_at'          => 'datetime',
        'next_repeat_due'      => 'date',
        'is_repeat'            => 'boolean',
    ];

    // ── Status constants ─────────────────────────────────────────────────────

    const STATUS_DRAFT            = 'draft';
    const STATUS_PENDING_REVIEW   = 'pending_review';
    const STATUS_APPROVED         = 'approved';
    const STATUS_SENT_TO_DISPENSE = 'sent_to_dispense';
    const STATUS_DISPENSED        = 'dispensed';
    const STATUS_ARCHIVED         = 'archived';

    const STATUSES = [
        self::STATUS_DRAFT            => 'Draft',
        self::STATUS_PENDING_REVIEW   => 'Pending Review',
        self::STATUS_APPROVED         => 'Approved',
        self::STATUS_SENT_TO_DISPENSE => 'Sent to Dispense',
        self::STATUS_DISPENSED        => 'Dispensed',
        self::STATUS_ARCHIVED         => 'Archived',
    ];

    // Valid forward transitions
    const TRANSITIONS = [
        self::STATUS_DRAFT            => [self::STATUS_PENDING_REVIEW],
        self::STATUS_PENDING_REVIEW   => [self::STATUS_APPROVED, self::STATUS_DRAFT],
        self::STATUS_APPROVED         => [self::STATUS_SENT_TO_DISPENSE, self::STATUS_ARCHIVED],
        self::STATUS_SENT_TO_DISPENSE => [self::STATUS_DISPENSED],
        self::STATUS_DISPENSED        => [self::STATUS_ARCHIVED],
        self::STATUS_ARCHIVED         => [],
    ];

    // Default GPhC legal wording for private prescriptions
    const DEFAULT_LEGAL_WORDING = 'This is a private prescription issued in accordance with the Medicines Act 1968. '
        . 'It is valid for 28 days from the date of issue. '
        . 'Prescribe & Co is a GPhC-registered online pharmacy providing services in accordance with GPhC standards for registered pharmacies.';

    // ── Boot ─────────────────────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Prescription $rx) {
            if (empty($rx->prescription_number)) {
                $rx->prescription_number = self::generateNumber();
            }
            if (empty($rx->legal_wording)) {
                $rx->legal_wording = self::DEFAULT_LEGAL_WORDING;
            }
            // Snapshot the prescriber's GPhC and name at time of creation
            if ($rx->prescriber_id && ! $rx->prescriber_gphc_number) {
                $prescriber = Staff::find($rx->prescriber_id);
                if ($prescriber) {
                    $rx->prescriber_gphc_number = $prescriber->gphc_number;
                    $rx->prescriber_name        = $prescriber->full_name;
                }
            }
        });
    }

    public static function generateNumber(): string
    {
        // Format: RX-YYYYMMDD-XXXXX  e.g. RX-20240115-A3F72
        return 'RX-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));
    }

    // ── Status helpers ────────────────────────────────────────────────────────

    public function canTransitionTo(string $status): bool
    {
        return in_array($status, self::TRANSITIONS[$this->status] ?? []);
    }

    public function transitionTo(string $newStatus, ?Staff $actor = null): void
    {
        if (! $this->canTransitionTo($newStatus)) {
            throw new \RuntimeException("Cannot transition prescription from {$this->status} to {$newStatus}.");
        }

        $timestamps = [
            self::STATUS_SENT_TO_DISPENSE => 'sent_to_dispense_at',
            self::STATUS_DISPENSED        => 'dispensed_at',
            self::STATUS_ARCHIVED         => 'archived_at',
            self::STATUS_APPROVED         => 'signed_at',
        ];

        $updates = ['status' => $newStatus];

        if (isset($timestamps[$newStatus]) && is_null($this->{$timestamps[$newStatus]})) {
            $updates[$timestamps[$newStatus]] = now();
        }

        $this->update($updates);

        if ($actor) {
            AuditLog::record(
                staffId:    $actor->id,
                action:     "prescription_{$newStatus}",
                entityType: 'prescription',
                entityId:   $this->id,
                metadata:   [
                    'prescription_number' => $this->prescription_number,
                    'gphc_number'         => $actor->gphc_number,
                ],
                request: request()
            );
        }
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function statusColour(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT            => 'bg-plum-100 text-plum-600',
            self::STATUS_PENDING_REVIEW   => 'bg-amber-100 text-amber-700',
            self::STATUS_APPROVED         => 'bg-green-100 text-green-700',
            self::STATUS_SENT_TO_DISPENSE => 'bg-blue-100 text-blue-700',
            self::STATUS_DISPENSED        => 'bg-teal-100 text-teal-700',
            self::STATUS_ARCHIVED         => 'bg-gray-100 text-gray-500',
            default                       => 'bg-plum-100 text-plum-600',
        };
    }

    // ── Signature ─────────────────────────────────────────────────────────────

    /**
     * Returns the effective signature path for this prescription:
     * override path if set, otherwise the prescriber's saved signature.
     */
    public function effectiveSignaturePath(): ?string
    {
        if ($this->signature_override_path) {
            return $this->signature_override_path;
        }
        return $this->prescriber?->signature_path;
    }

    public function hasSignature(): bool
    {
        $path = $this->effectiveSignaturePath();
        return $path && \Illuminate\Support\Facades\Storage::disk('private')->exists($path);
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function prescriber()
    {
        return $this->belongsTo(Staff::class, 'prescriber_id');
    }

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function repeatParent()
    {
        return $this->belongsTo(Prescription::class, 'repeat_parent_id');
    }

    public function repeatChildren()
    {
        return $this->hasMany(Prescription::class, 'repeat_parent_id');
    }
}
