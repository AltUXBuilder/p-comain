<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'treatment_id', 'name', 'slug', 'generic_name', 'brand_name',
        'strength', 'form', 'dosage_instructions', 'description',
        'product_type', 'price_one_off', 'subscription_tiers',
        'stock_quantity', 'minimum_stock_level', 'in_stock',
        'requires_cold_chain', 'requires_age_verification', 'has_questionnaire',
        'pil_path', 'images', 'supplier_name', 'active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'subscription_tiers'        => 'array',
            'images'                    => 'array',
            'price_one_off'             => 'decimal:2',
            'requires_cold_chain'       => 'boolean',
            'requires_age_verification' => 'boolean',
            'has_questionnaire'         => 'boolean',
            'in_stock'                  => 'boolean',
            'active'                    => 'boolean',
        ];
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class);
    }

    public function category()
    {
        return $this->treatment->category ?? null;
    }

    public function questionnaires(): BelongsToMany
    {
        return $this->belongsToMany(Questionnaire::class, 'product_questionnaire');
    }

    public function activeQuestionnaire()
    {
        return $this->questionnaires()->where('active', true)->first();
    }

    public function stock(): HasOne
    {
        return $this->hasOne(Stock::class);
    }

    public function stockBatches(): HasMany
    {
        return $this->hasMany(StockBatch::class);
    }

    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function isPom(): bool
    {
        return $this->product_type === 'POM';
    }

    public function scopeActive($query)
    {
        return $query->where('active', true)->orderBy('sort_order');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
