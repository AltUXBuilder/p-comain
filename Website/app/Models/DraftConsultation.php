<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DraftConsultation extends Model
{
    protected $fillable = [
        'uuid', 'user_id', 'product_id', 'questionnaire_id',
        'answers', 'current_step', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'answers'    => 'array',
            'expires_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (DraftConsultation $draft) {
            if (empty($draft->uuid)) {
                $draft->uuid = (string) Str::uuid();
            }
            if (empty($draft->expires_at)) {
                $draft->expires_at = now()->addHours(
                    config('pharmacy.draft_consultation.expiry_hours', 48)
                );
            }
        });
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', now());
    }
}
