<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

class TwoFactorToken extends Model
{
    protected $fillable = [
        'user_id', 'token', 'purpose',
        'ip_address', 'device_fingerprint',
        'used', 'expires_at',
    ];

    protected $hidden = ['token'];

    protected function casts(): array
    {
        return [
            'used'       => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isValid(): bool
    {
        return !$this->used && $this->expires_at->isFuture();
    }

    public function verify(string $plainOtp): bool
    {
        if (!$this->isValid()) {
            return false;
        }
        if (Hash::check($plainOtp, $this->token)) {
            $this->update(['used' => true]);
            return true;
        }
        return false;
    }

    public function scopeValid($query)
    {
        return $query->where('used', false)->where('expires_at', '>', now());
    }
}
