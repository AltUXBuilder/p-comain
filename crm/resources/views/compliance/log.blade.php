<x-layouts.app>
    <x-slot name="pageTitle">Compliance Log</x-slot>
    <div class="space-y-5 animate-fade-in">
        <a href="{{ route('compliance.index') }}" class="text-sm font-medium text-lilac-600 hover:text-lilac-800">← Compliance</a>
        <div class="rounded-2xl border border-plum-100 bg-white p-4 shadow-plum-sm">
            <form method="GET" action="{{ route('compliance.log') }}" class="flex flex-wrap gap-3">
                <select name="type" class="rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                    <option value="">All types</option>
                    @foreach($types as $k => $v)
                        <option value="{{ $k }}" {{ request('type') === $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
                <input type="date" name="from" value="{{ request('from') }}" class="rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                <input type="date" name="to" value="{{ request('to') }}" class="rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                <button type="submit" class="rounded-xl bg-plum-800 px-4 py-2 text-sm font-medium text-lilac-200 hover:bg-plum-900">Filter</button>
            </form>
        </div>
        <div class="overflow-hidden rounded-2xl border border-plum-100 bg-white shadow-plum-sm">
            <table class="min-w-full divide-y divide-plum-100">
                <thead class="bg-plum-50/60"><tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Type</th>
                    <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 sm:table-cell">Patient</th>
                    <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 md:table-cell">Staff</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Notes</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-plum-500">Date</th>
                </tr></thead>
                <tbody class="divide-y divide-plum-50">
                    @forelse($logs as $log)
                        <tr class="hover:bg-plum-50/10">
                            <td class="px-4 py-3 text-xs font-semibold text-plum-700">{{ $log->typeLabel() }}</td>
                            <td class="hidden px-4 py-3 text-sm text-plum-600 sm:table-cell">{{ $log->patient?->full_name ?? '—' }}</td>
                            <td class="hidden px-4 py-3 text-sm text-plum-500 md:table-cell">{{ $log->staff?->full_name ?? 'System' }}</td>
                            <td class="px-4 py-3 text-xs text-plum-500 max-w-xs truncate">{{ $log->notes }}</td>
                            <td class="px-4 py-3 text-right text-xs text-plum-400">{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-sm text-plum-400">No entries.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages()) <div class="flex justify-center">{{ $logs->links() }}</div> @endif
    </div>
</x-layouts.app>
