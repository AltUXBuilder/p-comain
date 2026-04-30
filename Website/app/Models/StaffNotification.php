<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffNotification extends Model
{
    protected $fillable = [
        'staff_id', 'type', 'title', 'message', 'severity',
        'entity_type', 'entity_id', 'action_url', 'read_at',
    ];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    public function isRead(): bool    { return $this->read_at !== null; }
    public function isUnread(): bool  { return $this->read_at === null; }
    public function isCritical(): bool { return $this->severity === 'critical'; }

    public function markAsRead(): void
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }
}
