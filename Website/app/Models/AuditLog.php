<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// ── AuditLog — append-only ────────────────────────────────────────────────────
class AuditLog extends Model
{
    public $timestamps = false;
    const UPDATED_AT = null;

    protected $fillable = [
        'staff_id', 'staff_name', 'staff_role', 'staff_gphc_number',
        'action', 'entity_type', 'entity_id', 'context',
        'ip_address', 'user_agent', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'context'    => 'array',
            'created_at' => 'datetime',
        ];
    }

    // Prevent any updates or deletes at model level
    public function save(array $options = []): bool
    {
        if ($this->exists) {
            throw new \RuntimeException('AuditLog records are append-only and cannot be modified.');
        }
        return parent::save($options);
    }

    public function delete(): bool
    {
        throw new \RuntimeException('AuditLog records cannot be deleted.');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
