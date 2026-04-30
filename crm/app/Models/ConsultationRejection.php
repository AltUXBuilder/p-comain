<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultationRejection extends Model
{
    protected $table = 'consultation_rejections';

    protected $fillable = [
        'consultation_id',
        'prescriber_id',
        'gphc_number',
        'reason',
        'patient_notified',
    ];

    protected $casts = [
        'patient_notified' => 'boolean',
    ];

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    public function prescriber()
    {
        return $this->belongsTo(Staff::class, 'prescriber_id');
    }
}
