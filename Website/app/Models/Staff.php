<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Staff extends Authenticatable
{
    use Notifiable, SoftDeletes;

    protected $table = 'staff';

    protected $fillable = [
        'first_name', 'last_name', 'email', 'password', 'role',
        'gphc_number',
        'two_factor_secret', 'two_factor_confirmed', 'two_factor_recovery_codes',
        'signature_path', 'signature_set_at',
        'active', 'out_of_office', 'out_of_office_reassign_to',
        'welcome_token', 'welcome_token_expires_at', 'welcome_completed_at',
        'max_daily_consultations',
    ];

    protected $hidden = [
        'password', 'remember_token',
        'two_factor_secret', 'two_factor_recovery_codes',
        'welcome_token',
    ];

    protected function casts(): array
    {
        return [
            'two_factor_confirmed'       => 'boolean',
            'two_factor_recovery_codes'  => 'encrypted:array',
            'two_factor_secret'          => 'encrypted',
            'active'                     => 'boolean',
            'out_of_office'              => 'boolean',
            'signature_set_at'           => 'datetime',
            'welcome_token_expires_at'   => 'datetime',
            'welcome_completed_at'       => 'datetime',
            'password'                   => 'hashed',
        ];
    }

    // ── Role helpers ──────────────────────────────────────────────────────

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isPrescriber(): bool
    {
        return in_array($this->role, ['prescriber', 'superintendent_pharmacist']);
    }

    public function isSuperintendent(): bool
    {
        return $this->role === 'superintendent_pharmacist';
    }

    public function isDispenser(): bool
    {
        return $this->role === 'dispenser';
    }

    public function requiresGphcNumber(): bool
    {
        return in_array($this->role, ['prescriber', 'superintendent_pharmacist']);
    }

    public function hasSignature(): bool
    {
        return !empty($this->signature_path);
    }

    // ── Computed ──────────────────────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getRoleDisplayAttribute(): string
    {
        return match($this->role) {
            'super_admin'                => 'Super Admin',
            'superintendent_pharmacist'  => 'Superintendent Pharmacist',
            'prescriber'                 => 'Prescriber',
            'dispenser'                  => 'Dispenser',
            'customer_support'           => 'Customer Support',
            'finance'                    => 'Finance',
            default                      => ucfirst($this->role),
        };
    }

    // ── Relations ─────────────────────────────────────────────────────────

    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class, 'prescriber_id');
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class, 'prescriber_id');
    }

    public function clinicalNotes(): HasMany
    {
        return $this->hasMany(ClinicalNote::class, 'staff_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(StaffNotification::class, 'staff_id');
    }

    public function unreadNotifications(): HasMany
    {
        return $this->notifications()->whereNull('read_at');
    }

    public function sessionLogs(): HasMany
    {
        return $this->hasMany(SessionLog::class, 'staff_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'staff_id');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopePrescribers($query)
    {
        return $query->whereIn('role', ['prescriber', 'superintendent_pharmacist']);
    }

    public function scopeAvailable($query)
    {
        return $query->active()->where('out_of_office', false);
    }
}
