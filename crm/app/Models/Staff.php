<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Staff extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, TwoFactorAuthenticatable;

    protected $table = 'staff';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'role',
        'gphc_number',
        'signature_path',
        'signature_set_at',
        'active',
        'out_of_office',
        'out_of_office_reassign_to',
        'welcome_token',
        'welcome_token_expires_at',
        'welcome_completed_at',
        'max_daily_consultations',
        'two_factor_secret',
        'two_factor_confirmed',
        'two_factor_recovery_codes',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'welcome_token',
    ];

    protected $casts = [
        'email_verified_at'          => 'datetime',
        'active'                     => 'boolean',
        'out_of_office'              => 'boolean',
        'two_factor_confirmed'       => 'boolean',
        'two_factor_recovery_codes'  => 'json',
        'welcome_token_expires_at'   => 'datetime',
        'welcome_completed_at'       => 'datetime',
        'signature_set_at'           => 'datetime',
        'password'                   => 'hashed',
    ];

    // ─── Role constants ──────────────────────────────────────────────────────

    const ROLE_SUPER_ADMIN               = 'super_admin';
    const ROLE_SUPERINTENDENT_PHARMACIST = 'superintendent_pharmacist';
    const ROLE_PRESCRIBER                = 'prescriber';
    const ROLE_DISPENSER                 = 'dispenser';
    const ROLE_CUSTOMER_SUPPORT          = 'customer_support';
    const ROLE_FINANCE                   = 'finance';

    const ROLES = [
        self::ROLE_SUPER_ADMIN               => 'Super Admin',
        self::ROLE_SUPERINTENDENT_PHARMACIST => 'Superintendent Pharmacist',
        self::ROLE_PRESCRIBER                => 'Prescriber',
        self::ROLE_DISPENSER                 => 'Dispenser',
        self::ROLE_CUSTOMER_SUPPORT          => 'Customer Support',
        self::ROLE_FINANCE                   => 'Finance',
    ];

    // Roles that require a GPhC number
    const ROLES_REQUIRING_GPHC = [
        self::ROLE_SUPERINTENDENT_PHARMACIST,
        self::ROLE_PRESCRIBER,
    ];

    // ─── Role helpers ────────────────────────────────────────────────────────

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isClinical(): bool
    {
        return in_array($this->role, [
            self::ROLE_SUPERINTENDENT_PHARMACIST,
            self::ROLE_PRESCRIBER,
        ]);
    }

    public function roleLabel(): string
    {
        return self::ROLES[$this->role] ?? ucfirst($this->role);
    }

    public function requiresGphc(): bool
    {
        return in_array($this->role, self::ROLES_REQUIRING_GPHC);
    }

    // ─── Full name helper ────────────────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getDisplayNameAttribute(): string
    {
        $prefix = $this->isClinical() ? 'Dr ' : '';
        return "{$prefix}{$this->first_name} {$this->last_name}";
    }

    // ─── Welcome token ───────────────────────────────────────────────────────

    public function generateWelcomeToken(): string
    {
        $token = Str::random(64);
        $this->update([
            'welcome_token'            => hash('sha256', $token),
            'welcome_token_expires_at' => Carbon::now()->addHours(72),
        ]);
        return $token;
    }

    public function isWelcomeTokenValid(string $token): bool
    {
        if (! $this->welcome_token || ! $this->welcome_token_expires_at) {
            return false;
        }
        return hash('sha256', $token) === $this->welcome_token
            && Carbon::now()->isBefore($this->welcome_token_expires_at);
    }

    public function completeWelcome(): void
    {
        $this->update([
            'welcome_token'            => null,
            'welcome_token_expires_at' => null,
            'welcome_completed_at'     => Carbon::now(),
        ]);
    }

    // ─── 2FA helpers ─────────────────────────────────────────────────────────

    public function hasTwoFactorEnabled(): bool
    {
        return ! is_null($this->two_factor_secret) && $this->two_factor_confirmed;
    }

    // ─── Relationships ───────────────────────────────────────────────────────

    public function sessionLogs()
    {
        return $this->hasMany(\App\Models\SessionLog::class, 'staff_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(\App\Models\AuditLog::class, 'staff_id');
    }

    public function notifications()
    {
        return $this->hasMany(\App\Models\StaffNotification::class, 'staff_id');
    }

    public function clinicalNotes()
    {
        return $this->hasMany(\App\Models\ClinicalNote::class, 'staff_id');
    }

    public function reassignTarget()
    {
        return $this->belongsTo(Staff::class, 'out_of_office_reassign_to');
    }
}
