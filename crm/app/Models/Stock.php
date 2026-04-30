<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $table = 'stock';

    protected $fillable = [
        'product_id',
        'quantity_on_hand',
        'minimum_threshold',
        'alert_sent',
        'last_reconciled_at',
    ];

    protected $casts = [
        'alert_sent'          => 'boolean',
        'last_reconciled_at'  => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // ── Status helpers ────────────────────────────────────────────────────────

    public function isBelowThreshold(): bool
    {
        return $this->quantity_on_hand <= $this->minimum_threshold;
    }

    public function isOutOfStock(): bool
    {
        return $this->quantity_on_hand <= 0;
    }

    public function statusColour(): string
    {
        if ($this->isOutOfStock())      return 'bg-red-100 text-red-700';
        if ($this->isBelowThreshold())  return 'bg-amber-100 text-amber-700';
        return 'bg-green-100 text-green-700';
    }

    public function statusLabel(): string
    {
        if ($this->isOutOfStock())      return 'Out of stock';
        if ($this->isBelowThreshold())  return 'Low stock';
        return 'In stock';
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeLow($query)
    {
        return $query->whereRaw('quantity_on_hand <= minimum_threshold');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('quantity_on_hand', '<=', 0);
    }
}
