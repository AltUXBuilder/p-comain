{{--
  Partial: questionnaire/_question-form.blade.php
  Used by:
    - questionnaire/show.blade.php  (Add Question modal + inline edit)
  Variables:
    $q             — Question|null  (null = add form, model = edit form)
    $questionnaire — Questionnaire
    $isEdit        — bool
--}}

@php
    $action   = $isEdit
        ? route('questionnaire.questions.update', [$questionnaire, $q])
        : route('questionnaire.questions.store', $questionnaire);
    $method   = $isEdit ? 'PUT' : 'POST';
    $qType    = old('type', $q?->type ?? 'yes_no');
@endphp

<form method="POST" action="{{ $action }}"
      x-data="{
          qtype: '{{ $qType }}',
          hasContra: {{ ($q?->has_contraindication ?? false) ? 'true' : 'false' }},
          isBranched: {{ ($q?->parent_question_id ?? false) ? 'true' : 'false' }},
          contraRules: {{ json_encode($q?->contraindication_rules ?? []) }},
          addRule() {
              this.contraRules.push({ trigger_value: '', action: 'flag', message: '' });
          },
          removeRule(i) { this.contraRules.splice(i, 1); }
      }"
      class="space-y-4">
    @csrf
    @if($isEdit) @method('PUT') @endif

    {{-- Question text --}}
    <div>
        <label class="mb-1 block text-sm font-medium text-plum-700">Question text <span class="text-red-500">*</span></label>
        <textarea name="question_text" rows="2" required
            class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500"
            >{{ old('question_text', $q?->question_text) }}</textarea>
    </div>

    <div class="grid grid-cols-2 gap-3">
        {{-- Type --}}
        <div>
            <label class="mb-1 block text-xs font-medium text-plum-600">Question type</label>
            <select name="type" x-model="qtype"
                class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                @foreach(\App\Models\Question::TYPES as $key => $label)
                    <option value="{{ $key }}" {{ $qType === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        {{-- Sort order --}}
        <div>
            <label class="mb-1 block text-xs font-medium text-plum-600">Sort order</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $q?->sort_order ?? '') }}"
                min="1" step="1"
                class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500"
                placeholder="Auto">
        </div>
    </div>

    {{-- Options (for MC types) --}}
    <div x-show="['multiple_choice_single','multiple_choice_multi','medical_history_checklist'].includes(qtype)" x-cloak
         x-data="{ opts: {{ json_encode($q?->options ?? ['']) }} }">
        <label class="mb-1 block text-xs font-medium text-plum-600">Answer options</label>
        <template x-for="(opt, i) in opts" :key="i">
            <div class="mb-1.5 flex gap-2">
                <input type="text" :name="`options[${i}]`" x-model="opts[i]"
                    placeholder="Option text"
                    class="flex-1 rounded-xl border-plum-200 py-1.5 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                <button type="button" @click="opts.splice(i,1)" class="text-red-400 hover:text-red-600">✕</button>
            </div>
        </template>
        <button type="button" @click="opts.push('')"
            class="mt-1 rounded-lg border border-plum-200 px-3 py-1 text-xs font-medium text-plum-600 hover:bg-plum-50">
            + Add option
        </button>
    </div>

    {{-- Numeric range --}}
    <div x-show="qtype === 'numeric'" x-cloak class="grid grid-cols-2 gap-3">
        <div>
            <label class="mb-1 block text-xs font-medium text-plum-600">Min value</label>
            <input type="number" name="numeric_min" value="{{ old('numeric_min', $q?->numeric_min) }}"
                step="any" class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-plum-600">Max value</label>
            <input type="number" name="numeric_max" value="{{ old('numeric_max', $q?->numeric_max) }}"
                step="any" class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
        </div>
    </div>

    {{-- BMI thresholds --}}
    <div x-show="qtype === 'bmi_calculator'" x-cloak class="space-y-3">
        <div class="grid grid-cols-3 gap-3">
            <div>
                <label class="mb-1 block text-xs font-medium text-plum-600">BMI minimum</label>
                <input type="number" name="bmi_min" value="{{ old('bmi_min', $q?->bmi_min) }}"
                    step="0.1" class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-plum-600">BMI maximum</label>
                <input type="number" name="bmi_max" value="{{ old('bmi_max', $q?->bmi_max) }}"
                    step="0.1" class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-plum-600">Out-of-range action</label>
                <select name="bmi_action" class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                    <option value="">— None —</option>
                    <option value="flag"   {{ ($q?->bmi_action === 'flag')   ? 'selected' : '' }}>Flag for review</option>
                    <option value="reject" {{ ($q?->bmi_action === 'reject') ? 'selected' : '' }}>Auto-reject</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Branching --}}
    <div class="rounded-xl border border-plum-100 bg-plum-50/50 p-3 space-y-2">
        <label class="flex items-center gap-2 cursor-pointer text-sm text-plum-700">
            <input type="checkbox" x-model="isBranched" class="size-4 rounded border-plum-300 text-lilac-500">
            Branch from a parent question
        </label>
        <div x-show="isBranched" x-cloak class="grid grid-cols-2 gap-3">
            <div>
                <label class="mb-1 block text-xs font-medium text-plum-600">Show this question when…</label>
                <select name="parent_question_id"
                    class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                    <option value="">— Select parent question —</option>
                    @foreach($questionnaire->questions as $parentQ)
                        @if(! $isEdit || $parentQ->id !== $q?->id)
                            <option value="{{ $parentQ->id }}"
                                {{ old('parent_question_id', $q?->parent_question_id) == $parentQ->id ? 'selected' : '' }}>
                                Q{{ $loop->iteration }}: {{ Str::limit($parentQ->question_text, 50) }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-plum-600">Answer that triggers this question</label>
                <input type="text" name="parent_trigger_value"
                    value="{{ old('parent_trigger_value', $q?->parent_trigger_value) }}"
                    placeholder="e.g. yes, Female, 1"
                    class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
            </div>
        </div>
    </div>

    {{-- Contraindication rules --}}
    <div class="rounded-xl border border-amber-100 bg-amber-50/40 p-3 space-y-2">
        <label class="flex items-center gap-2 cursor-pointer text-sm text-plum-700">
            <input type="checkbox" x-model="hasContra" name="has_contraindication" value="1"
                {{ ($q?->has_contraindication ?? false) ? 'checked' : '' }}
                class="size-4 rounded border-plum-300 text-amber-500">
            Add contraindication rule(s)
        </label>
        <div x-show="hasContra" x-cloak class="space-y-2">
            <template x-for="(rule, i) in contraRules" :key="i">
                <div class="grid grid-cols-3 gap-2 items-end">
                    <div>
                        <label class="mb-0.5 block text-[10px] font-medium text-plum-500">If answer is</label>
                        <input type="text" :name="`contraindication_rules[${i}][trigger_value]`"
                            x-model="rule.trigger_value" placeholder="e.g. yes, >60, heart_disease"
                            class="block w-full rounded-lg border-plum-200 py-1.5 px-2 text-xs focus:border-lilac-500 focus:ring-lilac-500">
                    </div>
                    <div>
                        <label class="mb-0.5 block text-[10px] font-medium text-plum-500">Action</label>
                        <select :name="`contraindication_rules[${i}][action]`" x-model="rule.action"
                            class="block w-full rounded-lg border-plum-200 py-1.5 px-2 text-xs focus:border-lilac-500 focus:ring-lilac-500">
                            <option value="flag">Flag for review</option>
                            <option value="reject">Auto-reject</option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <div class="flex-1">
                            <label class="mb-0.5 block text-[10px] font-medium text-plum-500">Message shown to prescriber</label>
                            <input type="text" :name="`contraindication_rules[${i}][message]`"
                                x-model="rule.message" placeholder="Clinical reason…"
                                class="block w-full rounded-lg border-plum-200 py-1.5 px-2 text-xs focus:border-lilac-500 focus:ring-lilac-500">
                        </div>
                        <button type="button" @click="removeRule(i)" class="text-red-400 hover:text-red-600 mt-4">✕</button>
                    </div>
                </div>
            </template>
            <button type="button" @click="addRule()"
                class="rounded-lg border border-amber-200 bg-white px-3 py-1 text-xs font-medium text-amber-700 hover:bg-amber-50">
                + Add rule
            </button>
        </div>
    </div>

    {{-- Mandatory toggle --}}
    <label class="flex items-center gap-2 text-sm text-plum-700">
        <input type="checkbox" name="mandatory" value="1" {{ ($q?->mandatory ?? false) ? 'checked' : '' }}
            class="size-4 rounded border-plum-300 text-lilac-500">
        Required question (patient cannot skip)
    </label>

    <div class="flex justify-end gap-2 pt-1">
        <button type="submit"
            class="rounded-xl bg-plum-800 px-5 py-2 text-sm font-semibold text-lilac-200 hover:bg-plum-900">
            {{ $isEdit ? 'Save changes' : 'Add question' }}
        </button>
    </div>
</form>
