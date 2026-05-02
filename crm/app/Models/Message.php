<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use SoftDeletes;

    protected $table = 'messages';

    protected $fillable = [
        'consultation_id',
        'user_id',
        'sender_type',
        'sender_id',
        'body',
        'internal_only',
        'read_at',
        'attachment_path',
    ];

    protected $casts = [
        'internal_only' => 'boolean',
        'read_at'       => 'datetime',
    ];

    public function consultation()
    {
        return $this->belongsTo(\App\Models\Consultation::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'user_id');
    }

    public function senderStaff()
    {
        return $this->belongsTo(Staff::class, 'sender_id');
    }

    public function isFromStaff(): bool
    {
        return $this->sender_type === 'staff';
    }

    public function isFromPatient(): bool
    {
        return $this->sender_type === 'patient';
    }

    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }

    public function senderName(): string
    {
        if ($this->sender_type === 'staff') {
            return $this->senderStaff?->full_name ?? 'Staff';
        }
        return $this->patient?->full_name ?? 'Patient';
    }

    public function scopeVisible($query)
    {
        // Staff see all; this scope is for patient-side filtering
        return $query->where('internal_only', false);
    }
}
