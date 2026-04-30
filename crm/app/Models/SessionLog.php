<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class SessionLog extends Model
{
    protected $table = 'session_logs';

    public $timestamps = false;

    protected $fillable = [
        'staff_id',
        'success',
        'failure_reason',
        'ip_address',
        'user_agent',
        'device_fingerprint',
        'created_at',
    ];

    protected $casts = [
        'success'    => 'boolean',
        'created_at' => 'datetime',
    ];

    // ─── Factory method ──────────────────────────────────────────────────────

    public static function recordAttempt(
        ?int $staffId,
        Request $request,
        bool $success,
        ?string $failureReason = null
    ): self {
        return static::create([
            'staff_id'           => $staffId,
            'success'            => $success,
            'failure_reason'     => $failureReason,
            'ip_address'         => $request->ip(),
            'user_agent'         => $request->userAgent(),
            'device_fingerprint' => static::fingerprint($request),
            'created_at'         => now(),
        ]);
    }

    public static function fingerprint(Request $request): string
    {
        return hash('sha256', implode('|', [
            $request->userAgent(),
            $request->header('Accept-Language', ''),
            $request->header('Accept-Encoding', ''),
        ]));
    }

    // ─── Relationships ───────────────────────────────────────────────────────

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}
