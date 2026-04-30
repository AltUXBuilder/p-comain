<x-layouts.app>
    <x-slot name="pageTitle">Consultation Queue</x-slot>

    <div class="space-y-5 animate-fade-in">

        {{-- Stats strip --}}
        <div class="grid grid-cols-3 gap-3">
            <div class="rounded-2xl border border-plum-100 bg-white p-4 text-center shadow-plum-sm">
                <p class="text-2xl font-bold text-plum-800">{{ $stats['awaiting'] }}</p>
                <p class="text-xs text-plum-400">Awaiting review</p>
            </div>
            <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4 text-center shadow-plum-sm">
                <p class="text-2xl font-bold text-amber-700">{{ $stats['flagged'] }}</p>
                <p class="text-xs text-amber-500">Flagged</p>
            </div>
            <div class="rounded-2xl border border-plum-100 bg-white p-4 text-center shadow-plum-sm">
                <p class="text-2xl font-bold text-plum-800">{{ $stats['today'] }}</p>
                <p class="text-xs text-plum-400">New today</p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="rounded-2xl border border-plum-100 bg-white p-4 shadow-plum-sm">
            <form method="GET" action="{{ route('consultations.queue') }}" class="flex flex-wrap gap-3">
                <label class="flex items-center gap-1.5 text-sm text-plum-600">
                    <input type="checkbox" name="mine" value="1" class="size-3.5 rounded border-plum-300 text-lilac-500" {{ request()->boolean('mine') ? 'checked' : '' }}>
                    My queue only
                </label>
                <button type="submit" class="rounded-xl bg-plum-800 px-4 py-1.5 text-xs font-medium text-lilac-200 hover:bg-plum-900">Filter</button>
                <a href="{{ route('consultations.queue') }}" class="rounded-xl border border-plum-200 px-4 py-1.5 text-xs text-plum-500 hover:bg-plum-50">Reset</a>
            </form>
        </div>

        {{-- Queue table --}}
        <div class="overflow-hidden rounded-2xl border border-plum-100 bg-white shadow-plum-sm">
            <table class="min-w-full divide-y divide-plum-100">
                <thead class="bg-plum-50/60">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Patient</th>
                        <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 sm:table-cell">Treatment</th>
                        <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 md:table-cell">Submitted</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Wait</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-plum-500"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-plum-50">
                    @forelse($consultations as $c)
                        <tr class="hover:bg-plum-50/20 transition">
                            <td class="px-4 py-3">
                                <div>
                                    <p class="text-sm font-medium text-plum-800">{{ $c->patient?->full_name ?? '—' }}</p>
                                    <p class="text-xs text-plum-400">{{ $c->patient?->email }}</p>
                                </div>
                            </td>
                            <td class="hidden px-4 py-3 text-sm text-plum-600 sm:table-cell">
                                {{ $c->product?->name ?? '—' }}
                            </td>
                            <td class="hidden px-4 py-3 text-xs text-plum-400 md:table-cell">
                                {{ $c->created_at->format('d M Y, H:i') }}
                            </td>
                            <td class="px-4 py-3">
                                @php $hours = $c->created_at->diffInHours(now()); @endphp
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium
                                    {{ $hours > 48 ? 'bg-red-100 text-red-700' : ($hours > 24 ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700') }}">
                                    {{ $hours > 48 ? $c->created_at->diffForHumans() : ($hours . 'h') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a
                                    href="{{ route('consultations.show', $c) }}"
                                    class="inline-flex items-center gap-1 rounded-lg bg-plum-800 px-3 py-1.5 text-xs font-medium text-lilac-200 hover:bg-plum-900"
                                >
                                    Review
                                    <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center">
                                <p class="text-sm font-medium text-plum-700">Queue is clear</p>
                                <p class="mt-1 text-xs text-plum-400">No consultations awaiting review.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($consultations->hasPages())
            <div class="flex justify-center">{{ $consultations->links() }}</div>
        @endif

    </div>
</x-layouts.app>
