<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultationRejection extends Model
{
    protected $fillable = [
        'consultation_id', 'prescriber_id', 'prescriber_gphc_number',
        'reason', 'rejection_type',
        'patient_notified', 'patient_notified_at',
    ];

    protected function casts(): array
    {
        return [
            'patient_notified'    => 'boolean',
            'patient_notified_at' => 'datetime',
        ];
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    public function prescriber(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'prescriber_id');
    }
}
