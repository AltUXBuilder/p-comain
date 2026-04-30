<?php

namespace App\Services;

use App\Models\Consultation;
use App\Models\DraftConsultation;
use App\Models\Product;
use App\Models\Questionnaire;
use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ConsultationService
{
    /**
     * Submit a consultation from session/draft answers for a given user and product.
     * This is the single point of submission for all 4 consultation flow scenarios.
     */
    public function submit(User $user, Product $product, array $answers, ?DraftConsultation $draft = null): Consultation
    {
        return DB::transaction(function () use ($user, $product, $answers, $draft) {
            $questionnaire = $product->activeQuestionnaire();

            // Run contraindication checks across all answers
            $flags = $this->checkContraindications($questionnaire, $answers);
            $autoReject = collect($flags)->contains('action', 'reject');
            $autoFlag   = collect($flags)->contains('action', 'flag');

            $status = 'submitted';
            if ($autoReject) {
                $status = 'rejected';
            } elseif ($autoFlag) {
                $status = 'flagged';
            }

            $consultation = Consultation::create([
                'user_id'                  => $user->id,
                'product_id'               => $product->id,
                'questionnaire_id'         => $questionnaire?->id,
                'answers'                  => $answers,
                'status'                   => $status,
                'contraindication_flagged' => $autoFlag || $autoReject,
                'contraindication_flags'   => $flags ?: null,
                'submitted_at'             => now(),
            ]);

            // Remove draft if exists
            if ($draft) {
                $draft->delete();
            }

            // Trigger workflow events
            if ($autoFlag || $autoReject) {
                event('consultation.contraindication_flagged', $consultation);
            }

            return $consultation;
        });
    }

    /**
     * Flush session answers to a new consultation on registration or login.
     * Called immediately after the patient authenticates.
     */
    public function flushSessionToConsultation(User $user, array $sessionData): ?Consultation
    {
        $productId = $sessionData['product_id'] ?? null;
        $answers   = $sessionData['answers'] ?? [];

        if (!$productId || empty($answers)) {
            return null;
        }

        $product = Product::find($productId);
        if (!$product) {
            return null;
        }

        return $this->submit($user, $product, $answers);
    }

    /**
     * Flush a persisted draft consultation (UUID-based cookie recovery) into a real consultation.
     */
    public function flushDraftToConsultation(User $user, DraftConsultation $draft): ?Consultation
    {
        if ($draft->isExpired()) {
            $draft->delete();
            return null;
        }

        $product = $draft->product;
        $answers = $draft->answers ?? [];

        if (!$product || empty($answers)) {
            return null;
        }

        $draft->update(['user_id' => $user->id]);

        return $this->submit($user, $product, $answers, $draft);
    }

    /**
     * Get all questions that should be visible given current answers (respects branching).
     */
    public function getVisibleQuestions(Questionnaire $questionnaire, array $answers = []): \Illuminate\Support\Collection
    {
        return $questionnaire->questions->filter(
            fn(Question $q) => $q->isVisibleGivenAnswers($answers)
        )->values();
    }

    /**
     * Run contraindication checks across all answers for a questionnaire.
     */
    public function checkContraindications(?Questionnaire $questionnaire, array $answers): array
    {
        if (!$questionnaire) {
            return [];
        }

        $flags = [];

        foreach ($questionnaire->questions as $question) {
            $answer = $answers[$question->id] ?? null;
            if ($answer === null) {
                continue;
            }

            // Standard contraindication check
            $flag = $question->checkContraindication($answer);
            if ($flag) {
                $flags[] = $flag;
            }

            // BMI check
            if ($question->type === 'bmi_calculator' && isset($answer['bmi'])) {
                $bmi = (float) $answer['bmi'];
                if ($question->bmi_min && $bmi < $question->bmi_min) {
                    $flags[] = [
                        'action'      => $question->bmi_action ?? 'flag',
                        'message'     => "BMI of {$bmi} is below the minimum threshold of {$question->bmi_min}.",
                        'question_id' => $question->id,
                        'value'       => $bmi,
                    ];
                }
                if ($question->bmi_max && $bmi > $question->bmi_max) {
                    $flags[] = [
                        'action'      => $question->bmi_action ?? 'flag',
                        'message'     => "BMI of {$bmi} exceeds the maximum threshold of {$question->bmi_max}.",
                        'question_id' => $question->id,
                        'value'       => $bmi,
                    ];
                }
            }
        }

        return $flags;
    }

    /**
     * Check if a patient can reorder based on their existing approved consultation.
     */
    public function canReorder(User $user, Product $product): bool
    {
        return Consultation::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->where('status', 'approved')
            ->whereHas('prescription', fn($q) => $q->whereIn('status', ['approved', 'sent_to_dispense', 'dispensed']))
            ->exists();
    }
}
