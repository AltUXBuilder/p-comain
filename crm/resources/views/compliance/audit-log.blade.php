<x-layouts.app>
    <x-slot name="pageTitle">Audit Log</x-slot>
    <div class="space-y-5 animate-fade-in">
        <div class="flex items-center justify-between">
            <a href="{{ route('compliance.index') }}" class="text-sm font-medium text-lilac-600 hover:text-lilac-800">← Compliance</a>
            <a href="{{ route('compliance.audit-log.export', request()->query()) }}" class="flex items-center gap-2 rounded-xl bg-plum-800 px-4 py-2 text-sm font-medium text-lilac-200 hover:bg-plum-900">Export CSV</a>
        </div>
        <div class="rounded-2xl border border-plum-100 bg-white p-4 shadow-plum-sm">
            <form method="GET" action="{{ route('compliance.audit-log') }}" class="flex flex-wrap gap-3">
                <input type="text" name="action" value="{{ request('action') }}" placeholder="Filter by action…" class="rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                <select name="staff_id" class="rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                    <option value="">All staff</option>
                    @foreach($staff as $s)
                        <option value="{{ $s->id }}" {{ request('staff_id') == $s->id ? 'selected' : '' }}>{{ $s->full_name }}</option>
                    @endforeach
                </select>
                <input type="date" name="from" value="{{ request('from') }}" class="rounded-xl border-plum-200 py-2 px-3 text-sm">
                <input type="date" name="to" value="{{ request('to') }}" class="rounded-xl border-plum-200 py-2 px-3 text-sm">
                <button type="submit" class="rounded-xl bg-plum-800 px-4 py-2 text-sm font-medium text-lilac-200">Filter</button>
            </form>
        </div>
        <div class="overflow-hidden rounded-2xl border border-plum-100 bg-white shadow-plum-sm">
            <table class="min-w-full divide-y divide-plum-100">
                <thead class="bg-plum-50/60"><tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Timestamp</th>
                    <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 sm:table-cell">Staff</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Action</th>
                    <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 md:table-cell">Entity</th>
                    <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 lg:table-cell">IP</th>
                </tr></thead>
                <tbody class="divide-y divide-plum-50">
                    @forelse($logs as $log)
                        <tr class="hover:bg-plum-50/10">
                            <td class="px-4 py-3 text-xs text-plum-400">{{ $log->created_at->format('d M Y, H:i:s') }}</td>
                            <td class="hidden px-4 py-3 sm:table-cell">
                                <p class="text-xs font-medium text-plum-700">{{ $log->staff?->full_name ?? 'System' }}</p>
                                @if($log->gphc_number) <p class="font-mono text-[10px] text-plum-400">{{ $log->gphc_number }}</p> @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-full bg-plum-100 px-2 py-0.5 font-mono text-[10px] font-medium text-plum-700">{{ $log->action }}</span>
                            </td>
                            <td class="hidden px-4 py-3 text-xs text-plum-500 md:table-cell">
                                {{ $log->entity_type }}{{ $log->entity_id ? " #{$log->entity_id}" : '' }}
                            </td>
                            <td class="hidden px-4 py-3 font-mono text-[10px] text-plum-400 lg:table-cell">{{ $log->ip_address }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-sm text-plum-400">No audit entries found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages()) <div class="flex justify-center">{{ $logs->links() }}</div> @endif
    </div>
</x-layouts.app>
