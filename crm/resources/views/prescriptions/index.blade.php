<x-layouts.app>
    <x-slot name="pageTitle">Prescriptions</x-slot>

    <div class="space-y-5 animate-fade-in" x-data="{ selected: [] }">

        {{-- Status tab counts --}}
        <div class="flex gap-2 overflow-x-auto pb-1">
            @foreach(\App\Models\Prescription::STATUSES as $key => $label)
                <a
                    href="{{ route('prescriptions.index', array_merge(request()->query(), ['status' => $key])) }}"
                    @class([
                        'flex shrink-0 items-center gap-2 rounded-xl px-4 py-2 text-sm font-medium transition',
                        'bg-plum-800 text-lilac-200' => request('status') === $key,
                        'border border-plum-200 text-plum-600 hover:bg-plum-50' => request('status') !== $key,
                    ])
                >
                    {{ $label }}
                    <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold
                        {{ request('status') === $key ? 'bg-lilac-500/30 text-lilac-200' : 'bg-plum-100 text-plum-600' }}">
                        {{ $counts[$key] ?? 0 }}
                    </span>
                </a>
            @endforeach
            <a href="{{ route('prescriptions.index') }}" class="shrink-0 rounded-xl border border-plum-200 px-4 py-2 text-sm text-plum-500 hover:bg-plum-50">All</a>
        </div>

        {{-- Register link --}}
        <div class="flex justify-end">
            <a href="{{ route('prescriptions.register') }}" class="flex items-center gap-2 rounded-xl border border-plum-200 px-4 py-2 text-sm font-medium text-plum-600 hover:bg-plum-50">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/>
                </svg>
                Private Prescription Register
            </a>
        </div>

        {{-- Batch sign form --}}
        @if(request('status') === \App\Models\Prescription::STATUS_PENDING_REVIEW)
        <form method="POST" action="{{ route('prescriptions.batch-sign') }}" id="batch-form">
            @csrf
        @endif

        {{-- Table --}}
        <div class="overflow-hidden rounded-2xl border border-plum-100 bg-white shadow-plum-sm">
            <table class="min-w-full divide-y divide-plum-100">
                <thead class="bg-plum-50/60">
                    <tr>
                        @if(request('status') === \App\Models\Prescription::STATUS_PENDING_REVIEW)
                        <th class="w-8 px-4 py-3">
                            <input type="checkbox" class="size-4 rounded border-plum-300 text-lilac-500"
                                @change="selected = $event.target.checked ? Array.from(document.querySelectorAll('.rx-check')).map(c => c.value) : []">
                        </th>
                        @endif
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Rx No.</th>
                        <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 sm:table-cell">Patient</th>
                        <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 md:table-cell">Medication</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Status</th>
                        <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 lg:table-cell">Date</th>
                        <th class="px-4 py-3 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-plum-50">
                    @forelse($prescriptions as $rx)
                        <tr class="hover:bg-plum-50/20 transition">
                            @if(request('status') === \App\Models\Prescription::STATUS_PENDING_REVIEW)
                            <td class="px-4 py-3">
                                <input type="checkbox" name="ids[]" value="{{ $rx->id }}" class="rx-check size-4 rounded border-plum-300 text-lilac-500"
                                    x-model="selected" :value="'{{ $rx->id }}'">
                            </td>
                            @endif
                            <td class="px-4 py-3">
                                <p class="font-mono text-xs font-semibold text-plum-700">{{ $rx->prescription_number }}</p>
                                @if($rx->is_repeat)
                                    <span class="text-[9px] font-semibold uppercase text-lilac-500">Repeat</span>
                                @endif
                            </td>
                            <td class="hidden px-4 py-3 text-sm text-plum-700 sm:table-cell">{{ $rx->patient?->full_name ?? '—' }}</td>
                            <td class="hidden px-4 py-3 text-sm text-plum-600 md:table-cell">{{ $rx->product?->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $rx->statusColour() }}">
                                    {{ $rx->statusLabel() }}
                                </span>
                            </td>
                            <td class="hidden px-4 py-3 text-xs text-plum-400 lg:table-cell">
                                {{ ($rx->signed_at ?? $rx->created_at)->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('prescriptions.show', $rx) }}" class="rounded-lg border border-plum-200 px-3 py-1.5 text-xs font-medium text-plum-600 hover:bg-plum-50">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-sm text-plum-400">No prescriptions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Batch sign button --}}
        @if(request('status') === \App\Models\Prescription::STATUS_PENDING_REVIEW)
            <div class="flex items-center justify-between" x-show="selected.length > 0" x-cloak>
                <p class="text-sm text-plum-600"><span x-text="selected.length"></span> prescription(s) selected</p>
                <button
                    type="submit"
                    form="batch-form"
                    class="rounded-xl bg-green-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-green-700"
                >
                    Sign Selected
                </button>
            </div>
        </form>
        @endif

        @if($prescriptions->hasPages())
            <div class="flex justify-center">{{ $prescriptions->links() }}</div>
        @endif

    </div>
</x-layouts.app>
