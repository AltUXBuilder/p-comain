<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class DsarRequest extends Model
{
    protected $table = 'dsar_requests';

    protected $fillable = [
        'user_id',
        'handled_by',
        'type',
        'status',
        'requestor_email',
        'notes',
        'due_at',
        'completed_at',
        'exported_data_path',
    ];

    protected $casts = [
        'due_at'             => 'datetime',
        'completed_at'       => 'datetime',
        'exported_data_path' => 'array',
    ];

    const TYPES = [
        'subject_access' => 'Subject Access Request (SAR)',
        'erasure'        => 'Right to Erasure',
        'rectification'  => 'Right to Rectification',
        'portability'    => 'Data Portability',
        'objection'      => 'Right to Object',
    ];

    const STATUSES = [
        'received'    => 'Received',
        'in_progress' => 'In Progress',
        'completed'   => 'Completed',
        'rejected'    => 'Rejected',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($dsar) {
            if (empty($dsar->due_at)) {
                // GDPR Article 12: respond within 1 calendar month (30 days)
                $dsar->due_at = Carbon::now()->addDays(30);
            }
        });
    }

    public function isOverdue(): bool
    {
        return $this->due_at->isPast() && $this->status !== 'completed';
    }

    public function statusColour(): string
    {
        return match($this->status) {
            'received'    => 'bg-blue-100 text-blue-700',
            'in_progress' => 'bg-amber-100 text-amber-700',
            'completed'   => 'bg-green-100 text-green-700',
            'rejected'    => 'bg-red-100 text-red-700',
            default       => 'bg-plum-100 text-plum-600',
        };
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'user_id');
    }

    public function handler()
    {
        return $this->belongsTo(Staff::class, 'handled_by');
    }
}
