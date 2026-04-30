<x-patient-layout>
    <x-slot name="title">Consultation — {{ $consultation->product->name }}</x-slot>
    <x-slot name="pageTitle">{{ $consultation->product->name }}</x-slot>

    <div class="grid lg:grid-cols-3 gap-6">

        {{-- ── Main panel ───────────────────────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Status card --}}
            <div class="card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-display text-lg font-bold text-plum-800">Consultation status</h2>
                    <x-patient.consultation-status-badge :status="$consultation->status" />
                </div>

                {{-- Timeline --}}
                <div class="space-y-4">
                    @php
                        $steps = [
                            ['key' => 'submitted',        'label' => 'Consultation submitted',         'done' => in_array($consultation->status, ['submitted','under_review','approved','rejected','flagged'])],
                            ['key' => 'under_review',     'label' => 'Under prescriber review',        'done' => in_array($consultation->status, ['under_review','approved','rejected'])],
                            ['key' => 'approved',         'label' => 'Prescription approved',          'done' => $consultation->status === 'approved'],
                            ['key' => 'order',            'label' => 'Order placed & dispensed',       'done' => $consultation->prescription?->status === 'dispensed'],
                        ];
                    @endphp
                    @foreach ($steps as $step)
                        <div class="flex items-center gap-3">
                            <div class="w-6 h-6 rounded-full shrink-0 flex items-center justify-center
                                {{ $step['done'] ? 'bg-plum-800' : 'bg-plum-100' }}">
                                @if ($step['done'])
                                    <svg class="w-3 h-3 text-lilac-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @endif
                            </div>
                            <p class="text-sm {{ $step['done'] ? 'text-plum-800 font-medium' : 'text-plum-400' }}">
                                {{ $step['label'] }}
                            </p>
                        </div>
                    @endforeach
                </div>

                {{-- CTA if approved --}}
                @if ($consultation->status === 'approved' && !$consultation->prescription?->order)
                    <div class="mt-6 pt-5 border-t border-plum-100">
                        <a href="{{ route('patient.consultation.checkout-ready', $consultation->product) }}"
                           class="btn-primary w-full justify-center">
                            Complete your order →
                        </a>
                    </div>
                @endif

                {{-- Rejection reason --}}
                @if ($consultation->status === 'rejected' && $consultation->rejection)
                    <div class="mt-5 pt-5 border-t border-plum-100">
                        <p class="text-sm font-medium text-plum-700 mb-1">Reason for outcome</p>
                        <p class="text-sm text-plum-500">{{ $consultation->rejection->reason }}</p>
                    </div>
                @endif
            </div>

            {{-- Questionnaire answers --}}
            @if ($consultation->questionnaire && $consultation->answers)
                <div class="card p-6">
                    <h2 class="font-display text-lg font-bold text-plum-800 mb-4">Your consultation answers</h2>
                    <div class="space-y-4">
                        @foreach ($consultation->questionnaire->questions as $question)
                            @php $answer = $consultation->answers[$question->id] ?? null; @endphp
                            @if ($answer !== null)
                                <div class="border-b border-plum-50 pb-4 last:border-0 last:pb-0">
                                    <p class="text-xs font-medium text-plum-500 mb-1">{{ $question->question_text }}</p>
                                    @if (is_array($answer))
                                        @if (isset($answer['bmi']))
                                            <p class="text-sm text-plum-800">Height: {{ $answer['height_cm'] }}cm · Weight: {{ $answer['weight_kg'] }}kg · BMI: <strong>{{ $answer['bmi'] }}</strong></p>
                                        @else
                                            <p class="text-sm text-plum-800">{{ implode(', ', $answer) }}</p>
                                        @endif
                                    @else
                                        <p class="text-sm text-plum-800 capitalize">{{ $answer }}</p>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Messages --}}
            <div class="card">
                <div class="px-6 py-4 border-b border-plum-100 flex items-center justify-between">
                    <h2 class="font-display text-lg font-bold text-plum-800">Messages</h2>
                </div>
                <div class="px-6 py-4 space-y-4 max-h-64 overflow-y-auto">
                    @forelse ($consultation->messages->where('internal_only', false) as $message)
                        <div class="flex gap-3 {{ $message->isFromPatient() ? 'flex-row-reverse' : '' }}">
                            <div class="w-7 h-7 rounded-full shrink-0 flex items-center justify-center text-xs font-bold
                                {{ $message->isFromPatient() ? 'bg-plum-800 text-lilac-400' : 'bg-lilac-200 text-plum-800' }}">
                                {{ $message->isFromPatient() ? 'You' : 'P&C' }}
                            </div>
                            <div class="max-w-xs rounded-xl px-4 py-2.5
                                {{ $message->isFromPatient() ? 'bg-plum-800 text-lilac-400 rounded-tr-sm' : 'bg-plum-50 text-plum-800 rounded-tl-sm' }}">
                                <p class="text-sm">{{ $message->body }}</p>
                                <p class="text-xs opacity-60 mt-1">{{ $message->created_at->format('j M, g:ia') }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-plum-400 text-center py-4">No messages yet.</p>
                    @endforelse
                </div>
                <div class="px-6 py-4 border-t border-plum-100">
                    <form method="POST" action="{{ route('patient.messages.send', $consultation) }}" class="flex gap-3">
                        @csrf
                        <input type="text" name="body" placeholder="Type a message…"
                               class="input flex-1 py-2.5 text-sm"
                               maxlength="2000" required>
                        <button type="submit" class="btn-primary btn-sm shrink-0">Send</button>
                    </form>
                </div>
            </div>

        </div>

        {{-- ── Sidebar ───────────────────────────────────────────────────────── --}}
        <div class="space-y-4">

            {{-- Product info --}}
            <div class="card p-5">
                <h3 class="font-display text-base font-bold text-plum-800 mb-3">Treatment</h3>
                <p class="text-sm font-medium text-plum-800">{{ $consultation->product->name }}</p>
                <p class="text-xs text-plum-400 mt-0.5">{{ $consultation->product->treatment->category->name ?? '' }}</p>
                @if ($consultation->product->product_type)
                    <span class="badge-lilac mt-2 inline-flex">{{ $consultation->product->product_type }}</span>
                @endif
            </div>

            {{-- Prescription link --}}
            @if ($consultation->prescription)
                <div class="card p-5">
                    <h3 class="font-display text-base font-bold text-plum-800 mb-3">Prescription</h3>
                    <p class="text-xs text-plum-400 mb-1">{{ $consultation->prescription->prescription_number }}</p>
                    <span class="badge-lilac capitalize">{{ str_replace('_', ' ', $consultation->prescription->status) }}</span>
                    <a href="{{ route('patient.prescriptions.show', $consultation->prescription) }}"
                       class="btn-secondary btn-sm w-full justify-center mt-3 text-xs">
                        View prescription
                    </a>
                </div>
            @endif

            {{-- Dates --}}
            <div class="card p-5 text-xs space-y-2">
                <div class="flex justify-between">
                    <span class="text-plum-400">Submitted</span>
                    <span class="text-plum-700 font-medium">{{ $consultation->submitted_at?->format('j M Y') ?? '—' }}</span>
                </div>
                @if ($consultation->reviewed_at)
                    <div class="flex justify-between">
                        <span class="text-plum-400">Reviewed</span>
                        <span class="text-plum-700 font-medium">{{ $consultation->reviewed_at->format('j M Y') }}</span>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-patient-layout>
