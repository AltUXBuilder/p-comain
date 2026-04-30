<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'consultation_id', 'user_id',
        'sender_type', 'sender_id',
        'body', 'internal_only',
        'read_at', 'attachment_path',
    ];

    protected function casts(): array
    {
        return [
            'internal_only' => 'boolean',
            'read_at'       => 'datetime',
        ];
    }

    public function isFromPatient(): bool { return $this->sender_type === 'patient'; }
    public function isFromStaff(): bool   { return $this->sender_type === 'staff'; }
    public function isRead(): bool        { return $this->read_at !== null; }

    public function markAsRead(): void
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sender()
    {
        if ($this->sender_type === 'patient') {
            return $this->belongsTo(User::class, 'sender_id');
        }
        return $this->belongsTo(Staff::class, 'sender_id');
    }

    public function scopeVisible($query)
    {
        return $query->where('internal_only', false);
    }
}
