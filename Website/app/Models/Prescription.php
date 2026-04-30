<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prescription extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'prescription_number',
        'consultation_id', 'user_id', 'product_id', 'prescriber_id',
        'prescriber_name', 'prescriber_gphc_number', 'prescriber_role',
        'quantity', 'dosage_instructions',
        'valid_from', 'valid_until',
        'status',
        'signature_path', 'signature_overridden', 'signed_at',
        'pdf_path', 'pdf_generated_at',
        'is_repeat', 'original_prescription_id', 'next_repeat_due', 'repeat_interval_days',
        'pharmacy_name', 'pharmacy_address', 'pharmacy_gphc_number',
        'approved_by_name', 'approved_by_gphc', 'approved_by_ip', 'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'valid_from'           => 'date',
            'valid_until'          => 'date',
            'signed_at'            => 'datetime',
            'pdf_generated_at'     => 'datetime',
            'approved_at'          => 'datetime',
            'next_repeat_due'      => 'date',
            'is_repeat'            => 'boolean',
            'signature_overridden' => 'boolean',
        ];
    }

    // ── Status helpers ─────────────────────────────────────────────────────

    public function isApproved(): bool    { return $this->status === 'approved'; }
    public function isDispensed(): bool   { return $this->status === 'dispensed'; }
    public function isExpired(): bool     { return $this->valid_until && $this->valid_until->isPast(); }

    // ── Relations ──────────────────────────────────────────────────────────

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function prescriber(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'prescriber_id');
    }

    public function dispensingLabel(): HasOne
    {
        return $this->hasOne(DispensingLabel::class);
    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }

    public function originalPrescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class, 'original_prescription_id');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopePendingDispense($query)
    {
        return $query->where('status', 'sent_to_dispense');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
