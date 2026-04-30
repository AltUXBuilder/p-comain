<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PurchaseOrder extends Model
{
    protected $table = 'purchase_orders';

    protected $fillable = [
        'po_number',
        'supplier_id',
        'created_by',
        'status',
        'expected_date',
        'received_date',
        'notes',
    ];

    protected $casts = [
        'expected_date' => 'date',
        'received_date' => 'date',
    ];

    const STATUSES = [
        'draft'     => 'Draft',
        'sent'      => 'Sent to Supplier',
        'received'  => 'Fully Received',
        'partial'   => 'Partially Received',
        'cancelled' => 'Cancelled',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($po) {
            if (empty($po->po_number)) {
                $po->po_number = 'PO-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
            }
        });
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator()
    {
        return $this->belongsTo(Staff::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
}

class PurchaseOrderItem extends Model
{
    protected $table = 'purchase_order_items';

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'quantity_ordered',
        'quantity_received',
        'unit_cost',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
