<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    protected $table = 'prescriptions';

    protected $fillable = [
        'prescription_number',
        'consultation_id',
        'patient_id',
        'prescriber_id',
        'product_id',
        'status',
        'signed_at',
        'pdf_path',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($prescription) {
            if (empty($prescription->prescription_number)) {
                $prescription->prescription_number = 'RX-' . strtoupper(substr(uniqid(), -8));
            }
        });
    }

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
}
