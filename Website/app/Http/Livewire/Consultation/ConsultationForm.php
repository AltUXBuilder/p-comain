<?php

namespace App\Http\Livewire\Consultation;

use App\Models\DraftConsultation;
use App\Models\Product;
use App\Models\Questionnaire;
use App\Models\Question;
use App\Services\ConsultationDraftService;
use App\Services\ConsultationService;
use Illuminate\Support\Collection;
use Livewire\Component;

class ConsultationForm extends Component
{
    public Product $product;
    public ?Questionnaire $questionnaire = null;

    // All questions loaded for this questionnaire
    public Collection $allQuestions;

    // Current answers keyed by question_id
    public array $answers = [];

    // BMI intermediate values
    public ?float $heightCm  = null;
    public ?float $weightKg  = null;
    public ?float $calculatedBmi = null;

    // Pagination
    public int $currentStep  = 0;
    public int $totalSteps   = 0;

    // Gate state — shown when guest reaches end of questionnaire
    public bool $showGate    = false;
    public bool $submitted   = false;

    // Validation errors
    public array $stepErrors = [];

    public function mount(Product $product, ?string $draftUuid = null): void
    {
        $this->product       = $product;
        $this->questionnaire = $product->activeQuestionnaire();
        $this->allQuestions  = $this->questionnaire
            ? $this->questionnaire->questions()->orderBy('sort_order')->get()
            : collect();

        // Calculate steps (root-level questions only, branching is dynamic)
        $rootQuestions   = $this->allQuestions->whereNull('parent_question_id');
        $this->totalSteps = $rootQuestions->count();

        // Restore from session or draft
        if ($draftUuid) {
            $draft = app(ConsultationDraftService::class)->findByUuid($draftUuid);
            if ($draft && !$draft->isExpired()) {
                $this->answers     = $draft->answers ?? [];
                $this->currentStep = $draft->current_step ?? 0;
            }
        } else {
            $sessionData = app(ConsultationDraftService::class)->getFromSession(request());
            if ($sessionData && ($sessionData['product_id'] ?? null) == $product->id) {
                $this->answers     = $sessionData['answers'] ?? [];
                $this->currentStep = $sessionData['current_step'] ?? 0;
            }
        }
    }

    /**
     * Get the visible questions for the current step.
     */
    public function getVisibleQuestionsProperty(): Collection
    {
        return $this->allQuestions->filter(
            fn(Question $q) => $q->isVisibleGivenAnswers($this->answers)
        )->values();
    }

    /**
     * Get questions for the current step (paginated by root question).
     */
    public function getCurrentStepQuestionsProperty(): Collection
    {
        $rootQuestions = $this->allQuestions
            ->whereNull('parent_question_id')
            ->values();

        $currentRoot = $rootQuestions->get($this->currentStep);
        if (!$currentRoot) {
            return collect();
        }

        // Include the root question plus any visible children
        $questions = collect([$currentRoot]);

        // Add branching children that belong to this root
        $children = $this->allQuestions
            ->where('parent_question_id', $currentRoot->id)
            ->filter(fn(Question $q) => $q->isVisibleGivenAnswers($this->answers));

        return $questions->merge($children);
    }

    /**
     * Update a BMI calculator step.
     */
    public function updateBmi(int $questionId): void
    {
        if ($this->heightCm && $this->weightKg && $this->heightCm > 0) {
            $heightM = $this->heightCm / 100;
            $this->calculatedBmi = round($this->weightKg / ($heightM * $heightM), 1);
            $this->answers[$questionId] = [
                'height_cm' => $this->heightCm,
                'weight_kg' => $this->weightKg,
                'bmi'       => $this->calculatedBmi,
            ];
            $this->saveToSession();
        }
    }

    /**
     * Move to next step — validates current step first.
     */
    public function nextStep(): void
    {
        $this->stepErrors = [];

        // Validate mandatory questions on current step
        foreach ($this->currentStepQuestions as $question) {
            if ($question->mandatory && empty($this->answers[$question->id])) {
                $this->stepErrors[$question->id] = 'This question is required.';
            }
        }

        if (!empty($this->stepErrors)) {
            return;
        }

        $this->saveToSession();

        if ($this->currentStep < $this->totalSteps - 1) {
            $this->currentStep++;
        } else {
            $this->onFinalStep();
        }
    }

    /**
     * Move to previous step.
     */
    public function prevStep(): void
    {
        if ($this->currentStep > 0) {
            $this->currentStep--;
        }
        $this->saveToSession();
    }

    /**
     * Called when patient reaches the final step and clicks submit/continue.
     */
    private function onFinalStep(): void
    {
        if (!auth()->check()) {
            // Guest patient — show the gate (register/login prompt)
            $this->showGate = true;
            $this->saveToSession();
        } else {
            // Logged-in patient — submit immediately
            $this->submitConsultation();
        }
    }

    /**
     * Submit the consultation for a logged-in patient.
     */
    public function submitConsultation(): void
    {
        if (!auth()->check()) {
            $this->showGate = true;
            return;
        }

        $consultation = app(ConsultationService::class)->submit(
            auth()->user(),
            $this->product,
            $this->answers
        );

        app(ConsultationDraftService::class)->clearSession(request());
        $this->submitted = true;

        $this->dispatch('consultation-submitted', consultationId: $consultation->id);
        $this->redirect(route('patient.consultation.submitted', $consultation));
    }

    /**
     * Save current state to session (called on every answer change for auto-save).
     */
    public function saveToSession(): void
    {
        app(ConsultationDraftService::class)->saveToSession(
            request(),
            $this->product->id,
            $this->questionnaire?->id,
            $this->answers,
            $this->currentStep
        );
    }

    /**
     * Called when an answer changes — auto-save and re-evaluate visible questions.
     */
    public function updatedAnswers(): void
    {
        $this->saveToSession();
    }

    public function getProgressPercentageProperty(): int
    {
        if ($this->totalSteps === 0) return 0;
        return (int) round((($this->currentStep) / $this->totalSteps) * 100);
    }

    public function render()
    {
        return view('livewire.consultation.consultation-form');
    }
}
