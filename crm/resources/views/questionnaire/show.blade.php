<x-layouts.app>
    <x-slot name="pageTitle">{{ $questionnaire->name }}</x-slot>

    <div class="space-y-5 animate-fade-in"
        x-data="{
            showAddQuestion: false,
            editQuestion: null,
            newType: 'yes_no',
            hasContraindication: false,
            isBranched: false,
        }">

        <a href="{{ route('questionnaire.index') }}" class="text-sm font-medium text-lilac-600 hover:text-lilac-800">← Questionnaires</a>

        @if($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
            </div>
        @endif

        {{-- Questionnaire meta --}}
        <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm">
            <form method="POST" action="{{ route('questionnaire.update', $questionnaire) }}" class="space-y-3">
                @csrf @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2 sm:col-span-1">
                        <label class="mb-1 block text-xs font-medium text-plum-600">Name</label>
                        <input type="text" name="name" value="{{ old('name', $questionnaire->name) }}" required
                            class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-plum-600">Assign to products</label>
                        <select name="product_ids[]" multiple
                            class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500 h-20">
                            @foreach($allProducts as $product)
                                <option value="{{ $product->id }}"
                                    {{ $questionnaire->products->contains($product->id) ? 'selected' : '' }}>
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="mb-1 block text-xs font-medium text-plum-600">Description</label>
                        <input type="text" name="description" value="{{ old('description', $questionnaire->description) }}"
                            class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm text-plum-700">
                        <input type="checkbox" name="active" value="1" {{ $questionnaire->active ? 'checked' : '' }}
                            class="size-4 rounded border-plum-300 text-lilac-500">
                        Active (visible to patients)
                    </label>
                    <button type="submit" class="rounded-xl bg-plum-800 px-4 py-2 text-sm font-medium text-lilac-200 hover:bg-plum-900">
                        Save settings
                    </button>
                </div>
            </form>
        </div>

        {{-- Questions list --}}
        <div class="rounded-2xl border border-plum-100 bg-white shadow-plum-sm">
            <div class="flex items-center justify-between border-b border-plum-50 px-5 py-4">
                <h3 class="text-sm font-semibold text-plum-700">
                    Questions <span class="text-plum-400 font-normal">({{ $questionnaire->questions->count() }})</span>
                </h3>
                <button @click="showAddQuestion = true"
                    class="rounded-xl bg-plum-800 px-4 py-2 text-xs font-medium text-lilac-200 hover:bg-plum-900">
                    + Add Question
                </button>
            </div>

            @if($questionnaire->questions->isEmpty())
                <div class="px-5 py-10 text-center text-sm text-plum-400">No questions yet. Add one above.</div>
            @else
                <div class="divide-y divide-plum-50">
                    @foreach($questionnaire->questions as $question)
                        <div class="flex items-start gap-4 px-5 py-4"
                            x-data="{ editing: false }">
                            <div class="flex size-7 shrink-0 items-center justify-center rounded-full bg-plum-100 text-xs font-bold text-plum-600 mt-0.5">
                                {{ $loop->iteration }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-plum-800">{{ $question->question_text }}</p>
                                        <div class="mt-1 flex flex-wrap gap-1">
                                            <span class="rounded-full bg-plum-100 px-2 py-0.5 text-[10px] font-medium text-plum-600">
                                                {{ \App\Models\Question::TYPES[$question->type] ?? $question->type }}
                                            </span>
                                            @if($question->mandatory)
                                                <span class="rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-semibold text-red-600">Required</span>
                                            @endif
                                            @if($question->has_contraindication)
                                                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-700">Contraindication check</span>
                                            @endif
                                            @if($question->parent_question_id)
                                                <span class="rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-medium text-blue-600">
                                                    Branched (shows if Q{{ $questionnaire->questions->search(fn($q) => $q->id === $question->parent_question_id) + 1 }} = "{{ $question->parent_trigger_value }}")
                                                </span>
                                            @endif
                                            @if($question->type === \App\Models\Question::TYPE_BMI && ($question->bmi_min || $question->bmi_max))
                                                <span class="rounded-full bg-purple-100 px-2 py-0.5 text-[10px] font-medium text-purple-600">
                                                    BMI range: {{ $question->bmi_min }}–{{ $question->bmi_max }}
                                                </span>
                                            @endif
                                            @if($question->options)
                                                <span class="rounded-full bg-plum-50 px-2 py-0.5 text-[10px] text-plum-400">
                                                    {{ count($question->options) }} options
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex shrink-0 gap-2">
                                        <button @click="editing = !editing"
                                            class="rounded-lg border border-plum-200 px-3 py-1.5 text-xs text-plum-600 hover:bg-plum-50">
                                            Edit
                                        </button>
                                        <form method="POST" action="{{ route('questionnaire.questions.destroy', [$questionnaire, $question]) }}"
                                              onsubmit="return confirm('Delete this question?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-red-200 px-2.5 py-1.5 text-xs text-red-500 hover:bg-red-50">✕</button>
                                        </form>
                                    </div>
                                </div>

                                {{-- Inline edit form --}}
                                <div x-show="editing" x-cloak class="mt-3 rounded-xl border border-plum-100 bg-plum-50/60 p-4">
                                    @include('questionnaire._question-form', ['q' => $question, 'questionnaire' => $questionnaire, 'isEdit' => true])
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Add question modal --}}
        <div x-show="showAddQuestion" class="fixed inset-0 z-50 flex items-center justify-center bg-plum-950/50 p-4 overflow-y-auto" x-cloak>
            <div class="my-auto w-full max-w-xl rounded-2xl bg-white p-6 shadow-plum-lg">
                <h3 class="mb-4 text-base font-semibold text-plum-800">Add Question</h3>
                @include('questionnaire._question-form', ['q' => null, 'questionnaire' => $questionnaire, 'isEdit' => false])
                <button type="button" @click="showAddQuestion = false"
                    class="mt-2 w-full rounded-xl border border-plum-200 py-2 text-sm text-plum-500">Cancel</button>
            </div>
        </div>

    </div>
</x-layouts.app>
