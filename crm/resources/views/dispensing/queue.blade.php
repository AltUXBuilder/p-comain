<x-layouts.app>
    <x-slot name="pageTitle">Dispensing Queue</x-slot>

    <div class="space-y-5 animate-fade-in" x-data="{ selected: [], format: 'standard' }">

        {{-- Stats --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
            <div class="rounded-2xl border border-plum-100 bg-white p-4 text-center shadow-plum-sm">
                <p class="text-2xl font-bold text-plum-800">{{ $stats['total'] }}</p>
                <p class="text-xs text-plum-400">In queue</p>
            </div>
            <div class="rounded-2xl border border-blue-100 bg-blue-50 p-4 text-center shadow-plum-sm">
                <p class="text-2xl font-bold text-blue-700">{{ $stats['cold_chain'] }}</p>
                <p class="text-xs text-blue-400">❄ Cold chain</p>
            </div>
            <div class="hidden rounded-2xl border border-plum-100 bg-white p-4 text-center shadow-plum-sm sm:block">
                <a href="{{ route('labels.index') }}" class="block">
                    <p class="text-2xl font-bold text-plum-800">→</p>
                    <p class="text-xs text-plum-400">Print queue</p>
                </a>
            </div>
        </div>

        {{-- Filters --}}
        <div class="rounded-2xl border border-plum-100 bg-white p-4 shadow-plum-sm">
            <form method="GET" action="{{ route('dispensing.queue') }}" class="flex flex-wrap gap-3 items-end">
                <label class="flex items-center gap-1.5 text-sm text-plum-600">
                    <input type="checkbox" name="cold_chain" value="1" class="size-4 rounded border-plum-300 text-blue-500"
                        {{ request()->boolean('cold_chain') ? 'checked' : '' }}>
                    Cold chain only
                </label>
                <button type="submit" class="rounded-xl bg-plum-800 px-4 py-1.5 text-xs font-medium text-lilac-200 hover:bg-plum-900">Filter</button>
                @if(request()->hasAny(['cold_chain']))
                    <a href="{{ route('dispensing.queue') }}" class="rounded-xl border border-plum-200 px-4 py-1.5 text-xs text-plum-500 hover:bg-plum-50">Clear</a>
                @endif
            </form>
        </div>

        {{-- Batch dispense form wrapping the table --}}
        <form method="POST" action="{{ route('dispensing.batch') }}" id="batch-form">
            @csrf
            <input type="hidden" name="format" :value="format">

            {{-- Format picker --}}
            <div class="flex items-center gap-4 mb-3">
                <p class="text-xs font-medium text-plum-500">Label format:</p>
                <label class="flex items-center gap-1.5 text-sm text-plum-700">
                    <input type="radio" x-model="format" value="standard" class="text-lilac-500"> Standard
                </label>
                <label class="flex items-center gap-1.5 text-sm text-plum-700">
                    <input type="radio" x-model="format" value="branded" class="text-lilac-500"> Branded (P&Co)
                </label>
            </div>

            <div class="overflow-hidden rounded-2xl border border-plum-100 bg-white shadow-plum-sm">
                <table class="min-w-full divide-y divide-plum-100">
                    <thead class="bg-plum-50/60">
                        <tr>
                            <th class="w-8 px-4 py-3">
                                <input type="checkbox" class="size-4 rounded border-plum-300 text-lilac-500"
                                    @change="selected = $event.target.checked
                                        ? Array.from(document.querySelectorAll('.disp-check')).map(c => c.value)
                                        : []">
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Rx / Patient</th>
                            <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 sm:table-cell">Medication</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Flags</th>
                            <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 md:table-cell">Sent</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-plum-500"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-plum-50">
                        @forelse($prescriptions as $rx)
                            <tr class="hover:bg-plum-50/20 transition">
                                <td class="px-4 py-3">
                                    <input
                                        type="checkbox"
                                        name="ids[]"
                                        value="{{ $rx->id }}"
                                        class="disp-check size-4 rounded border-plum-300 text-lilac-500"
                                        x-model="selected"
                                    >
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-mono text-xs font-semibold text-plum-600">{{ $rx->prescription_number }}</p>
                                    <p class="text-sm font-medium text-plum-800">{{ $rx->patient?->full_name }}</p>
                                </td>
                                <td class="hidden px-4 py-3 sm:table-cell">
                                    <p class="text-sm text-plum-700">{{ $rx->product?->name }}</p>
                                    <p class="text-xs text-plum-400">{{ $rx->product?->strength }} {{ $rx->product?->form }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        @if($rx->product?->cold_chain)
                                            <span class="rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-semibold text-blue-700">❄ Cold chain</span>
                                        @endif
                                        @if(isset($nearExpiryWarnings[$rx->product_id]))
                                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-700"
                                                title="Some batches expiring within 90 days">
                                                ⚠ Near expiry
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="hidden px-4 py-3 text-xs text-plum-400 md:table-cell">
                                    {{ $rx->sent_to_dispense_at?->format('d M Y, H:i') }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a
                                        href="{{ route('dispensing.show', $rx) }}"
                                        class="inline-flex items-center gap-1 rounded-lg bg-plum-800 px-3 py-1.5 text-xs font-medium text-lilac-200 hover:bg-plum-900"
                                    >
                                        Dispense
                                        <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center">
                                    <p class="text-sm font-medium text-plum-700">Dispensing queue is empty</p>
                                    <p class="mt-1 text-xs text-plum-400">No prescriptions awaiting dispensing.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Batch dispense button --}}
            <div class="mt-3 flex items-center justify-between" x-show="selected.length > 0" x-cloak>
                <p class="text-sm text-plum-600">
                    <span x-text="selected.length"></span> selected
                    &nbsp;·&nbsp; Format: <span x-text="format" class="font-medium"></span>
                </p>
                <button
                    type="submit"
                    class="rounded-xl bg-plum-800 px-5 py-2.5 text-sm font-semibold text-lilac-200 hover:bg-plum-900"
                >
                    Dispense &amp; Generate Labels
                </button>
            </div>

        </form>

        @if($prescriptions->hasPages())
            <div class="flex justify-center">{{ $prescriptions->links() }}</div>
        @endif

        {{-- Batch results --}}
        @if(session('batch_results'))
            @php $results = session('batch_results'); @endphp
            @if(count($results['failed']))
                <div class="rounded-2xl border border-red-200 bg-red-50 p-4">
                    <p class="mb-2 text-sm font-semibold text-red-700">Some items failed:</p>
                    @foreach($results['failed'] as $f)
                        <p class="text-xs text-red-600">ID {{ $f['id'] }}: {{ $f['reason'] }}</p>
                    @endforeach
                </div>
            @endif
        @endif

    </div>
</x-layouts.app>
