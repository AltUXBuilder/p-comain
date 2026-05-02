<x-layouts.app>
    <x-slot name="pageTitle">Prescription PDF Archive</x-slot>

    <div class="space-y-5 animate-fade-in">

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-plum-800">Prescription PDF Archive</h2>
                <p class="mt-0.5 text-xs text-plum-400">{{ $docs->total() }} prescription PDF(s) on file — retained indefinitely for regulatory compliance</p>
            </div>
            <a href="{{ route('prescriptions.register') }}" class="rounded-xl border border-plum-200 px-4 py-2 text-sm font-medium text-plum-600 hover:bg-plum-50">
                Prescription Register →
            </a>
        </div>

        {{-- Filters --}}
        <div class="rounded-2xl border border-plum-100 bg-white p-4 shadow-plum-sm">
            <form method="GET" action="{{ route('documents.prescriptions') }}" class="flex flex-wrap gap-3">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Rx number or patient name…"
                    class="flex-1 rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                <input type="date" name="from" value="{{ request('from') }}"
                    class="rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                <input type="date" name="to" value="{{ request('to') }}"
                    class="rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                <button type="submit" class="rounded-xl bg-plum-800 px-4 py-2 text-sm font-medium text-lilac-200 hover:bg-plum-900">Search</button>
                @if(request()->hasAny(['search', 'from', 'to']))
                    <a href="{{ route('documents.prescriptions') }}" class="rounded-xl border border-plum-200 px-4 py-2 text-sm text-plum-500 hover:bg-plum-50">Clear</a>
                @endif
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-hidden rounded-2xl border border-plum-100 bg-white shadow-plum-sm">
            <table class="min-w-full divide-y divide-plum-100">
                <thead class="bg-plum-50/60">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Filename</th>
                        <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 sm:table-cell">Patient</th>
                        <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 md:table-cell">Size</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Archived</th>
                        <th class="px-4 py-3 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-plum-50">
                    @forelse($docs as $doc)
                        <tr class="hover:bg-plum-50/10">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-plum-100">
                                        <svg class="size-3.5 text-plum-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <p class="font-mono text-xs font-semibold text-plum-700">{{ $doc->name }}</p>
                                </div>
                            </td>
                            <td class="hidden px-4 py-3 text-sm text-plum-700 sm:table-cell">{{ $doc->patient?->full_name ?? '—' }}</td>
                            <td class="hidden px-4 py-3 text-xs text-plum-400 md:table-cell">{{ $doc->sizeFormatted() }}</td>
                            <td class="px-4 py-3 text-xs text-plum-400">{{ $doc->created_at->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('documents.view', $doc) }}" target="_blank"
                                    class="mr-2 text-xs font-medium text-lilac-600 hover:text-lilac-800">View</a>
                                <a href="{{ route('documents.download', $doc) }}"
                                    class="text-xs font-medium text-plum-500 hover:text-plum-700">Download</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-sm text-plum-400">No prescription PDFs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($docs->hasPages())
            <div class="flex justify-center">{{ $docs->links() }}</div>
        @endif

    </div>
</x-layouts.app>
