<?php

namespace App\Services;

use App\Models\DraftConsultation;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ConsultationDraftService
{
    private const COOKIE_NAME_KEY = 'draft_consultation.cookie_name';
    private const SESSION_KEY     = 'consultation_draft';

    /**
     * Save progress to Laravel session (primary storage during questionnaire).
     */
    public function saveToSession(Request $request, int $productId, int $questionnaireId, array $answers, int $currentStep): void
    {
        $request->session()->put(self::SESSION_KEY, [
            'product_id'       => $productId,
            'questionnaire_id' => $questionnaireId,
            'answers'          => $answers,
            'current_step'     => $currentStep,
            'saved_at'         => now()->toISOString(),
        ]);
    }

    /**
     * Get current draft data from session.
     */
    public function getFromSession(Request $request): ?array
    {
        return $request->session()->get(self::SESSION_KEY);
    }

    /**
     * Clear session draft data (called after submission or login flush).
     */
    public function clearSession(Request $request): void
    {
        $request->session()->forget(self::SESSION_KEY);
    }

    /**
     * Persist session data to DB as a recoverable draft (called if session about to expire).
     * Returns the UUID stored in the patient's cookie.
     */
    public function persistToDB(array $sessionData, ?int $userId = null): ?string
    {
        $productId = $sessionData['product_id'] ?? null;
        if (!$productId || empty($sessionData['answers'])) {
            return null;
        }

        $draft = DraftConsultation::create([
            'user_id'          => $userId,
            'product_id'       => $productId,
            'questionnaire_id' => $sessionData['questionnaire_id'] ?? null,
            'answers'          => $sessionData['answers'],
            'current_step'     => $sessionData['current_step'] ?? 0,
        ]);

        return $draft->uuid;
    }

    /**
     * Find a draft consultation by UUID from cookie.
     */
    public function findByUuid(string $uuid): ?DraftConsultation
    {
        return DraftConsultation::valid()->where('uuid', $uuid)->first();
    }

    /**
     * Find an existing valid draft for a user + product combination.
     */
    public function findForUser(User $user, int $productId): ?DraftConsultation
    {
        return DraftConsultation::valid()
            ->where('user_id', $user->id)
            ->where('product_id', $productId)
            ->latest()
            ->first();
    }

    /**
     * Restore a draft into session (for the "resume draft" flow on login).
     */
    public function restoreToSession(Request $request, DraftConsultation $draft): void
    {
        $this->saveToSession(
            $request,
            $draft->product_id,
            $draft->questionnaire_id,
            $draft->answers ?? [],
            $draft->current_step ?? 0
        );
    }

    /**
     * Get the cookie name from config.
     */
    public function cookieName(): string
    {
        return config('pharmacy.' . self::COOKIE_NAME_KEY, 'pando_draft_uuid');
    }

    /**
     * Purge all expired drafts (called by scheduler).
     */
    public static function purgeExpired(): int
    {
        return DraftConsultation::where('expires_at', '<', now())->delete();
    }
}
