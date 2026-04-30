<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number', 'user_id', 'order_id',
        'subtotal', 'vat_amount', 'total', 'currency',
        'stripe_invoice_id', 'pdf_path', 'status',
        'issued_at', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'   => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'total'      => 'decimal:2',
            'issued_at'  => 'datetime',
            'paid_at'    => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            if (empty($invoice->invoice_number)) {
                $prefix = config('pharmacy.invoice_prefix', 'INV');
                $year   = now()->format('Y');
                $last   = self::whereYear('created_at', $year)->count() + 1;
                $invoice->invoice_number = "{$prefix}-{$year}-" . str_pad($last, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
