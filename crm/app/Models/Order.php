<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $table = 'orders';

    protected $fillable = [
        'order_number',
        'user_id',
        'prescription_id',
        'status',
        'subtotal',
        'vat_amount',
        'shipping_cost',
        'total',
        'currency',
        'stripe_payment_intent_id',
        'stripe_charge_id',
        'payment_method',
        'carrier',
        'tracking_number',
        'tracking_url',
        'requires_cold_chain',
        'delivery_name',
        'delivery_address_line_1',
        'delivery_address_line_2',
        'delivery_city',
        'delivery_postcode',
        'delivery_country',
        'dispatched_by',
        'dispatched_at',
        'delivered_at',
        'fulfilment_notes',
        'packing_notes',
        'return_reason',
        'return_received_at',
        'return_handled_by',
        'failed_delivery_notes',
        'reship_order_id',
        'manifest_batch',
    ];

    protected $casts = [
        'dispatched_at'      => 'datetime',
        'delivered_at'       => 'datetime',
        'return_received_at' => 'datetime',
        'requires_cold_chain' => 'boolean',
        'subtotal'           => 'decimal:2',
        'vat_amount'         => 'decimal:2',
        'shipping_cost'      => 'decimal:2',
        'total'              => 'decimal:2',
    ];

    // ── Status constants ──────────────────────────────────────────────────────

    const STATUS_PENDING_PAYMENT   = 'pending_payment';
    const STATUS_PAYMENT_CONFIRMED = 'payment_confirmed';
    const STATUS_PROCESSING        = 'processing';
    const STATUS_DISPATCHED        = 'dispatched';
    const STATUS_DELIVERED         = 'delivered';
    const STATUS_RETURNED          = 'returned';
    const STATUS_CANCELLED         = 'cancelled';
    const STATUS_REFUNDED          = 'refunded';
    const STATUS_FAILED_DELIVERY   = 'failed_delivery';

    const STATUSES = [
        self::STATUS_PENDING_PAYMENT   => 'Pending Payment',
        self::STATUS_PAYMENT_CONFIRMED => 'Payment Confirmed',
        self::STATUS_PROCESSING        => 'Processing',
        self::STATUS_DISPATCHED        => 'Dispatched',
        self::STATUS_DELIVERED         => 'Delivered',
        self::STATUS_RETURNED          => 'Returned',
        self::STATUS_CANCELLED         => 'Cancelled',
        self::STATUS_REFUNDED          => 'Refunded',
        self::STATUS_FAILED_DELIVERY   => 'Failed Delivery',
    ];

    const CARRIERS = [
        'royal_mail' => 'Royal Mail',
        'dpd'        => 'DPD',
        'evri'       => 'Evri',
        'other'      => 'Other',
    ];

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function statusColour(): string
    {
        return match($this->status) {
            self::STATUS_PENDING_PAYMENT   => 'bg-amber-100 text-amber-700',
            self::STATUS_PAYMENT_CONFIRMED => 'bg-blue-100 text-blue-700',
            self::STATUS_PROCESSING        => 'bg-plum-100 text-plum-700',
            self::STATUS_DISPATCHED        => 'bg-indigo-100 text-indigo-700',
            self::STATUS_DELIVERED         => 'bg-green-100 text-green-700',
            self::STATUS_RETURNED          => 'bg-orange-100 text-orange-700',
            self::STATUS_CANCELLED,
            self::STATUS_REFUNDED          => 'bg-gray-100 text-gray-600',
            self::STATUS_FAILED_DELIVERY   => 'bg-red-100 text-red-700',
            default                        => 'bg-plum-100 text-plum-600',
        };
    }

    public function carrierLabel(): string
    {
        return self::CARRIERS[$this->carrier ?? ''] ?? ($this->carrier ?? '—');
    }

    public function deliveryAddress(): string
    {
        return collect([
            $this->delivery_address_line_1,
            $this->delivery_address_line_2,
            $this->delivery_city,
            $this->delivery_postcode,
        ])->filter()->implode(', ');
    }

    public function trackingLink(): ?string
    {
        if ($this->tracking_url)   return $this->tracking_url;
        if (! $this->tracking_number) return null;

        return match($this->carrier) {
            'royal_mail' => "https://www.royalmail.com/track-your-item#/tracking-results/{$this->tracking_number}",
            'dpd'        => "https://track.dpd.co.uk/search?reference={$this->tracking_number}",
            'evri'       => "https://www.evri.com/track-a-parcel/parcel/{$this->tracking_number}",
            default      => null,
        };
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'user_id');
    }

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function dispatchedBy()
    {
        return $this->belongsTo(Staff::class, 'dispatched_by');
    }

    public function returnHandledBy()
    {
        return $this->belongsTo(Staff::class, 'return_handled_by');
    }

    public function reshipOrder()
    {
        return $this->belongsTo(Order::class, 'reship_order_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeReadyToDispatch($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    public function scopeColdChain($query)
    {
        return $query->where('requires_cold_chain', true);
    }
}
