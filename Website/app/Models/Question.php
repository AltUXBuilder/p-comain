<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = [
        'questionnaire_id', 'question_text', 'type', 'options',
        'mandatory', 'sort_order',
        'parent_question_id', 'parent_trigger_value',
        'has_contraindication', 'contraindication_rules',
        'numeric_min', 'numeric_max',
        'bmi_min', 'bmi_max', 'bmi_action',
    ];

    protected function casts(): array
    {
        return [
            'options'                 => 'array',
            'mandatory'               => 'boolean',
            'has_contraindication'    => 'boolean',
            'contraindication_rules'  => 'array',
            'numeric_min'             => 'decimal:2',
            'numeric_max'             => 'decimal:2',
            'bmi_min'                 => 'decimal:2',
            'bmi_max'                 => 'decimal:2',
        ];
    }

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'parent_question_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Question::class, 'parent_question_id')->orderBy('sort_order');
    }

    /**
     * Determine if this question should be shown given the current answers.
     */
    public function isVisibleGivenAnswers(array $answers): bool
    {
        if (!$this->parent_question_id) {
            return true;
        }
        $parentAnswer = $answers[$this->parent_question_id] ?? null;
        if ($parentAnswer === null) {
            return false;
        }
        return (string) $parentAnswer === (string) $this->parent_trigger_value;
    }

    /**
     * Check if an answer triggers a contraindication.
     * Returns ['action' => 'flag'|'reject', 'message' => string] or null.
     */
    public function checkContraindication(mixed $answer): ?array
    {
        if (!$this->has_contraindication || empty($this->contraindication_rules)) {
            return null;
        }
        foreach ($this->contraindication_rules as $rule) {
            if ((string) $answer === (string) ($rule['trigger_value'] ?? '')) {
                return [
                    'action'      => $rule['action'] ?? 'flag',
                    'message'     => $rule['message'] ?? 'This answer requires review.',
                    'question_id' => $this->id,
                    'value'       => $answer,
                ];
            }
        }
        return null;
    }
}
