<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DispensingLabel extends Model
{
    protected $table = 'dispensing_labels';

    protected $fillable = [
        'prescription_id',
        'product_id',
        'dispensed_by',
        'patient_id',
        'batch_number',
        'expiry_date',
        'stock_batch_id',
        'patient_name',
        'medication_name',
        'medication_strength',
        'medication_form',
        'dosage_instructions',
        'dispensing_date',
        'pharmacy_name',
        'pharmacy_address',
        'pharmacy_gphc_number',
        'dispensed_by_name',
        'dispensed_by_gphc',
        'cold_chain',
        'printed',
        'printed_at',
        'format',
        'pdf_path',
        'dispensed_at',
    ];

    protected $casts = [
        'expiry_date'    => 'date',
        'dispensing_date' => 'date',
        'cold_chain'     => 'boolean',
        'printed'        => 'boolean',
        'printed_at'     => 'datetime',
        'dispensed_at'   => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function dispenser()
    {
        return $this->belongsTo(Staff::class, 'dispensed_by');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function stockBatch()
    {
        return $this->belongsTo(StockBatch::class, 'stock_batch_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isNearExpiry(int $thresholdDays = 30): bool
    {
        if (! $this->expiry_date) {
            return false;
        }
        return $this->expiry_date->diffInDays(now()) <= $thresholdDays && $this->expiry_date->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->expiry_date?->isPast() ?? false;
    }

    public function expiryColour(): string
    {
        if (! $this->expiry_date)             return 'text-plum-400';
        if ($this->isExpired())               return 'text-red-700 font-semibold';
        if ($this->isNearExpiry(30))          return 'text-red-600';
        if ($this->isNearExpiry(90))          return 'text-amber-600';
        return 'text-plum-700';
    }
}
