<div class="max-w-2xl mx-auto" x-data>

    {{-- ── Header ──────────────────────────────────────────────────────────── --}}
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-3">
            <span class="badge-lilac text-xs">{{ $product->treatment->category->name ?? '' }}</span>
            @if ($product->requires_cold_chain)
                <span class="badge-plum text-xs">Cold chain</span>
            @endif
        </div>
        <h1 class="font-display text-display-sm font-bold text-plum-800">
            {{ $product->name }} Consultation
        </h1>
        <p class="text-plum-500 mt-2 text-sm leading-relaxed">
            This short consultation helps our prescribers assess whether {{ $product->name }} is right for you.
            All information is reviewed by a UK-registered prescriber.
        </p>
    </div>

    {{-- ── Progress bar ─────────────────────────────────────────────────────── --}}
    @if ($totalSteps > 1 && !$showGate && !$submitted)
        <div class="mb-8">
            <div class="flex items-center justify-between text-xs text-plum-400 mb-2">
                <span>Step {{ $currentStep + 1 }} of {{ $totalSteps }}</span>
                <span>{{ $this->progressPercentage }}% complete</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: {{ $this->progressPercentage }}%"></div>
            </div>
        </div>
    @endif

    {{-- ── Submission confirmation ───────────────────────────────────────────── --}}
    @if ($submitted)
        <div class="card p-8 text-center animate-fade-in">
            <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h2 class="font-display text-2xl font-bold text-plum-800 mb-2">Consultation submitted</h2>
            <p class="text-plum-500 text-sm">Your consultation is now with our prescribers. We'll email you once it's been reviewed, usually within a few hours.</p>
        </div>

    {{-- ── Guest gate ────────────────────────────────────────────────────────── --}}
    @elseif ($showGate)
        <div class="card overflow-hidden animate-fade-in">
            <div class="bg-plum-800 px-8 py-6 text-center">
                <div class="w-12 h-12 rounded-xl bg-lilac-500/20 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-lilac-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h2 class="font-display text-2xl font-bold text-lilac-400 mb-1">Almost there</h2>
                <p class="text-lilac-500/70 text-sm">Your answers have been saved. Create an account or sign in to submit your consultation.</p>
            </div>
            <div class="p-8">
                <div class="grid sm:grid-cols-2 gap-4">
                    <a href="{{ route('register') }}" class="btn-primary justify-center">
                        Create account &amp; submit
                    </a>
                    <a href="{{ route('login') }}" class="btn-secondary justify-center">
                        Sign in &amp; submit
                    </a>
                </div>
                <p class="text-center text-xs text-plum-400 mt-4">
                    Your answers are saved for 48 hours.
                </p>
                <button wire:click="$set('showGate', false)"
                        class="mt-3 w-full text-center text-xs text-plum-400 hover:text-plum-600 transition-colors">
                    ← Go back and review my answers
                </button>
            </div>
        </div>

    {{-- ── Question steps ────────────────────────────────────────────────────── --}}
    @else
        <div class="space-y-6 animate-fade-in">

            @foreach ($this->currentStepQuestions as $question)
                <div class="card p-6 @if($loop->first) ring-2 ring-plum-200 @endif">

                    {{-- Question text --}}
                    <p class="font-medium text-plum-800 mb-1 leading-snug">
                        {{ $question->question_text }}
                        @if ($question->mandatory)
                            <span class="text-red-500 ml-0.5">*</span>
                        @endif
                    </p>

                    {{-- Step errors --}}
                    @if (isset($stepErrors[$question->id]))
                        <p class="text-sm text-red-600 mb-3">{{ $stepErrors[$question->id] }}</p>
                    @endif

                    {{-- ── Question type rendering ─────────────────────────── --}}

                    {{-- Yes / No --}}
                    @if ($question->type === 'yes_no')
                        <div class="flex gap-3 mt-3">
                            @foreach (['Yes' => 'yes', 'No' => 'no'] as $label => $value)
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio"
                                           wire:model.live="answers.{{ $question->id }}"
                                           value="{{ $value }}"
                                           class="sr-only peer">
                                    <div class="border-2 rounded-xl px-4 py-3 text-center text-sm font-medium transition-all
                                                peer-checked:border-plum-800 peer-checked:bg-plum-800 peer-checked:text-lilac-500
                                                border-plum-200 text-plum-600 hover:border-plum-400">
                                        {{ $label }}
                                    </div>
                                </label>
                            @endforeach
                        </div>

                    {{-- Multiple choice single --}}
                    @elseif ($question->type === 'multiple_choice_single')
                        <div class="space-y-2 mt-3">
                            @foreach ($question->options ?? [] as $option)
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="radio"
                                           wire:model.live="answers.{{ $question->id }}"
                                           value="{{ $option }}"
                                           class="border-plum-300 text-plum-800 focus:ring-plum-800">
                                    <span class="text-sm text-plum-700 group-hover:text-plum-900">{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>

                    {{-- Multiple choice multi-select --}}
                    @elseif ($question->type === 'multiple_choice_multi')
                        <div class="space-y-2 mt-3">
                            @foreach ($question->options ?? [] as $option)
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox"
                                           wire:model.live="answers.{{ $question->id }}"
                                           value="{{ $option }}"
                                           class="rounded border-plum-300 text-plum-800 focus:ring-plum-800">
                                    <span class="text-sm text-plum-700 group-hover:text-plum-900">{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>

                    {{-- Free text --}}
                    @elseif ($question->type === 'free_text')
                        <textarea wire:model.blur="answers.{{ $question->id }}"
                                  rows="3"
                                  placeholder="Please provide details..."
                                  class="input mt-3 resize-none"></textarea>

                    {{-- Numeric --}}
                    @elseif ($question->type === 'numeric')
                        <input type="number"
                               wire:model.blur="answers.{{ $question->id }}"
                               @if($question->numeric_min) min="{{ $question->numeric_min }}" @endif
                               @if($question->numeric_max) max="{{ $question->numeric_max }}" @endif
                               step="any"
                               class="input mt-3 max-w-xs">

                    {{-- BMI Calculator --}}
                    @elseif ($question->type === 'bmi_calculator')
                        <div class="mt-3 space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="label text-xs">Height (cm)</label>
                                    <input type="number" wire:model.blur="heightCm"
                                           wire:change="updateBmi({{ $question->id }})"
                                           min="100" max="250" step="0.1"
                                           placeholder="175"
                                           class="input">
                                </div>
                                <div>
                                    <label class="label text-xs">Weight (kg)</label>
                                    <input type="number" wire:model.blur="weightKg"
                                           wire:change="updateBmi({{ $question->id }})"
                                           min="30" max="400" step="0.1"
                                           placeholder="75"
                                           class="input">
                                </div>
                            </div>
                            @if ($calculatedBmi)
                                <div class="bg-lilac-50 border border-lilac-200 rounded-xl px-4 py-3 text-center">
                                    <p class="text-xs text-plum-500 mb-1">Your BMI</p>
                                    <p class="font-display text-3xl font-bold text-plum-800">{{ $calculatedBmi }}</p>
                                </div>
                            @endif
                        </div>

                    {{-- Medical history checklist --}}
                    @elseif ($question->type === 'medical_history_checklist')
                        <div class="mt-3 space-y-2">
                            @foreach ($question->options ?? [] as $condition)
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox"
                                           wire:model.live="answers.{{ $question->id }}"
                                           value="{{ $condition }}"
                                           class="rounded border-plum-300 text-plum-800 focus:ring-plum-800">
                                    <span class="text-sm text-plum-700 group-hover:text-plum-900">{{ $condition }}</span>
                                </label>
                            @endforeach
                            <label class="flex items-center gap-3 cursor-pointer group mt-2 pt-2 border-t border-plum-100">
                                <input type="checkbox"
                                       wire:model.live="answers.{{ $question->id }}"
                                       value="none"
                                       class="rounded border-plum-300 text-plum-800 focus:ring-plum-800">
                                <span class="text-sm font-medium text-plum-800">None of the above</span>
                            </label>
                        </div>

                    {{-- Medications list --}}
                    @elseif ($question->type === 'medications_list')
                        <div class="mt-3">
                            <p class="text-xs text-plum-500 mb-2">List any prescription medications, over-the-counter medicines, supplements or herbal remedies you are currently taking.</p>
                            <textarea wire:model.blur="answers.{{ $question->id }}"
                                      rows="4"
                                      placeholder="e.g. Metformin 500mg, Aspirin 75mg, Vitamin D 1000IU..."
                                      class="input resize-none"></textarea>
                            <p class="text-xs text-plum-400 mt-1">If you are not taking any medicines, please write 'None'.</p>
                        </div>
                    @endif

                </div>
            @endforeach

            {{-- ── Navigation ────────────────────────────────────────────────── --}}
            <div class="flex items-center gap-3 pt-2">
                @if ($currentStep > 0)
                    <button wire:click="prevStep" class="btn-secondary">
                        ← Back
                    </button>
                @endif

                <div class="flex-1"></div>

                @if ($currentStep < $totalSteps - 1)
                    <button wire:click="nextStep" class="btn-primary">
                        Continue →
                    </button>
                @else
                    {{-- Final step --}}
                    @auth
                        <button wire:click="submitConsultation" class="btn-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Submit consultation
                        </button>
                    @else
                        <button wire:click="nextStep" class="btn-primary">
                            Continue to submit →
                        </button>
                    @endauth
                @endif
            </div>

            {{-- Loading overlay --}}
            <div wire:loading wire:target="nextStep,prevStep,submitConsultation"
                 class="fixed inset-0 bg-white/60 backdrop-blur-sm flex items-center justify-center z-50">
                <div class="w-8 h-8 border-3 border-plum-800 border-t-transparent rounded-full animate-spin"></div>
            </div>

        </div>
    @endif

    {{-- ── Reassurance strip ─────────────────────────────────────────────────── --}}
    <div class="mt-8 grid grid-cols-3 gap-4">
        @foreach ([
            ['icon' => 'shield', 'text' => 'Reviewed by UK prescribers'],
            ['icon' => 'lock',   'text' => 'Your data is secure & private'],
            ['icon' => 'clock',  'text' => 'Response within a few hours'],
        ] as $item)
            <div class="text-center">
                <div class="w-8 h-8 rounded-lg bg-lilac-100 flex items-center justify-center mx-auto mb-2">
                    @if ($item['icon'] === 'shield')
                        <svg class="w-4 h-4 text-plum-700" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    @elseif ($item['icon'] === 'lock')
                        <svg class="w-4 h-4 text-plum-700" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                    @else
                        <svg class="w-4 h-4 text-plum-700" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                    @endif
                </div>
                <p class="text-xs text-plum-500">{{ $item['text'] }}</p>
            </div>
        @endforeach
    </div>

</div>
