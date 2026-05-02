<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Invoice extends Model
{
    protected $table = 'invoices';

    protected $fillable = [
        'invoice_number',
        'user_id',
        'order_id',
        'subtotal',
        'vat_amount',
        'total',
        'currency',
        'stripe_invoice_id',
        'pdf_path',
        'status',
        'issued_at',
        'paid_at',
    ];

    protected $casts = [
        'subtotal'    => 'decimal:2',
        'vat_amount'  => 'decimal:2',
        'total'       => 'decimal:2',
        'issued_at'   => 'datetime',
        'paid_at'     => 'datetime',
    ];

    const STATUSES = [
        'draft'  => 'Draft',
        'issued' => 'Issued',
        'paid'   => 'Paid',
        'void'   => 'Void',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($inv) {
            if (empty($inv->invoice_number)) {
                $seq = static::withTrashed()->count() + 1;
                $inv->invoice_number = 'INV-' . now()->format('Y') . '-' . str_pad($seq, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function statusColour(): string
    {
        return match($this->status) {
            'draft'  => 'bg-plum-100 text-plum-600',
            'issued' => 'bg-blue-100 text-blue-700',
            'paid'   => 'bg-green-100 text-green-700',
            'void'   => 'bg-gray-100 text-gray-500',
            default  => 'bg-plum-100 text-plum-600',
        };
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'user_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
