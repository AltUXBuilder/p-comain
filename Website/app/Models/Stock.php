<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stock extends Model
{
    protected $table = 'stock';

    protected $fillable = [
        'product_id', 'quantity_on_hand', 'minimum_threshold',
        'alert_sent', 'last_reconciled_at',
    ];

    protected function casts(): array
    {
        return [
            'alert_sent'          => 'boolean',
            'last_reconciled_at'  => 'datetime',
        ];
    }

    public function isBelowMinimum(): bool
    {
        return $this->quantity_on_hand <= $this->minimum_threshold;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeBelowMinimum($query)
    {
        return $query->whereRaw('quantity_on_hand <= minimum_threshold');
    }
}
