<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    protected $table = 'consultations';

    protected $fillable = [
        'patient_id',
        'product_id',
        'questionnaire_id',
        'answers',
        'status',
        'prescriber_id',
        'reviewed_at',
    ];

    protected $casts = [
        'answers'     => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class);
    }

    public function questionnaire()
    {
        return $this->belongsTo(\App\Models\Questionnaire::class);
    }

    public function prescriber()
    {
        return $this->belongsTo(Staff::class, 'prescriber_id');
    }

    public function rejection()
    {
        return $this->hasOne(ConsultationRejection::class);
    }
}
