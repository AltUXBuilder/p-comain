<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes, Billable;

    protected $fillable = [
        'first_name', 'last_name', 'email', 'password',
        'date_of_birth',
        'address_line_1', 'address_line_2', 'city', 'county', 'postcode', 'country',
        'two_factor_enabled', 'trusted_devices',
        'gdpr_marketing_consent', 'gdpr_consent_at',
        'terms_accepted', 'terms_accepted_at',
        'do_not_treat', 'do_not_treat_reason',
        'deceased', 'deceased_at',
        'age_verified', 'age_verified_at',
        'identity_verified', 'identity_verified_at',
        'gp_surgery_id',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at'       => 'datetime',
            'date_of_birth'           => 'date',
            'two_factor_enabled'      => 'boolean',
            'trusted_devices'         => 'array',
            'gdpr_marketing_consent'  => 'boolean',
            'gdpr_consent_at'         => 'datetime',
            'terms_accepted'          => 'boolean',
            'terms_accepted_at'       => 'datetime',
            'do_not_treat'            => 'boolean',
            'deceased'                => 'boolean',
            'deceased_at'             => 'datetime',
            'age_verified'            => 'boolean',
            'age_verified_at'         => 'datetime',
            'identity_verified'       => 'boolean',
            'identity_verified_at'    => 'datetime',
            'password'                => 'hashed',
        ];
    }

    // ── Computed ──────────────────────────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getAgeAttribute(): int
    {
        return $this->date_of_birth->age;
    }

    public function isAdult(): bool
    {
        return $this->date_of_birth->age >= config('pharmacy.age_verification.minimum_age', 18);
    }

    // ── 2FA ───────────────────────────────────────────────────────────────────

    public function isTrustedDevice(string $fingerprint): bool
    {
        $devices = $this->trusted_devices ?? [];
        return collect($devices)->contains('fingerprint', $fingerprint);
    }

    public function trustDevice(string $fingerprint, string $ip): void
    {
        $devices   = $this->trusted_devices ?? [];
        $devices[] = [
            'fingerprint' => $fingerprint,
            'ip'          => $ip,
            'last_seen'   => now()->toISOString(),
        ];
        // Keep last 10 trusted devices
        if (count($devices) > 10) {
            $devices = array_slice($devices, -10);
        }
        $this->update(['trusted_devices' => $devices]);
    }

    // ── Relations ─────────────────────────────────────────────────────────────

    public function gpSurgery(): BelongsTo
    {
        return $this->belongsTo(GpSurgery::class);
    }

    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }

    public function draftConsultations(): HasMany
    {
        return $this->hasMany(DraftConsultation::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function twoFactorTokens(): HasMany
    {
        return $this->hasMany(TwoFactorToken::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('deceased', false)->where('do_not_treat', false);
    }
}
