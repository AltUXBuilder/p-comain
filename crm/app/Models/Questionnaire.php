<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Questionnaire extends Model
{
    use SoftDeletes;

    protected $table = 'questionnaires';

    protected $fillable = ['name', 'description', 'active'];

    protected $casts = ['active' => 'boolean'];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('sort_order');
    }

    /**
     * Root questions only (no parent — not a branched child).
     * Used when rendering the questionnaire on the website.
     */
    public function rootQuestions()
    {
        return $this->hasMany(Question::class)
            ->whereNull('parent_question_id')
            ->orderBy('sort_order');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_questionnaire');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function questionCount(): int
    {
        return $this->questions()->count();
    }

    public function hasContraindications(): bool
    {
        return $this->questions()->where('has_contraindication', true)->exists();
    }

    public function duplicate(): self
    {
        $clone = $this->replicate(['deleted_at']);
        $clone->name = $this->name . ' (copy)';
        $clone->active = false;
        $clone->save();

        // Clone all questions and remap parent IDs
        $idMap = [];
        foreach ($this->questions()->whereNull('parent_question_id')->orderBy('sort_order')->get() as $q) {
            $newQ = $q->replicate();
            $newQ->questionnaire_id = $clone->id;
            $newQ->save();
            $idMap[$q->id] = $newQ->id;
        }

        // Now clone branched (child) questions with remapped parent IDs
        foreach ($this->questions()->whereNotNull('parent_question_id')->orderBy('sort_order')->get() as $q) {
            $newQ = $q->replicate();
            $newQ->questionnaire_id = $clone->id;
            $newQ->parent_question_id = $idMap[$q->parent_question_id] ?? null;
            $newQ->save();
            $idMap[$q->id] = $newQ->id;
        }

        return $clone;
    }
}
