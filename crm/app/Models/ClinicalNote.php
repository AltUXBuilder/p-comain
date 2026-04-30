<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicalNote extends Model
{
    protected $table = 'clinical_notes';

    protected $fillable = [
        'patient_id',
        'staff_id',
        'consultation_id',
        'body',
        'internal_only',
    ];

    protected $casts = [
        'internal_only' => 'boolean',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
}
