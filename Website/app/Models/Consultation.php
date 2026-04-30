<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Consultation extends Model
{
    protected $fillable = [
        'user_id', 'product_id', 'questionnaire_id', 'prescriber_id',
        'answers', 'status',
        'contraindication_flagged', 'contraindication_flags',
        'high_risk', 'prescriber_notes',
        'submitted_at', 'reviewed_at', 'approved_at',
        'parent_consultation_id', 'is_reorder',
    ];

    protected function casts(): array
    {
        return [
            'answers'                  => 'array',
            'contraindication_flagged' => 'boolean',
            'contraindication_flags'   => 'array',
            'high_risk'                => 'boolean',
            'is_reorder'               => 'boolean',
            'submitted_at'             => 'datetime',
            'reviewed_at'              => 'datetime',
            'approved_at'              => 'datetime',
        ];
    }

    // ── Status helpers ────────────────────────────────────────────────────────

    public function isApproved(): bool  { return $this->status === 'approved'; }
    public function isRejected(): bool  { return $this->status === 'rejected'; }
    public function isPending(): bool   { return in_array($this->status, ['submitted', 'under_review']); }
    public function isFlagged(): bool   { return $this->status === 'flagged' || $this->contraindication_flagged; }

    // ── Relations ─────────────────────────────────────────────────────────────

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

    public function prescriber(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'prescriber_id');
    }

    public function prescription(): HasOne
    {
        return $this->hasOne(Prescription::class);
    }

    public function rejection(): HasOne
    {
        return $this->hasOne(ConsultationRejection::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function parentConsultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class, 'parent_consultation_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->whereIn('status', ['submitted', 'under_review']);
    }

    public function scopeFlagged($query)
    {
        return $query->where(function ($q) {
            $q->where('status', 'flagged')
              ->orWhere('contraindication_flagged', true);
        });
    }
}
