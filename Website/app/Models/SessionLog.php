<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionLog extends Model
{
    public $timestamps = false;
    const UPDATED_AT = null;

    protected $fillable = [
        'staff_id', 'email_attempted', 'outcome',
        'ip_address', 'device_fingerprint', 'user_agent',
        'new_device', 'new_ip', 'suspicious',
        'session_id', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'new_device'  => 'boolean',
            'new_ip'      => 'boolean',
            'suspicious'  => 'boolean',
            'created_at'  => 'datetime',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function scopeSuspicious($query)
    {
        return $query->where('suspicious', true);
    }
}
