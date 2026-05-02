<x-layouts.app>
    <x-slot name="pageTitle">GDPR Consent — {{ $patient->full_name }}</x-slot>

    <div class="max-w-xl space-y-5 animate-fade-in">

        <div class="flex items-center gap-3">
            <a href="{{ route('patients.show', $patient) }}" class="text-sm font-medium text-lilac-600 hover:text-lilac-800">← Patient record</a>
        </div>

        {{-- Current consent status --}}
        <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm">
            <h2 class="mb-4 text-base font-semibold text-plum-800">Current Consent Status</h2>
            <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <div>
                    <dt class="text-xs text-plum-400">GDPR consent given</dt>
                    <dd class="{{ $patient->gdpr_consent_at ? 'text-green-700 font-medium' : 'text-red-600' }}">
                        {{ $patient->gdpr_consent_at ? '✓ ' . $patient->gdpr_consent_at->format('d M Y, H:i') : '✗ Not given' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-plum-400">Marketing consent</dt>
                    <dd class="{{ $patient->gdpr_marketing_consent ? 'text-green-700 font-medium' : 'text-plum-500' }}">
                        {{ $patient->gdpr_marketing_consent ? '✓ Opted in' : '✗ Not opted in' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-plum-400">Terms accepted</dt>
                    <dd class="{{ $patient->terms_accepted_at ? 'text-green-700 font-medium' : 'text-red-600' }}">
                        {{ $patient->terms_accepted_at ? '✓ ' . $patient->terms_accepted_at->format('d M Y') : '✗ Not accepted' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-plum-400">Age verified</dt>
                    <dd class="{{ $patient->age_verified ? 'text-green-700 font-medium' : 'text-amber-600' }}">
                        {{ $patient->age_verified ? '✓ Verified' . ($patient->age_verified_at ? ' ' . $patient->age_verified_at->format('d M Y') : '') : '✗ Not verified' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-plum-400">Identity verified</dt>
                    <dd class="{{ $patient->identity_verified ? 'text-green-700 font-medium' : 'text-plum-400' }}">
                        {{ $patient->identity_verified ? '✓ Verified' : '— Not verified' }}
                    </dd>
                </div>
            </dl>
        </div>

        {{-- Compliance log entries for this patient --}}
        <div class="rounded-2xl border border-plum-100 bg-white shadow-plum-sm">
            <div class="flex items-center justify-between border-b border-plum-50 px-5 py-4">
                <h3 class="text-sm font-semibold text-plum-700">Consent & Compliance History</h3>
                <a href="{{ route('compliance.log', ['type' => 'gdpr_consent', 'user_id' => $patient->id]) }}"
                   class="text-xs font-medium text-lilac-600 hover:text-lilac-800">View all →</a>
            </div>
            @forelse($consentLogs as $log)
                <div class="flex items-start justify-between gap-4 border-b border-plum-50 px-5 py-3 last:border-0">
                    <div>
                        <p class="text-xs font-semibold text-plum-700">{{ $log->typeLabel() }}</p>
                        @if($log->notes)
                            <p class="mt-0.5 text-xs text-plum-500">{{ $log->notes }}</p>
                        @endif
                        @if($log->staff)
                            <p class="mt-0.5 text-xs text-plum-400">Recorded by {{ $log->staff->full_name }}</p>
                        @endif
                    </div>
                    <p class="shrink-0 text-xs text-plum-400">{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y') }}</p>
                </div>
            @empty
                <div class="px-5 py-8 text-center text-sm text-plum-400">No compliance events recorded for this patient.</div>
            @endforelse
        </div>

        {{-- Log a consent event manually --}}
        <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm" x-data="{ open: false }">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-plum-700">Record Consent Event</h3>
                <button @click="open = !open" class="text-xs font-medium text-lilac-600 hover:text-lilac-800">+ Log event</button>
            </div>
            <div x-show="open" x-cloak class="mt-4 space-y-3">
                <form method="POST" action="{{ route('compliance.log.store') }}" class="space-y-3">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $patient->id }}">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-plum-600">Event type</label>
                        <select name="type" required class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                            @foreach(\App\Models\ComplianceLog::TYPES_GROUPED['Patient'] as $k)
                                <option value="{{ $k }}">{{ \App\Models\ComplianceLog::TYPES[$k] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-plum-600">Notes <span class="text-red-500">*</span></label>
                        <textarea name="notes" rows="2" required
                            class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500"
                            placeholder="e.g. Patient called to withdraw marketing consent…"></textarea>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="open = false" class="rounded-xl border border-plum-200 px-4 py-2 text-sm text-plum-500">Cancel</button>
                        <button type="submit" class="rounded-xl bg-plum-800 px-4 py-2 text-sm font-medium text-lilac-200 hover:bg-plum-900">Save</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- DSAR shortcut --}}
        <div class="rounded-xl border border-plum-100 bg-plum-50/60 px-4 py-3">
            <p class="text-xs text-plum-600">
                If this patient has submitted a data request, log it as a
                <a href="{{ route('compliance.dsar.create') }}?email={{ urlencode($patient->email) }}"
                   class="font-medium text-lilac-600 hover:text-lilac-800">DSAR request →</a>
            </p>
        </div>

    </div>
</x-layouts.app>
