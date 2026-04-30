<x-layouts.app>
    <x-slot name="pageTitle">{{ $patient->full_name }}</x-slot>

    <div class="space-y-5 animate-fade-in" x-data="{ tab: 'overview' }">

        {{-- ── Do Not Treat banner ──────────────────────────────────────── --}}
        @if($patient->do_not_treat)
            <div class="flex items-center gap-3 rounded-2xl border-2 border-red-300 bg-red-50 px-5 py-4">
                <svg class="size-6 shrink-0 text-red-600" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="font-semibold text-red-800">DO NOT TREAT</p>
                    <p class="text-sm text-red-700">{{ $patient->do_not_treat_reason }}</p>
                    <p class="mt-0.5 text-xs text-red-500">Set by {{ $patient->doNotTreatSetBy?->full_name }} on {{ $patient->do_not_treat_set_at?->format('d M Y') }}</p>
                </div>
            </div>
        @endif

        {{-- ── Deceased banner ─────────────────────────────────────────── --}}
        @if($patient->deceased)
            <div class="flex items-center gap-3 rounded-2xl border border-plum-200 bg-plum-50 px-5 py-4">
                <svg class="size-5 shrink-0 text-plum-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                </svg>
                <p class="text-sm text-plum-600">This patient is marked as deceased. Clinical actions are locked.</p>
            </div>
        @endif

        {{-- ── Page header ─────────────────────────────────────────────── --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-start gap-4">
                <div class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-plum-800 text-xl font-bold text-lilac-300">
                    {{ strtoupper(substr($patient->first_name, 0, 1) . substr($patient->last_name, 0, 1)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-xl font-semibold text-plum-800">{{ $patient->full_name }}</h2>
                        @if($patient->risk_flagged)
                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">High Risk</span>
                        @endif
                        @if($patient->identity_verified)
                            <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700">ID Verified</span>
                        @endif
                    </div>
                    <p class="mt-0.5 text-sm text-plum-400">
                        {{ $patient->email }}
                        @if($patient->date_of_birth)
                            · DOB {{ $patient->date_of_birth->format('d M Y') }} ({{ $patient->age }}y)
                        @endif
                    </p>
                    <p class="text-xs text-plum-300">Patient since {{ $patient->created_at->format('d M Y') }}</p>
                </div>
            </div>

            {{-- Action buttons --}}
            @if($patient->isActionable())
                <div class="flex flex-wrap gap-2" x-data="{ showFlagModal: false, showDntModal: false }">

                    @if(! $patient->risk_flagged)
                        <button
                            @click="showFlagModal = true"
                            class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-medium text-amber-700 hover:bg-amber-100"
                        >Flag Risk</button>
                    @else
                        <form method="POST" action="{{ route('patients.unflag', $patient) }}">
                            @csrf
                            <button type="submit" class="rounded-xl border border-plum-200 px-3 py-2 text-xs font-medium text-plum-500 hover:bg-plum-50">Remove Flag</button>
                        </form>
                    @endif

                    <button
                        @click="showDntModal = true"
                        class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-medium text-red-600 hover:bg-red-100"
                    >Do Not Treat</button>

                    <form method="POST" action="{{ route('patients.deceased', $patient) }}"
                        onsubmit="return confirm('Mark {{ $patient->first_name }} as deceased? This action locks all clinical activity.')">
                        @csrf
                        <button type="submit" class="rounded-xl border border-plum-200 px-3 py-2 text-xs font-medium text-plum-400 hover:bg-plum-50">Mark Deceased</button>
                    </form>

                    {{-- Flag modal --}}
                    <div x-show="showFlagModal" class="fixed inset-0 z-50 flex items-center justify-center bg-plum-950/50 p-4" x-cloak>
                        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-plum-lg" @click.stop>
                            <h3 class="mb-3 text-base font-semibold text-plum-800">Flag as High Risk</h3>
                            <form method="POST" action="{{ route('patients.flag', $patient) }}">
                                @csrf
                                <textarea
                                    name="reason"
                                    rows="4"
                                    required
                                    class="block w-full rounded-xl border-plum-200 text-sm focus:border-lilac-500 focus:ring-lilac-500"
                                    placeholder="Reason for flagging this patient…"
                                ></textarea>
                                <div class="mt-4 flex justify-end gap-2">
                                    <button type="button" @click="showFlagModal = false" class="rounded-xl border border-plum-200 px-4 py-2 text-sm text-plum-500">Cancel</button>
                                    <button type="submit" class="rounded-xl bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600">Flag Patient</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- DNT modal --}}
                    <div x-show="showDntModal" class="fixed inset-0 z-50 flex items-center justify-center bg-plum-950/50 p-4" x-cloak>
                        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-plum-lg" @click.stop>
                            <h3 class="mb-1 text-base font-semibold text-red-700">Set Do Not Treat</h3>
                            <p class="mb-3 text-xs text-plum-400">This will permanently block all clinical actions for this patient. This is displayed as a warning banner on every page view.</p>
                            <form method="POST" action="{{ route('patients.do-not-treat', $patient) }}">
                                @csrf
                                <textarea
                                    name="reason"
                                    rows="4"
                                    required
                                    class="block w-full rounded-xl border-red-200 text-sm focus:border-red-400 focus:ring-red-400"
                                    placeholder="Clinical or safety reason for Do Not Treat…"
                                ></textarea>
                                <div class="mt-4 flex justify-end gap-2">
                                    <button type="button" @click="showDntModal = false" class="rounded-xl border border-plum-200 px-4 py-2 text-sm text-plum-500">Cancel</button>
                                    <button type="submit" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Confirm Do Not Treat</button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            @endif
        </div>

        {{-- ── Tabs ─────────────────────────────────────────────────────── --}}
        <div class="border-b border-plum-100">
            <nav class="-mb-px flex gap-1 overflow-x-auto" aria-label="Patient tabs">
                @foreach([
                    ['key' => 'overview',      'label' => 'Overview'],
                    ['key' => 'consultations', 'label' => 'Consultations (' . $patient->consultations->count() . ')'],
                    ['key' => 'prescriptions', 'label' => 'Prescriptions (' . $patient->prescriptions->count() . ')'],
                    ['key' => 'orders',        'label' => 'Orders (' . $patient->orders->count() . ')'],
                    ['key' => 'notes',         'label' => 'Clinical Notes (' . $patient->clinicalNotes->count() . ')'],
                    ['key' => 'timeline',      'label' => 'Timeline'],
                ] as $t)
                    <button
                        @click="tab = '{{ $t['key'] }}'"
                        :class="tab === '{{ $t['key'] }}' ? 'border-lilac-500 text-plum-800' : 'border-transparent text-plum-400 hover:border-plum-200 hover:text-plum-600'"
                        class="shrink-0 border-b-2 px-4 py-2.5 text-sm font-medium transition"
                    >{{ $t['label'] }}</button>
                @endforeach
            </nav>
        </div>

        {{-- ── Tab panels ──────────────────────────────────────────────── --}}

        {{-- Overview --}}
        <div x-show="tab === 'overview'" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">

            {{-- Demographics --}}
            <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm col-span-2">
                <h3 class="mb-4 text-sm font-semibold text-plum-700">Demographics</h3>
                <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div><dt class="text-xs text-plum-400">Full name</dt><dd class="font-medium text-plum-800">{{ $patient->full_name }}</dd></div>
                    <div><dt class="text-xs text-plum-400">Date of birth</dt><dd class="text-plum-700">{{ $patient->date_of_birth?->format('d M Y') ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-plum-400">Email</dt><dd class="text-plum-700 truncate">{{ $patient->email }}</dd></div>
                    <div><dt class="text-xs text-plum-400">Mobile</dt><dd class="text-plum-700">{{ $patient->mobile ?? '—' }}</dd></div>
                    <div class="col-span-2"><dt class="text-xs text-plum-400">Address</dt><dd class="text-plum-700">{{ $patient->formatted_address ?: '—' }}</dd></div>
                    <div><dt class="text-xs text-plum-400">GP Surgery</dt><dd class="text-plum-700">{{ $patient->gpSurgery?->name ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-plum-400">Identity verified</dt><dd>
                        @if($patient->identity_verified)
                            <span class="text-xs font-medium text-green-600">✓ Verified</span>
                        @else
                            <span class="text-xs text-plum-400">Not verified</span>
                        @endif
                    </dd></div>
                </dl>
                <div class="mt-4 pt-4 border-t border-plum-50">
                    <a href="{{ route('patients.edit', $patient) }}" class="text-xs font-medium text-lilac-600 hover:text-lilac-800">Edit demographics →</a>
                </div>
            </div>

            {{-- Stats sidebar --}}
            <div class="space-y-3">
                @foreach([
                    ['label' => 'Consultations', 'value' => $patient->consultations->count(), 'route' => null],
                    ['label' => 'Prescriptions', 'value' => $patient->prescriptions->count(), 'route' => null],
                    ['label' => 'Orders',        'value' => $patient->orders->count(),        'route' => null],
                ] as $stat)
                <div class="rounded-xl border border-plum-100 bg-white p-4 shadow-plum-sm text-center">
                    <p class="text-2xl font-bold text-plum-800">{{ $stat['value'] }}</p>
                    <p class="text-xs text-plum-400">{{ $stat['label'] }}</p>
                </div>
                @endforeach
            </div>

        </div>

        {{-- Consultations --}}
        <div x-show="tab === 'consultations'" x-cloak>
            @if($patient->consultations->isEmpty())
                <div class="rounded-2xl border border-plum-100 bg-white p-12 text-center text-sm text-plum-400">No consultations on record.</div>
            @else
                <div class="overflow-hidden rounded-2xl border border-plum-100 bg-white shadow-plum-sm">
                    <table class="min-w-full divide-y divide-plum-100">
                        <thead class="bg-plum-50/60">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Treatment</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Submitted</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-plum-500"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-plum-50">
                            @foreach($patient->consultations as $c)
                                <tr class="hover:bg-plum-50/20">
                                    <td class="px-4 py-3 text-sm font-medium text-plum-800">{{ $c->product?->name ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full px-2 py-0.5 text-xs font-medium
                                            {{ match($c->status) {
                                                'approved' => 'bg-green-100 text-green-700',
                                                'rejected' => 'bg-red-100 text-red-700',
                                                'flagged'  => 'bg-amber-100 text-amber-700',
                                                default    => 'bg-plum-100 text-plum-600',
                                            } }}
                                        ">{{ ucfirst(str_replace('_', ' ', $c->status)) }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-plum-400">{{ $c->created_at->format('d M Y') }}</td>
                                    <td class="px-4 py-3 text-right">
                                        @can('review_consultations')
                                        <a href="{{ route('consultations.show', $c) }}" class="text-xs font-medium text-lilac-600 hover:text-lilac-800">Review →</a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Clinical Notes --}}
        <div x-show="tab === 'notes'" x-cloak>
            <div class="space-y-3">

                {{-- Add note form --}}
                @if($patient->isActionable())
                <div class="rounded-2xl border border-plum-100 bg-white p-4 shadow-plum-sm">
                    <p class="mb-2 text-sm font-medium text-plum-700">Add Clinical Note</p>
                    <form method="POST" action="{{ route('patients.notes.store', $patient) }}">
                        @csrf
                        <textarea
                            name="body"
                            rows="3"
                            required
                            class="block w-full rounded-xl border-plum-200 text-sm focus:border-lilac-500 focus:ring-lilac-500"
                            placeholder="Internal note — not visible to the patient…"
                        ></textarea>
                        <div class="mt-2 flex items-center justify-between">
                            <label class="flex items-center gap-1.5 text-xs text-plum-500">
                                <input type="checkbox" name="internal_only" value="1" checked class="size-3.5 rounded border-plum-300 text-lilac-500">
                                Internal only
                            </label>
                            <button type="submit" class="rounded-xl bg-plum-800 px-4 py-1.5 text-xs font-medium text-lilac-200 hover:bg-plum-900">Save Note</button>
                        </div>
                    </form>
                </div>
                @endif

                {{-- Notes list --}}
                @forelse($patient->clinicalNotes->sortByDesc('created_at') as $note)
                    <div class="rounded-2xl border border-plum-100 bg-white p-4 shadow-plum-sm">
                        <div class="mb-2 flex items-center justify-between">
                            <p class="text-xs font-medium text-plum-700">{{ $note->staff?->full_name ?? 'Unknown' }}</p>
                            <p class="text-xs text-plum-400">{{ $note->created_at->format('d M Y, H:i') }}</p>
                        </div>
                        <p class="text-sm text-plum-600">{{ $note->body }}</p>
                        @if($note->internal_only)
                            <span class="mt-2 inline-flex rounded-full bg-plum-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-plum-500">Internal only</span>
                        @endif
                    </div>
                @empty
                    <div class="rounded-2xl border border-plum-100 bg-white p-12 text-center text-sm text-plum-400">No clinical notes.</div>
                @endforelse

            </div>
        </div>

        {{-- Prescriptions, Orders, Timeline — stubs for Phase 8/11/7 --}}
        <div x-show="tab === 'prescriptions'" x-cloak>
            <div class="rounded-2xl border border-plum-100 bg-white p-12 text-center text-sm text-plum-400">Prescription module coming in Phase 8.</div>
        </div>
        <div x-show="tab === 'orders'" x-cloak>
            <div class="rounded-2xl border border-plum-100 bg-white p-12 text-center text-sm text-plum-400">Orders module coming in Phase 11.</div>
        </div>
        <div x-show="tab === 'timeline'" x-cloak>
            <div class="rounded-2xl border border-plum-100 bg-white p-12 text-center text-sm text-plum-400">
                <a href="{{ route('patients.timeline', $patient) }}" class="font-medium text-lilac-600 hover:text-lilac-800">View full timeline →</a>
            </div>
        </div>

    </div>
</x-layouts.app>
