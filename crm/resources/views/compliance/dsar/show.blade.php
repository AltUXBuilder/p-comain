<x-layouts.app>
    <x-slot name="pageTitle">DSAR #{{ $dsar->id }}</x-slot>

    <div class="max-w-xl space-y-5 animate-fade-in">

        <a href="{{ route('compliance.dsar.index') }}" class="text-sm font-medium text-lilac-600 hover:text-lilac-800">← DSAR Requests</a>

        @if($dsar->isOverdue())
            <div class="rounded-2xl border-2 border-red-300 bg-red-50 px-5 py-4">
                <p class="font-semibold text-red-800">⚠ OVERDUE — Response was due {{ $dsar->due_at->format('d M Y') }}</p>
                <p class="text-sm text-red-600">GDPR Article 12 requires a response within 30 calendar days of receipt.</p>
            </div>
        @endif

        {{-- Detail card --}}
        <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="font-mono text-sm font-bold text-plum-500">DSAR #{{ $dsar->id }}</p>
                    <h2 class="mt-1 text-lg font-semibold text-plum-800">{{ \App\Models\DsarRequest::TYPES[$dsar->type] ?? $dsar->type }}</h2>
                </div>
                <span class="rounded-full px-3 py-1 text-sm font-semibold {{ $dsar->statusColour() }}">
                    {{ \App\Models\DsarRequest::STATUSES[$dsar->status] ?? $dsar->status }}
                </span>
            </div>
            <dl class="mt-4 grid grid-cols-2 gap-x-6 gap-y-2.5 text-sm">
                <div>
                    <dt class="text-xs text-plum-400">Requestor email</dt>
                    <dd class="text-plum-700">{{ $dsar->requestor_email }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-plum-400">Due date</dt>
                    <dd class="{{ $dsar->isOverdue() ? 'font-semibold text-red-600' : 'text-plum-700' }}">{{ $dsar->due_at->format('d M Y') }}</dd>
                </div>
                @if($dsar->patient)
                <div>
                    <dt class="text-xs text-plum-400">Linked patient</dt>
                    <dd><a href="{{ route('patients.show', $dsar->patient) }}" class="font-medium text-lilac-600 hover:text-lilac-800">{{ $dsar->patient->full_name }}</a></dd>
                </div>
                @endif
                <div>
                    <dt class="text-xs text-plum-400">Received</dt>
                    <dd class="text-plum-700">{{ $dsar->created_at->format('d M Y') }}</dd>
                </div>
                @if($dsar->completed_at)
                <div>
                    <dt class="text-xs text-plum-400">Completed</dt>
                    <dd class="text-green-600 font-medium">{{ $dsar->completed_at->format('d M Y') }}</dd>
                </div>
                @endif
                @if($dsar->handler)
                <div>
                    <dt class="text-xs text-plum-400">Handled by</dt>
                    <dd class="text-plum-700">{{ $dsar->handler->full_name }}</dd>
                </div>
                @endif
                @if($dsar->notes)
                <div class="col-span-2">
                    <dt class="text-xs text-plum-400">Notes</dt>
                    <dd class="text-plum-600">{{ $dsar->notes }}</dd>
                </div>
                @endif
            </dl>
        </div>

        {{-- Update status --}}
        @if(! in_array($dsar->status, ['completed', 'rejected']))
        <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm">
            <h3 class="mb-3 text-sm font-semibold text-plum-700">Update Status</h3>
            <form method="POST" action="{{ route('compliance.dsar.status', $dsar) }}" class="space-y-3">
                @csrf @method('PATCH')
                <div class="flex gap-3">
                    <select name="status" class="flex-1 rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                        @foreach(\App\Models\DsarRequest::STATUSES as $k => $v)
                            <option value="{{ $k }}" {{ $dsar->status === $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="rounded-xl bg-plum-800 px-4 py-2 text-sm font-medium text-lilac-200 hover:bg-plum-900">Update</button>
                </div>
                <textarea name="notes" rows="2" class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500"
                    placeholder="Add notes (optional)…">{{ $dsar->notes }}</textarea>
            </form>
        </div>
        @endif

        {{-- SAR export --}}
        @if($dsar->type === 'subject_access' && $dsar->user_id)
        <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm">
            <h3 class="mb-2 text-sm font-semibold text-plum-700">Subject Access Request — Data Export</h3>
            <p class="mb-3 text-xs text-plum-400">Generates a JSON file containing all personal data held for this patient.</p>
            <div class="flex gap-2">
                <form method="POST" action="{{ route('compliance.dsar.export', $dsar) }}">
                    @csrf
                    <button type="submit" class="rounded-xl bg-plum-800 px-4 py-2 text-sm font-medium text-lilac-200 hover:bg-plum-900">
                        Generate &amp; Download Data Export
                    </button>
                </form>
                @if($dsar->exported_data_path)
                    <a href="{{ route('compliance.dsar.download', $dsar) }}" class="rounded-xl border border-plum-200 px-4 py-2 text-sm font-medium text-plum-600 hover:bg-plum-50">
                        Re-download Previous Export
                    </a>
                @endif
            </div>
        </div>
        @endif

        {{-- Erasure --}}
        @if($dsar->type === 'erasure' && $dsar->user_id && $dsar->status !== 'completed')
        <div class="rounded-2xl border-2 border-red-200 bg-red-50 p-5" x-data="{ confirm: false }">
            <h3 class="mb-2 text-sm font-semibold text-red-800">Right to Erasure</h3>
            <p class="mb-3 text-xs text-red-600">This will pseudonymise all personal data for this patient. Prescription records will be retained as required by GPhC and the Medicines Act. This action cannot be undone.</p>

            <div x-show="!confirm">
                <button @click="confirm = true" type="button" class="rounded-xl border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-100">
                    Initiate Erasure
                </button>
            </div>

            <div x-show="confirm" x-cloak class="space-y-3">
                <p class="text-sm font-semibold text-red-800">Are you absolutely sure? This is irreversible.</p>
                <div class="flex gap-2">
                    <form method="POST" action="{{ route('compliance.dsar.erase', $dsar) }}">
                        @csrf
                        <button type="submit" class="rounded-xl bg-red-700 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800">
                            Confirm — Erase Patient Data
                        </button>
                    </form>
                    <button @click="confirm = false" type="button" class="rounded-xl border border-plum-200 px-4 py-2 text-sm text-plum-500">Cancel</button>
                </div>
            </div>
        </div>
        @endif

    </div>
</x-layouts.app>
