<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffNotification extends Model
{
    protected $table = 'staff_notifications';

    protected $fillable = [
        'staff_id',
        'type',
        'message',
        'read_at',
        'entity_type',
        'entity_id',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function markRead(): void
    {
        $this->update(['read_at' => now()]);
    }
}
