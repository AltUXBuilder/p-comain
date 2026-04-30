<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowRule extends Model
{
    protected $fillable = [
        'name', 'description', 'trigger_event',
        'conditions', 'actions', 'active', 'is_system',
        'created_by', 'updated_by', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'conditions' => 'array',
            'actions'    => 'array',
            'active'     => 'boolean',
            'is_system'  => 'boolean',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true)->orderBy('sort_order');
    }

    public function scopeForEvent($query, string $event)
    {
        return $query->active()->where('trigger_event', $event);
    }
}
