<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_number', 'user_id', 'prescription_id', 'status',
        'subtotal', 'vat_amount', 'shipping_cost', 'total', 'currency',
        'stripe_payment_intent_id', 'stripe_charge_id', 'payment_method',
        'carrier', 'tracking_number', 'tracking_url', 'requires_cold_chain',
        'delivery_name', 'delivery_address_line_1', 'delivery_address_line_2',
        'delivery_city', 'delivery_postcode', 'delivery_country',
        'dispatched_by', 'dispatched_at', 'delivered_at', 'fulfilment_notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'            => 'decimal:2',
            'vat_amount'          => 'decimal:2',
            'shipping_cost'       => 'decimal:2',
            'total'               => 'decimal:2',
            'requires_cold_chain' => 'boolean',
            'dispatched_at'       => 'datetime',
            'delivered_at'        => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
        });
    }

    public static function generateOrderNumber(): string
    {
        $prefix = config('pharmacy.order_prefix', 'ORD');
        $year   = now()->format('Y');
        $last   = self::whereYear('created_at', $year)->count() + 1;
        return "{$prefix}-{$year}-" . str_pad($last, 5, '0', STR_PAD_LEFT);
    }

    // ── Relations ──────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function dispatcher(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'dispatched_by');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeDispatched($query)
    {
        return $query->where('status', 'dispatched');
    }

    public function scopeColdChain($query)
    {
        return $query->where('requires_cold_chain', true);
    }

    public function scopePendingDispatch($query)
    {
        return $query->where('status', 'processing');
    }
}
