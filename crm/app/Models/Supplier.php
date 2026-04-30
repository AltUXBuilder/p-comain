<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'suppliers';

    protected $fillable = [
        'name',
        'contact_name',
        'email',
        'phone',
        'address',
        'notes',
        'active',
    ];

    protected $casts = ['active' => 'boolean'];

    public function batches()
    {
        return $this->hasMany(StockBatch::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function products()
    {
        return $this->hasManyThrough(Product::class, StockBatch::class, 'supplier_id', 'id', 'id', 'product_id')
            ->distinct();
    }
}
