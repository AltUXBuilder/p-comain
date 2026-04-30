<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DispensingLabel extends Model
{
    protected $fillable = [
        'prescription_id', 'product_id', 'stock_batch_id', 'dispensed_by', 'dispensed_by_name',
        'patient_name', 'medication_name', 'medication_strength', 'medication_form',
        'dosage_instructions', 'dispensing_date', 'batch_number', 'expiry_date',
        'pharmacy_name', 'pharmacy_address', 'pharmacy_gphc_number',
        'pdf_path', 'label_format', 'printed_at',
    ];

    protected function casts(): array
    {
        return [
            'dispensing_date' => 'date',
            'expiry_date'     => 'date',
            'printed_at'      => 'datetime',
        ];
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stockBatch(): BelongsTo
    {
        return $this->belongsTo(StockBatch::class);
    }

    public function dispenser(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'dispensed_by');
    }
}
