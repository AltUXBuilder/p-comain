<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    public $timestamps = false;

    protected $fillable = [
        'staff_id',
        'gphc_number',
        'action',
        'entity_type',
        'entity_id',
        'metadata',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'metadata'   => 'array',
        'created_at' => 'datetime',
    ];

    // Prevent updates/deletes on audit records
    protected static function boot(): void
    {
        parent::boot();

        static::updating(fn () => false);
        static::deleting(fn () => false);
    }

    // ─── Factory method ──────────────────────────────────────────────────────

    public static function record(
        ?int $staffId,
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        array $metadata = [],
        ?Request $request = null
    ): self {
        $request ??= app(Request::class);

        $staff = $staffId ? Staff::find($staffId) : null;

        return static::create([
            'staff_id'    => $staffId,
            'gphc_number' => $staff?->gphc_number,
            'action'      => $action,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'metadata'    => $metadata,
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
            'created_at'  => now(),
        ]);
    }

    // ─── Relationships ───────────────────────────────────────────────────────

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}
