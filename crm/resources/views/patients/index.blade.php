<x-layouts.app>
    <x-slot name="pageTitle">Patients</x-slot>

    <div class="space-y-5 animate-fade-in">

        {{-- Page header --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-plum-800">Patient Records</h2>
                <p class="mt-0.5 text-sm text-plum-400">{{ number_format($patients->total()) }} {{ Str::plural('patient', $patients->total()) }}</p>
            </div>
        </div>

        {{-- Search & filters --}}
        <div class="rounded-2xl border border-plum-100 bg-white p-4 shadow-plum-sm">
            <form method="GET" action="{{ route('patients.index') }}" class="space-y-3">

                {{-- Main search row --}}
                <div class="flex gap-3">
                    <div class="relative flex-1">
                        <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                            <svg class="size-4 text-plum-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803a7.5 7.5 0 0010.607 10.607z"/>
                            </svg>
                        </div>
                        <input
                            type="text"
                            name="search"
                            value="{{ $filters['search'] ?? '' }}"
                            placeholder="Name, email, or phone…"
                            class="block w-full rounded-xl border-plum-200 py-2.5 pl-9 pr-4 text-sm text-plum-800 placeholder:text-plum-300 focus:border-lilac-500 focus:ring-lilac-500"
                        >
                    </div>
                    <input
                        type="date"
                        name="dob"
                        value="{{ $filters['dob'] ?? '' }}"
                        title="Filter by date of birth"
                        class="rounded-xl border-plum-200 py-2.5 px-3 text-sm text-plum-700 focus:border-lilac-500 focus:ring-lilac-500"
                    >
                    <button
                        type="submit"
                        class="rounded-xl bg-plum-800 px-4 py-2.5 text-sm font-medium text-lilac-200 hover:bg-plum-900"
                    >Search</button>
                    @if(array_filter($filters))
                        <a href="{{ route('patients.index') }}" class="rounded-xl border border-plum-200 px-4 py-2.5 text-sm text-plum-500 hover:bg-plum-50">Clear</a>
                    @endif
                </div>

                {{-- Flag filters --}}
                <div class="flex flex-wrap gap-2">
                    <label class="flex cursor-pointer items-center gap-1.5 rounded-full border border-plum-200 px-3 py-1 text-xs hover:bg-plum-50">
                        <input type="checkbox" name="risk_flagged" value="1" class="size-3.5 rounded border-plum-300 text-lilac-500" {{ ($filters['risk_flagged'] ?? false) ? 'checked' : '' }}>
                        <span class="text-plum-600">Risk flagged</span>
                    </label>
                    <label class="flex cursor-pointer items-center gap-1.5 rounded-full border border-red-200 px-3 py-1 text-xs hover:bg-red-50">
                        <input type="checkbox" name="do_not_treat" value="1" class="size-3.5 rounded border-red-300 text-red-500" {{ ($filters['do_not_treat'] ?? false) ? 'checked' : '' }}>
                        <span class="text-red-600">Do Not Treat</span>
                    </label>
                    <label class="flex cursor-pointer items-center gap-1.5 rounded-full border border-plum-200 px-3 py-1 text-xs hover:bg-plum-50">
                        <input type="checkbox" name="deceased" value="1" class="size-3.5 rounded border-plum-300 text-plum-400" {{ ($filters['deceased'] ?? false) ? 'checked' : '' }}>
                        <span class="text-plum-400">Include deceased</span>
                    </label>
                </div>

            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-hidden rounded-2xl border border-plum-100 bg-white shadow-plum-sm">
            <table class="min-w-full divide-y divide-plum-100">
                <thead class="bg-plum-50/60">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Patient</th>
                        <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 sm:table-cell">DOB</th>
                        <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 md:table-cell">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Flags</th>
                        <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 lg:table-cell">Joined</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-plum-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-plum-50">
                    @forelse($patients as $patient)
                        <tr class="{{ $patient->do_not_treat ? 'bg-red-50/30' : ($patient->deceased ? 'bg-plum-50/30 opacity-60' : 'hover:bg-plum-50/20') }} transition">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-plum-100 text-xs font-semibold text-plum-700">
                                        {{ strtoupper(substr($patient->first_name, 0, 1) . substr($patient->last_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('patients.show', $patient) }}" class="text-sm font-medium text-plum-800 hover:text-lilac-700">
                                            {{ $patient->full_name }}
                                        </a>
                                        @if($patient->deceased)
                                            <span class="ml-1 text-xs text-plum-400">†</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="hidden px-4 py-3 text-sm text-plum-600 sm:table-cell">
                                {{ $patient->date_of_birth?->format('d M Y') ?? '—' }}
                                @if($patient->age) <span class="text-xs text-plum-400">({{ $patient->age }})</span> @endif
                            </td>
                            <td class="hidden px-4 py-3 text-sm text-plum-500 md:table-cell">{{ $patient->email }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    @if($patient->do_not_treat)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-red-700">
                                            <svg class="size-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                                            DNT
                                        </span>
                                    @endif
                                    @if($patient->risk_flagged)
                                        <span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-amber-700">Flagged</span>
                                    @endif
                                </div>
                            </td>
                            <td class="hidden px-4 py-3 text-xs text-plum-400 lg:table-cell">
                                {{ $patient->created_at->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a
                                    href="{{ route('patients.show', $patient) }}"
                                    class="inline-flex items-center gap-1 rounded-lg border border-plum-200 px-3 py-1.5 text-xs font-medium text-plum-600 hover:bg-plum-50 hover:text-plum-800"
                                >
                                    View
                                    <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-sm text-plum-400">
                                No patients found matching your search.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($patients->hasPages())
            <div class="flex justify-center">
                {{ $patients->links('vendor.pagination.tailwind') }}
            </div>
        @endif

    </div>
</x-layouts.app>
