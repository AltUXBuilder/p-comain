<x-layouts.app>
    <x-slot name="pageTitle">Private Prescription Register</x-slot>

    <div class="space-y-5 animate-fade-in">

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-plum-800">Private Prescription Register</h2>
                <p class="mt-0.5 text-xs text-plum-400">GPhC inspection ready · {{ $prescriptions->total() }} records</p>
            </div>
            <a
                href="{{ route('prescriptions.register.export', request()->query()) }}"
                class="flex items-center gap-2 rounded-xl bg-plum-800 px-4 py-2 text-sm font-medium text-lilac-200 hover:bg-plum-900"
            >
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                </svg>
                Export CSV
            </a>
        </div>

        {{-- Filters --}}
        <div class="rounded-2xl border border-plum-100 bg-white p-4 shadow-plum-sm">
            <form method="GET" action="{{ route('prescriptions.register') }}" class="flex flex-wrap gap-3">
                <div>
                    <label class="mb-1 block text-xs font-medium text-plum-500">From</label>
                    <input type="date" name="from" value="{{ request('from') }}"
                        class="rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-plum-500">To</label>
                    <input type="date" name="to" value="{{ request('to') }}"
                        class="rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-plum-500">Prescriber</label>
                    <select name="prescriber_id" class="rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                        <option value="">All prescribers</option>
                        @foreach($prescribers as $p)
                            <option value="{{ $p->id }}" {{ request('prescriber_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->full_name }} ({{ $p->gphc_number }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-plum-500">Category</label>
                    <select name="category_id" class="rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                        <option value="">All categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="rounded-xl bg-plum-800 px-4 py-2 text-sm font-medium text-lilac-200 hover:bg-plum-900">Filter</button>
                </div>
                @if(request()->hasAny(['from', 'to', 'prescriber_id', 'category_id']))
                    <div class="flex items-end">
                        <a href="{{ route('prescriptions.register') }}" class="rounded-xl border border-plum-200 px-4 py-2 text-sm text-plum-500 hover:bg-plum-50">Clear</a>
                    </div>
                @endif
            </form>
        </div>

        {{-- Register table --}}
        <div class="overflow-hidden rounded-2xl border border-plum-100 bg-white shadow-plum-sm">
            <table class="min-w-full divide-y divide-plum-100 text-sm">
                <thead class="bg-plum-50/60">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Rx No.</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Date</th>
                        <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 sm:table-cell">Patient</th>
                        <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 md:table-cell">Medication</th>
                        <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 lg:table-cell">Prescriber (GPhC)</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Status</th>
                        <th class="px-4 py-3 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-plum-50">
                    @forelse($prescriptions as $rx)
                        <tr class="hover:bg-plum-50/20">
                            <td class="px-4 py-3 font-mono text-xs font-semibold text-plum-700">{{ $rx->prescription_number }}</td>
                            <td class="px-4 py-3 text-xs text-plum-500">{{ $rx->signed_at?->format('d/m/Y') ?? '—' }}</td>
                            <td class="hidden px-4 py-3 sm:table-cell">
                                <p class="font-medium text-plum-800">{{ $rx->patient?->full_name }}</p>
                                <p class="text-xs text-plum-400">DOB {{ $rx->patient?->date_of_birth?->format('d/m/Y') }}</p>
                            </td>
                            <td class="hidden px-4 py-3 text-plum-600 md:table-cell">
                                {{ $rx->product?->name }}
                                <span class="text-xs text-plum-400">{{ $rx->product?->strength }}</span>
                            </td>
                            <td class="hidden px-4 py-3 lg:table-cell">
                                <p class="text-plum-700">{{ $rx->prescriber_name }}</p>
                                <p class="font-mono text-xs text-plum-400">{{ $rx->prescriber_gphc_number }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $rx->statusColour() }}">{{ $rx->statusLabel() }}</span>
                                @if($rx->is_repeat)
                                    <span class="ml-1 text-[9px] font-semibold text-lilac-500 uppercase">R</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('prescriptions.show', $rx) }}" class="text-xs font-medium text-lilac-600 hover:text-lilac-800">View</a>
                                @if($rx->pdf_path)
                                    <a href="{{ route('prescriptions.download', $rx) }}" class="ml-3 text-xs font-medium text-plum-500 hover:text-plum-700">PDF</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-sm text-plum-400">No prescriptions match the current filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($prescriptions->hasPages())
            <div class="flex justify-center">{{ $prescriptions->links() }}</div>
        @endif

    </div>
</x-layouts.app>
