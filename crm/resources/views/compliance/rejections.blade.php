<x-layouts.app>
    <x-slot name="pageTitle">Rejection Register</x-slot>
    <div class="space-y-5 animate-fade-in">
        <div class="flex items-center justify-between">
            <a href="{{ route('compliance.index') }}" class="text-sm font-medium text-lilac-600 hover:text-lilac-800">← Compliance</a>
            <a href="{{ route('compliance.rejections.export', request()->query()) }}" class="flex items-center gap-2 rounded-xl bg-plum-800 px-4 py-2 text-sm font-medium text-lilac-200 hover:bg-plum-900">Export CSV</a>
        </div>
        <div class="rounded-2xl border border-plum-100 bg-white p-4 shadow-plum-sm">
            <form method="GET" action="{{ route('compliance.rejections') }}" class="flex gap-3">
                <input type="date" name="from" value="{{ request('from') }}" class="rounded-xl border-plum-200 py-2 px-3 text-sm">
                <input type="date" name="to" value="{{ request('to') }}" class="rounded-xl border-plum-200 py-2 px-3 text-sm">
                <button type="submit" class="rounded-xl bg-plum-800 px-4 py-2 text-sm font-medium text-lilac-200">Filter</button>
            </form>
        </div>
        <div class="overflow-hidden rounded-2xl border border-plum-100 bg-white shadow-plum-sm">
            <table class="min-w-full divide-y divide-plum-100">
                <thead class="bg-plum-50/60"><tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Patient</th>
                    <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 sm:table-cell">Prescriber (GPhC)</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Reason</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Notified</th>
                </tr></thead>
                <tbody class="divide-y divide-plum-50">
                    @forelse($rejections as $r)
                        <tr class="hover:bg-plum-50/10">
                            <td class="px-4 py-3 text-xs text-plum-500">{{ $r->created_at->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-sm text-plum-800">{{ $r->consultation?->patient?->full_name ?? '—' }}</td>
                            <td class="hidden px-4 py-3 sm:table-cell">
                                <p class="text-sm text-plum-700">{{ $r->prescriber?->full_name ?? '—' }}</p>
                                @if($r->gphc_number) <p class="font-mono text-xs text-plum-400">{{ $r->gphc_number }}</p> @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-plum-500 max-w-xs">{{ Str::limit($r->reason, 80) }}</td>
                            <td class="px-4 py-3 text-xs {{ $r->patient_notified ? 'text-green-600' : 'text-plum-400' }}">{{ $r->patient_notified ? 'Yes' : 'No' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-sm text-plum-400">No rejections on record.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($rejections->hasPages()) <div class="flex justify-center">{{ $rejections->links() }}</div> @endif
    </div>
</x-layouts.app>
