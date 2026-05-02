<x-layouts.app>
    <x-slot name="pageTitle">DSAR Requests</x-slot>
    <div class="space-y-5 animate-fade-in">

        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('compliance.index') }}" class="text-sm font-medium text-lilac-600 hover:text-lilac-800">← Compliance</a>
                @if($overdue > 0)
                    <p class="mt-1 text-xs font-semibold text-red-600">⚠ {{ $overdue }} request(s) overdue (GDPR 30-day limit)</p>
                @endif
            </div>
            <a href="{{ route('compliance.dsar.create') }}" class="flex items-center gap-2 rounded-xl bg-plum-800 px-4 py-2 text-sm font-medium text-lilac-200 hover:bg-plum-900">
                + Log DSAR
            </a>
        </div>

        <div class="flex gap-2">
            @foreach(array_merge(['all' => 'All'], \App\Models\DsarRequest::STATUSES) as $k => $v)
                <a href="{{ route('compliance.dsar.index', $k !== 'all' ? ['status' => $k] : []) }}"
                    @class(['rounded-xl px-3 py-1.5 text-xs font-medium border transition',
                        'bg-plum-800 text-lilac-200 border-plum-800' => request('status', 'all') === $k,
                        'border-plum-200 text-plum-600 hover:bg-plum-50' => request('status', 'all') !== $k])>
                    {{ $v }}
                </a>
            @endforeach
        </div>

        <div class="overflow-hidden rounded-2xl border border-plum-100 bg-white shadow-plum-sm">
            <table class="min-w-full divide-y divide-plum-100">
                <thead class="bg-plum-50/60"><tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">DSAR #</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Type</th>
                    <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 sm:table-cell">Requestor</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Due</th>
                    <th class="px-4 py-3 text-right"></th>
                </tr></thead>
                <tbody class="divide-y divide-plum-50">
                    @forelse($requests as $dsar)
                        <tr class="hover:bg-plum-50/20 {{ $dsar->isOverdue() ? 'bg-red-50/30' : '' }}">
                            <td class="px-4 py-3 font-mono text-xs font-semibold text-plum-600">#{{ $dsar->id }}</td>
                            <td class="px-4 py-3 text-sm text-plum-800">{{ \App\Models\DsarRequest::TYPES[$dsar->type] ?? $dsar->type }}</td>
                            <td class="hidden px-4 py-3 text-sm text-plum-600 sm:table-cell">{{ $dsar->requestor_email }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $dsar->statusColour() }}">
                                    {{ \App\Models\DsarRequest::STATUSES[$dsar->status] ?? $dsar->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm {{ $dsar->isOverdue() ? 'font-semibold text-red-600' : 'text-plum-600' }}">
                                {{ $dsar->due_at->format('d M Y') }}
                                @if($dsar->isOverdue()) <span class="text-xs">(overdue)</span> @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('compliance.dsar.show', $dsar) }}" class="rounded-lg border border-plum-200 px-3 py-1.5 text-xs font-medium text-plum-600 hover:bg-plum-50">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-sm text-plum-400">No DSAR requests on record.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($requests->hasPages()) <div class="flex justify-center">{{ $requests->links() }}</div> @endif
    </div>
</x-layouts.app>
