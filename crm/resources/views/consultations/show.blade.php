<x-layouts.app>
    <x-slot name="pageTitle">Consultation Review</x-slot>

    <div class="space-y-5 animate-fade-in" x-data="{ showRejectModal: false, showFlagModal: false }">

        {{-- Breadcrumb --}}
        <div>
            <a href="{{ route('consultations.queue') }}" class="text-sm font-medium text-lilac-600 hover:text-lilac-800">← Back to queue</a>
        </div>

        {{-- Patient banner --}}
        @if($consultation->patient?->do_not_treat)
            <div class="rounded-2xl border-2 border-red-300 bg-red-50 px-5 py-4">
                <p class="font-semibold text-red-800">⚠ DO NOT TREAT — Clinical actions blocked</p>
                <p class="text-sm text-red-600">{{ $consultation->patient->do_not_treat_reason }}</p>
            </div>
        @endif

        <div class="grid gap-5 lg:grid-cols-3">

            {{-- Main: questionnaire answers --}}
            <div class="space-y-4 lg:col-span-2">

                {{-- Header --}}
                <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-plum-400">Consultation</p>
                            <h2 class="mt-1 text-lg font-semibold text-plum-800">
                                {{ $consultation->product?->name ?? 'Unknown product' }}
                            </h2>
                            <p class="mt-0.5 text-sm text-plum-500">
                                Submitted {{ $consultation->created_at->format('d M Y, H:i') }}
                                · {{ $consultation->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold
                            {{ match($consultation->status) {
                                'awaiting_review' => 'bg-plum-100 text-plum-700',
                                'approved'        => 'bg-green-100 text-green-700',
                                'rejected'        => 'bg-red-100 text-red-700',
                                'flagged'         => 'bg-amber-100 text-amber-700',
                                default           => 'bg-plum-100 text-plum-600',
                            } }}
                        ">{{ ucfirst(str_replace('_', ' ', $consultation->status)) }}</span>
                    </div>
                </div>

                {{-- Questionnaire answers --}}
                <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm">
                    <h3 class="mb-4 text-sm font-semibold text-plum-700">Patient Responses</h3>

                    @if($answers && count($answers))
                        <dl class="space-y-4">
                            @foreach($answers as $questionId => $answer)
                                @php $question = $questions->get($questionId); @endphp
                                <div class="rounded-xl bg-plum-50/50 px-4 py-3">
                                    <dt class="text-xs font-medium text-plum-500">
                                        {{ $question?->body ?? "Question #{$questionId}" }}
                                        @if($question?->is_contraindication)
                                            <span class="ml-1 rounded-full bg-red-100 px-1.5 py-0.5 text-[9px] font-semibold uppercase text-red-600">Contraindication check</span>
                                        @endif
                                    </dt>
                                    <dd class="mt-1 text-sm font-medium text-plum-800">
                                        @if(is_array($answer))
                                            {{ implode(', ', $answer) }}
                                        @else
                                            {{ $answer ?: '—' }}
                                        @endif
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    @else
                        <p class="text-sm text-plum-400">No questionnaire answers recorded.</p>
                    @endif
                </div>

            </div>

            {{-- Sidebar: patient info + actions --}}
            <div class="space-y-4">

                {{-- Patient card --}}
                <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm">
                    <h3 class="mb-3 text-sm font-semibold text-plum-700">Patient</h3>
                    @php $patient = $consultation->patient; @endphp
                    @if($patient)
                        <dl class="space-y-2 text-sm">
                            <div>
                                <dt class="text-xs text-plum-400">Name</dt>
                                <dd class="font-medium text-plum-800">
                                    <a href="{{ route('patients.show', $patient) }}" class="hover:text-lilac-700">{{ $patient->full_name }}</a>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs text-plum-400">Date of birth</dt>
                                <dd class="text-plum-700">{{ $patient->date_of_birth?->format('d M Y') ?? '—' }} @if($patient->age)({{ $patient->age }}y)@endif</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-plum-400">Email</dt>
                                <dd class="truncate text-plum-700">{{ $patient->email }}</dd>
                            </div>
                            @if($patient->risk_flagged)
                                <div class="rounded-lg bg-amber-50 px-3 py-2">
                                    <p class="text-xs font-semibold text-amber-700">⚑ High risk patient</p>
                                    <p class="mt-0.5 text-xs text-amber-600">{{ $patient->risk_flag_reason }}</p>
                                </div>
                            @endif
                        </dl>
                    @else
                        <p class="text-sm text-plum-400">Patient not found.</p>
                    @endif
                </div>

                {{-- Actions --}}
                @if($consultation->status === 'awaiting_review' || $consultation->status === 'flagged')
                    @if(! $consultation->patient?->do_not_treat && ! $consultation->patient?->deceased)
                    <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm space-y-3">
                        <h3 class="text-sm font-semibold text-plum-700">Clinical Decision</h3>

                        {{-- Approve --}}
                        <form method="POST" action="{{ route('consultations.approve', $consultation) }}">
                            @csrf
                            <button
                                type="submit"
                                onclick="return confirm('Approve this consultation and create a draft prescription?')"
                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-green-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-700"
                            >
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                                </svg>
                                Approve &amp; Create Prescription
                            </button>
                        </form>

                        {{-- Reject --}}
                        <button
                            @click="showRejectModal = true"
                            class="flex w-full items-center justify-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700 hover:bg-red-100"
                        >
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                            </svg>
                            Reject
                        </button>

                        {{-- Flag --}}
                        <button
                            @click="showFlagModal = true"
                            class="flex w-full items-center justify-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm font-medium text-amber-700 hover:bg-amber-100"
                        >
                            Flag for Review
                        </button>
                    </div>
                    @endif
                @endif

                @if($consultation->status === 'approved')
                    <div class="rounded-2xl border border-green-200 bg-green-50 p-4 text-center">
                        <p class="text-sm font-semibold text-green-700">✓ Approved</p>
                        <p class="text-xs text-green-600 mt-1">By {{ $consultation->prescriber?->full_name }} on {{ $consultation->reviewed_at?->format('d M Y') }}</p>
                    </div>
                @endif

                @if($consultation->status === 'rejected')
                    <div class="rounded-2xl border border-red-200 bg-red-50 p-4">
                        <p class="text-sm font-semibold text-red-700">✗ Rejected</p>
                        <p class="text-xs text-red-600 mt-1">{{ $consultation->rejection?->reason }}</p>
                    </div>
                @endif

            </div>
        </div>

        {{-- Reject modal --}}
        <div x-show="showRejectModal" class="fixed inset-0 z-50 flex items-center justify-center bg-plum-950/50 p-4" x-cloak>
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-plum-lg">
                <h3 class="mb-1 text-base font-semibold text-plum-800">Reject Consultation</h3>
                <p class="mb-3 text-xs text-plum-400">This will be recorded in the GPhC rejection register.</p>
                <form method="POST" action="{{ route('consultations.reject', $consultation) }}">
                    @csrf
                    <textarea
                        name="reason"
                        rows="4"
                        required
                        class="block w-full rounded-xl border-plum-200 text-sm focus:border-lilac-500 focus:ring-lilac-500"
                        placeholder="Clinical reason for rejection…"
                    ></textarea>
                    <label class="mt-3 flex items-center gap-2 text-sm text-plum-600">
                        <input type="checkbox" name="notify_patient" value="1" checked class="size-4 rounded border-plum-300 text-lilac-500">
                        Notify patient by email
                    </label>
                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" @click="showRejectModal = false" class="rounded-xl border border-plum-200 px-4 py-2 text-sm text-plum-500">Cancel</button>
                        <button type="submit" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Confirm Rejection</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Flag modal --}}
        <div x-show="showFlagModal" class="fixed inset-0 z-50 flex items-center justify-center bg-plum-950/50 p-4" x-cloak>
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-plum-lg">
                <h3 class="mb-3 text-base font-semibold text-plum-800">Flag for Further Review</h3>
                <form method="POST" action="{{ route('consultations.flag', $consultation) }}">
                    @csrf
                    <textarea
                        name="reason"
                        rows="3"
                        required
                        class="block w-full rounded-xl border-plum-200 text-sm focus:border-lilac-500 focus:ring-lilac-500"
                        placeholder="Reason for flagging…"
                    ></textarea>
                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" @click="showFlagModal = false" class="rounded-xl border border-plum-200 px-4 py-2 text-sm text-plum-500">Cancel</button>
                        <button type="submit" class="rounded-xl bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600">Flag</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.app>
