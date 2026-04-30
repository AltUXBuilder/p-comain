<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockBatch extends Model
{
    protected $fillable = [
        'product_id', 'supplier_id', 'batch_number', 'expiry_date',
        'quantity_received', 'quantity_remaining', 'unit_cost',
        'received_date', 'received_by', 'cold_chain_maintained',
        'status', 'write_off_reason',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date'           => 'date',
            'received_date'         => 'date',
            'unit_cost'             => 'decimal:2',
            'cold_chain_maintained' => 'boolean',
        ];
    }

    public function isExpired(): bool
    {
        return $this->expiry_date->isPast();
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->expiry_date->isBefore(now()->addDays($days)) && !$this->isExpired();
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'received_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('quantity_remaining', '>', 0);
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->active()
            ->where('expiry_date', '<=', now()->addDays($days))
            ->where('expiry_date', '>=', now())
            ->orderBy('expiry_date');
    }
}
