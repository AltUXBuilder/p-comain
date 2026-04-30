<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockWriteOff extends Model
{
    protected $table = 'stock_write_offs';

    protected $fillable = [
        'stock_batch_id',
        'product_id',
        'written_off_by',
        'quantity',
        'reason',
        'notes',
    ];

    const REASONS = [
        'damaged'  => 'Damaged',
        'expired'  => 'Expired',
        'returned' => 'Returned',
        'recalled' => 'Recalled',
        'other'    => 'Other',
    ];

    public function batch()
    {
        return $this->belongsTo(StockBatch::class, 'stock_batch_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'written_off_by');
    }
}
