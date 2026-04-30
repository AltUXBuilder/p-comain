<x-layouts.app>
    <x-slot name="pageTitle">{{ $prescription->prescription_number }}</x-slot>

    <div class="space-y-5 animate-fade-in" x-data="{ showSignModal: false, useOverride: false }">

        <div class="flex items-center gap-3">
            <a href="{{ route('prescriptions.index') }}" class="text-sm font-medium text-lilac-600 hover:text-lilac-800">← Prescriptions</a>
        </div>

        @if($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 space-y-1">
                @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        <div class="grid gap-5 lg:grid-cols-3">

            {{-- ── Main prescription detail ─────────────────────────── --}}
            <div class="space-y-4 lg:col-span-2">

                {{-- Header card --}}
                <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="font-mono text-sm font-bold text-plum-600">{{ $prescription->prescription_number }}</p>
                            <h2 class="mt-1 text-lg font-semibold text-plum-800">
                                {{ $prescription->product?->name ?? 'Unknown medication' }}
                                @if($prescription->is_repeat)
                                    <span class="ml-2 rounded-full bg-lilac-100 px-2 py-0.5 text-xs font-semibold text-lilac-700">Repeat</span>
                                @endif
                            </h2>
                            <p class="mt-0.5 text-sm text-plum-400">
                                Patient: <a href="{{ route('patients.show', $prescription->patient) }}" class="font-medium text-plum-700 hover:text-lilac-700">{{ $prescription->patient?->full_name }}</a>
                            </p>
                        </div>
                        <span class="rounded-full px-3 py-1 text-sm font-semibold {{ $prescription->statusColour() }}">
                            {{ $prescription->statusLabel() }}
                        </span>
                    </div>
                </div>

                {{-- Clinical detail --}}
                <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm">
                    <h3 class="mb-4 text-sm font-semibold text-plum-700">Prescription Detail</h3>
                    <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                        <div>
                            <dt class="text-xs text-plum-400">Medication</dt>
                            <dd class="font-semibold text-plum-800">{{ $prescription->product?->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-plum-400">Strength / Form</dt>
                            <dd class="text-plum-700">{{ $prescription->product?->strength }} {{ $prescription->product?->form }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-plum-400">Quantity</dt>
                            <dd class="text-plum-700">{{ $prescription->quantity ?: '—' }}</dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-xs text-plum-400">Dosage Instructions</dt>
                            <dd class="mt-1 rounded-lg bg-plum-50 px-3 py-2 font-medium text-plum-800">{{ $prescription->dosage_instructions ?: '—' }}</dd>
                        </div>
                        @if($prescription->prescriber_notes)
                        <div class="col-span-2">
                            <dt class="text-xs text-plum-400">Prescriber Notes</dt>
                            <dd class="mt-1 text-plum-600">{{ $prescription->prescriber_notes }}</dd>
                        </div>
                        @endif
                        @if($prescription->is_repeat)
                        <div>
                            <dt class="text-xs text-plum-400">Repeat interval</dt>
                            <dd class="text-plum-700">Every {{ $prescription->repeat_interval_days }} days</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-plum-400">Next due</dt>
                            <dd class="text-plum-700">{{ $prescription->next_repeat_due?->format('d M Y') ?? '—' }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>

                {{-- Legal wording --}}
                <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm">
                    <h3 class="mb-3 text-sm font-semibold text-plum-700">Legal Wording (printed on prescription)</h3>
                    <p class="text-sm text-plum-500 leading-relaxed">{{ $prescription->legal_wording }}</p>
                </div>

                {{-- PDF preview --}}
                @if($prescription->pdf_path)
                <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-plum-700">Prescription PDF</h3>
                        <div class="flex gap-2">
                            <a href="{{ route('prescriptions.pdf', $prescription) }}" target="_blank"
                                class="rounded-lg border border-plum-200 px-3 py-1.5 text-xs font-medium text-plum-600 hover:bg-plum-50">
                                View PDF
                            </a>
                            <a href="{{ route('prescriptions.download', $prescription) }}"
                                class="rounded-lg bg-plum-800 px-3 py-1.5 text-xs font-medium text-lilac-200 hover:bg-plum-900">
                                Download
                            </a>
                        </div>
                    </div>
                    <iframe
                        src="{{ route('prescriptions.pdf', $prescription) }}"
                        class="h-96 w-full rounded-xl border border-plum-100"
                        title="Prescription PDF preview"
                    ></iframe>
                </div>
                @endif

                {{-- Repeat history --}}
                @if($prescription->repeatChildren->isNotEmpty() || $prescription->repeatParent)
                <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm">
                    <h3 class="mb-3 text-sm font-semibold text-plum-700">Repeat History</h3>
                    @if($prescription->repeatParent)
                        <p class="mb-2 text-xs text-plum-400">Parent: <a href="{{ route('prescriptions.show', $prescription->repeatParent) }}" class="font-medium text-lilac-600">{{ $prescription->repeatParent->prescription_number }}</a></p>
                    @endif
                    @foreach($prescription->repeatChildren as $child)
                        <div class="flex items-center justify-between border-b border-plum-50 py-2 last:border-0">
                            <div>
                                <a href="{{ route('prescriptions.show', $child) }}" class="font-mono text-xs font-semibold text-plum-700">{{ $child->prescription_number }}</a>
                                <span class="ml-2 rounded-full px-2 py-0.5 text-[10px] {{ $child->statusColour() }}">{{ $child->statusLabel() }}</span>
                            </div>
                            <p class="text-xs text-plum-400">{{ $child->created_at->format('d M Y') }}</p>
                        </div>
                    @endforeach
                </div>
                @endif

            </div>

            {{-- ── Sidebar: patient, prescriber, actions ────────────── --}}
            <div class="space-y-4">

                {{-- Patient --}}
                <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm">
                    <h3 class="mb-3 text-sm font-semibold text-plum-700">Patient</h3>
                    <dl class="space-y-2 text-sm">
                        <div>
                            <dt class="text-xs text-plum-400">Name</dt>
                            <dd><a href="{{ route('patients.show', $prescription->patient) }}" class="font-medium text-plum-800 hover:text-lilac-700">{{ $prescription->patient?->full_name }}</a></dd>
                        </div>
                        <div>
                            <dt class="text-xs text-plum-400">DOB</dt>
                            <dd class="text-plum-700">{{ $prescription->patient?->date_of_birth?->format('d M Y') ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Prescriber --}}
                <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm">
                    <h3 class="mb-3 text-sm font-semibold text-plum-700">Prescriber</h3>
                    <dl class="space-y-2 text-sm">
                        <div>
                            <dt class="text-xs text-plum-400">Name</dt>
                            <dd class="font-medium text-plum-800">{{ $prescription->prescriber_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-plum-400">GPhC No.</dt>
                            <dd class="font-mono text-plum-700">{{ $prescription->prescriber_gphc_number }}</dd>
                        </div>
                        @if($prescription->signed_at)
                        <div>
                            <dt class="text-xs text-plum-400">Signed</dt>
                            <dd class="text-plum-700">{{ $prescription->signed_at->format('d M Y, H:i') }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>

                {{-- Workflow actions --}}
                <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm space-y-3">
                    <h3 class="text-sm font-semibold text-plum-700">Actions</h3>

                    {{-- Edit draft --}}
                    @if($prescription->status === \App\Models\Prescription::STATUS_DRAFT)
                        <a href="{{ route('prescriptions.edit', $prescription) }}"
                            class="flex w-full items-center justify-center gap-2 rounded-xl border border-plum-200 px-4 py-2.5 text-sm font-medium text-plum-700 hover:bg-plum-50">
                            Edit prescription
                        </a>
                        <form method="POST" action="{{ route('prescriptions.submit', $prescription) }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-plum-800 px-4 py-2.5 text-sm font-semibold text-lilac-200 hover:bg-plum-900">
                                Submit for review
                            </button>
                        </form>
                    @endif

                    {{-- Sign --}}
                    @if($prescription->status === \App\Models\Prescription::STATUS_PENDING_REVIEW)
                        @if(auth('staff')->user()->isClinical() || auth('staff')->user()->isSuperAdmin())
                        <button
                            @click="showSignModal = true"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-green-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-700"
                        >
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                            </svg>
                            Sign &amp; Approve
                        </button>
                        @endif
                    @endif

                    {{-- Send to dispense --}}
                    @if($prescription->status === \App\Models\Prescription::STATUS_APPROVED)
                        <form method="POST" action="{{ route('prescriptions.send-to-dispense', $prescription) }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center justify-center rounded-xl bg-plum-800 px-4 py-2.5 text-sm font-semibold text-lilac-200 hover:bg-plum-900">
                                Send to Dispense
                            </button>
                        </form>
                    @endif

                    {{-- Archive --}}
                    @if(in_array($prescription->status, [\App\Models\Prescription::STATUS_APPROVED, \App\Models\Prescription::STATUS_DISPENSED]))
                        <form method="POST" action="{{ route('prescriptions.archive', $prescription) }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center justify-center rounded-xl border border-plum-200 px-4 py-2 text-sm text-plum-500 hover:bg-plum-50">
                                Archive
                            </button>
                        </form>
                    @endif

                </div>

            </div>

        </div>

        {{-- ── Sign modal ───────────────────────────────────────────── --}}
        <div x-show="showSignModal" class="fixed inset-0 z-50 flex items-center justify-center bg-plum-950/50 p-4" x-cloak>
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-plum-lg">
                <h3 class="mb-1 text-base font-semibold text-plum-800">Sign Prescription</h3>
                <p class="mb-4 text-xs text-plum-400">
                    {{ \App\Models\Prescription::DEFAULT_LEGAL_WORDING }}
                </p>

                <form method="POST" action="{{ route('prescriptions.sign', $prescription) }}" id="sign-form">
                    @csrf
                    <input type="hidden" name="signature_override" id="sig-override-input">

                    {{-- Saved signature preview --}}
                    @if(auth('staff')->user()->signature_path)
                        <div class="mb-4 rounded-xl border border-plum-100 bg-plum-50 p-4">
                            <p class="mb-2 text-xs font-medium text-plum-600">Your saved signature will be used:</p>
                            <img
                                src="{{ route('profile.signature.preview') }}"
                                alt="Saved signature"
                                class="h-16 object-contain"
                            >
                            <button type="button" @click="useOverride = !useOverride" class="mt-2 text-xs font-medium text-lilac-600 hover:text-lilac-800">
                                <span x-text="useOverride ? 'Use saved signature instead' : 'Draw a different signature for this prescription'"></span>
                            </button>
                        </div>
                    @else
                        <div x-data="{ $store: { useOverride: true } }" x-init="useOverride = true">
                        <p class="mb-2 text-xs text-amber-600 font-medium">No signature saved. Draw one below (it will only apply to this prescription).</p>
                        </div>
                    @endif

                    {{-- Canvas override --}}
                    <div x-show="useOverride || !{{ auth('staff')->user()->signature_path ? 'true' : 'false' }}" x-cloak>
                        <x-signature-canvas input-id="sig-override-input" />
                    </div>

                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button" @click="showSignModal = false" class="rounded-xl border border-plum-200 px-4 py-2 text-sm text-plum-500">Cancel</button>
                        <button type="submit" class="rounded-xl bg-green-600 px-5 py-2 text-sm font-semibold text-white hover:bg-green-700">
                            Confirm &amp; Sign
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.app>
