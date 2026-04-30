<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * CRM-side Patient model.
 *
 * Maps to the shared `users` table — same DB, read/write access.
 * Aliased as "Patient" in CRM context to distinguish from any future
 * staff-facing User concept.
 */
class Patient extends Model
{
    use SoftDeletes;

    protected $table = 'users';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'date_of_birth',
        'mobile',
        'address_line_1',
        'address_line_2',
        'city',
        'county',
        'postcode',
        'country',
        // CRM-managed flags
        'risk_flagged',
        'risk_flag_reason',
        'risk_flagged_by',
        'risk_flagged_at',
        'do_not_treat',
        'do_not_treat_reason',
        'do_not_treat_set_by',
        'do_not_treat_set_at',
        'deceased',
        'deceased_noted_at',
        'deceased_noted_by',
        // Identity
        'identity_verified',
        'gp_surgery_id',
    ];

    protected $casts = [
        'date_of_birth'       => 'date',
        'risk_flagged'        => 'boolean',
        'risk_flagged_at'     => 'datetime',
        'do_not_treat'        => 'boolean',
        'do_not_treat_set_at' => 'datetime',
        'deceased'            => 'boolean',
        'deceased_noted_at'   => 'datetime',
        'identity_verified'   => 'boolean',
        'email_verified_at'   => 'datetime',
    ];

    // ─── Computed attributes ─────────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth?->age;
    }

    public function getFormattedAddressAttribute(): string
    {
        return collect([
            $this->address_line_1,
            $this->address_line_2,
            $this->city,
            $this->county,
            $this->postcode,
        ])->filter()->implode(', ');
    }

    // ─── Relationships ───────────────────────────────────────────────────

    public function consultations()
    {
        return $this->hasMany(\App\Models\Consultation::class, 'patient_id');
    }

    public function prescriptions()
    {
        return $this->hasMany(\App\Models\Prescription::class, 'patient_id');
    }

    public function orders()
    {
        return $this->hasMany(\App\Models\Order::class, 'patient_id');
    }

    public function messages()
    {
        return $this->hasMany(\App\Models\Message::class, 'sender_id')
            ->where('sender_type', 'patient');
    }

    public function clinicalNotes()
    {
        return $this->hasMany(\App\Models\ClinicalNote::class, 'patient_id');
    }

    public function gpSurgery()
    {
        return $this->belongsTo(\App\Models\GpSurgery::class, 'gp_surgery_id');
    }

    public function riskFlaggedBy()
    {
        return $this->belongsTo(Staff::class, 'risk_flagged_by');
    }

    public function doNotTreatSetBy()
    {
        return $this->belongsTo(Staff::class, 'do_not_treat_set_by');
    }

    // ─── Scopes ──────────────────────────────────────────────────────────

    public function scopeFlagged($query)
    {
        return $query->where('risk_flagged', true);
    }

    public function scopeDoNotTreat($query)
    {
        return $query->where('do_not_treat', true);
    }

    public function scopeDeceased($query)
    {
        return $query->where('deceased', true);
    }

    public function scopeActive($query)
    {
        return $query->where('deceased', false)->where('do_not_treat', false);
    }

    // ─── Helper methods ──────────────────────────────────────────────────

    public function isActionable(): bool
    {
        return ! $this->deceased && ! $this->do_not_treat;
    }
}
