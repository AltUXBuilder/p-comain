<x-layouts.app>
    <x-slot name="pageTitle">Print Queue</x-slot>

    <div class="space-y-5 animate-fade-in" x-data="{ selected: [] }">

        {{-- Stats --}}
        <div class="grid grid-cols-3 gap-3">
            <div class="rounded-2xl border border-plum-100 bg-white p-4 text-center shadow-plum-sm">
                <p class="text-2xl font-bold text-plum-800">{{ $stats['total'] }}</p>
                <p class="text-xs text-plum-400">Total labels</p>
            </div>
            <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4 text-center shadow-plum-sm">
                <p class="text-2xl font-bold text-amber-700">{{ $stats['unprinted'] }}</p>
                <p class="text-xs text-amber-500">Not yet printed</p>
            </div>
            <div class="rounded-2xl border border-blue-100 bg-blue-50 p-4 text-center shadow-plum-sm">
                <p class="text-2xl font-bold text-blue-700">{{ $stats['cold_chain'] }}</p>
                <p class="text-xs text-blue-400">❄ Cold chain</p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="rounded-2xl border border-plum-100 bg-white p-4 shadow-plum-sm">
            <form method="GET" action="{{ route('labels.index') }}" class="flex flex-wrap gap-3 items-end">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search patient, medication, Rx number…"
                        class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                </div>
                <label class="flex items-center gap-1.5 text-sm text-plum-600">
                    <input type="checkbox" name="unprinted" value="1" class="size-4 rounded border-plum-300 text-lilac-500"
                        {{ request()->boolean('unprinted') ? 'checked' : '' }}>
                    Unprinted only
                </label>
                <label class="flex items-center gap-1.5 text-sm text-plum-600">
                    <input type="checkbox" name="cold_chain" value="1" class="size-4 rounded border-plum-300 text-blue-500"
                        {{ request()->boolean('cold_chain') ? 'checked' : '' }}>
                    Cold chain only
                </label>
                <button type="submit" class="rounded-xl bg-plum-800 px-4 py-2 text-sm font-medium text-lilac-200 hover:bg-plum-900">Filter</button>
            </form>
        </div>

        {{-- Mark printed form --}}
        <form method="POST" action="{{ route('labels.mark-printed') }}" id="mark-printed-form">
            @csrf

            <div class="overflow-hidden rounded-2xl border border-plum-100 bg-white shadow-plum-sm">
                <table class="min-w-full divide-y divide-plum-100">
                    <thead class="bg-plum-50/60">
                        <tr>
                            <th class="w-8 px-4 py-3">
                                <input type="checkbox" class="size-4 rounded border-plum-300 text-lilac-500"
                                    @change="selected = $event.target.checked
                                        ? Array.from(document.querySelectorAll('.lbl-check')).map(c => c.value)
                                        : []">
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Label / Patient</th>
                            <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 sm:table-cell">Medication</th>
                            <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 md:table-cell">Batch / Expiry</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Flags</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-plum-500"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-plum-50">
                        @forelse($labels as $label)
                            <tr class="hover:bg-plum-50/20 transition">
                                <td class="px-4 py-3">
                                    <input
                                        type="checkbox"
                                        name="ids[]"
                                        value="{{ $label->id }}"
                                        class="lbl-check size-4 rounded border-plum-300 text-lilac-500"
                                        x-model="selected"
                                    >
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-mono text-xs text-plum-400">{{ $label->prescription?->prescription_number }}</p>
                                    <p class="text-sm font-medium text-plum-800">{{ $label->patient_name }}</p>
                                    <p class="text-xs text-plum-400">{{ $label->dispensing_date->format('d M Y') }}</p>
                                </td>
                                <td class="hidden px-4 py-3 text-sm text-plum-700 sm:table-cell">
                                    {{ $label->medication_name }}
                                    <span class="text-xs text-plum-400">{{ $label->medication_strength }}</span>
                                </td>
                                <td class="hidden px-4 py-3 md:table-cell">
                                    @if($label->batch_number)
                                        <p class="font-mono text-xs text-plum-600">{{ $label->batch_number }}</p>
                                    @endif
                                    @if($label->expiry_date)
                                        <p class="text-xs {{ $label->expiryColour() }}">Exp {{ $label->expiry_date->format('m/Y') }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        @if($label->cold_chain)
                                            <span class="rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-semibold text-blue-700">❄</span>
                                        @endif
                                        @if(! $label->printed)
                                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-700">Unprinted</span>
                                        @else
                                            <span class="rounded-full bg-green-100 px-2 py-0.5 text-[10px] font-semibold text-green-700">Printed</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('dispensing.label', $label) }}" class="text-xs font-medium text-lilac-600 hover:text-lilac-800">View</a>
                                    <a href="{{ route('dispensing.label.download', $label) }}" class="ml-3 text-xs font-medium text-plum-500 hover:text-plum-700">PDF</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-sm text-plum-400">No labels found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mark printed bulk action --}}
            <div class="mt-3 flex items-center justify-between" x-show="selected.length > 0" x-cloak>
                <p class="text-sm text-plum-600"><span x-text="selected.length"></span> label(s) selected</p>
                <button type="submit" class="rounded-xl bg-plum-800 px-5 py-2.5 text-sm font-semibold text-lilac-200 hover:bg-plum-900">
                    Mark as Printed
                </button>
            </div>

        </form>

        @if($labels->hasPages())
            <div class="flex justify-center">{{ $labels->links() }}</div>
        @endif

    </div>
</x-layouts.app>
