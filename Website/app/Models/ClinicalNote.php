<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClinicalNote extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'staff_id', 'consultation_id',
        'body', 'internal_only',
        'gp_notified', 'gp_notified_at', 'gp_surgery_id',
    ];

    protected function casts(): array
    {
        return [
            'internal_only'  => 'boolean',
            'gp_notified'    => 'boolean',
            'gp_notified_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    public function gpSurgery(): BelongsTo
    {
        return $this->belongsTo(GpSurgery::class);
    }
}
