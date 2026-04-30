<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockBatch extends Model
{
    protected $table = 'stock_batches';

    protected $fillable = [
        'product_id',
        'batch_number',
        'expiry_date',
        'quantity_received',
        'quantity_remaining',
        'supplier_id',
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function isExpired(): bool
    {
        return $this->expiry_date?->isPast() ?? false;
    }

    public function isNearExpiry(int $days = 30): bool
    {
        return $this->expiry_date
            && $this->expiry_date->isFuture()
            && $this->expiry_date->diffInDays(now()) <= $days;
    }

    public function scopeAvailable($query)
    {
        return $query->where('quantity_remaining', '>', 0)
                     ->where(fn ($q) => $q->whereNull('expiry_date')->orWhere('expiry_date', '>', now()));
    }
}
