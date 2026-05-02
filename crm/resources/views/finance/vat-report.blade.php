<x-layouts.app>
    <x-slot name="pageTitle">VAT Report</x-slot>
    <div class="max-w-2xl space-y-5 animate-fade-in">
        <div class="flex items-center justify-between">
            <a href="{{ route('finance.dashboard') }}" class="text-sm font-medium text-lilac-600 hover:text-lilac-800">← Finance</a>
            <a href="{{ route('finance.vat-report.export', request()->query()) }}" class="flex items-center gap-2 rounded-xl bg-plum-800 px-4 py-2 text-sm font-medium text-lilac-200 hover:bg-plum-900">Export CSV</a>
        </div>
        <div class="rounded-2xl border border-plum-100 bg-white p-4 shadow-plum-sm">
            <form method="GET" action="{{ route('finance.vat-report') }}" class="flex gap-3">
                <div><label class="mb-1 block text-xs font-medium text-plum-500">From</label><input type="date" name="from" value="{{ request('from') }}" class="rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500"></div>
                <div><label class="mb-1 block text-xs font-medium text-plum-500">To</label><input type="date" name="to" value="{{ request('to') }}" class="rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500"></div>
                <div class="flex items-end"><button type="submit" class="rounded-xl bg-plum-800 px-4 py-2 text-sm font-medium text-lilac-200 hover:bg-plum-900">Apply</button></div>
            </form>
        </div>
        <div class="rounded-2xl border border-plum-100 bg-white shadow-plum-sm overflow-hidden">
            <div class="border-b border-plum-50 px-5 py-4">
                <h3 class="text-sm font-semibold text-plum-700">VAT Summary: {{ $report['period_from'] }} – {{ $report['period_to'] }}</h3>
            </div>
            <table class="min-w-full divide-y divide-plum-100">
                <thead class="bg-plum-50/60"><tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">VAT Rate</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-plum-500">Net Revenue</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-plum-500">VAT Amount</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-plum-500">Gross</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-plum-500">Lines</th>
                </tr></thead>
                <tbody class="divide-y divide-plum-50">
                    @forelse($report['rows'] as $row)
                        <tr class="hover:bg-plum-50/10">
                            <td class="px-4 py-3 text-sm text-plum-700">{{ number_format($row->vat_rate, 2) }}%
                                @if($row->vat_rate == 0) <span class="text-xs text-plum-400">(Zero-rated)</span> @endif
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-medium text-plum-800">£{{ number_format($row->net, 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm text-plum-700">£{{ number_format($row->vat_total, 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm text-plum-700">£{{ number_format($row->net + $row->vat_total, 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm text-plum-500">{{ $row->line_items }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-plum-400">No data for this period.</td></tr>
                    @endforelse
                    @if($report['rows']->isNotEmpty())
                    <tr class="bg-plum-50 font-semibold">
                        <td class="px-4 py-3 text-sm text-plum-800">Total</td>
                        <td class="px-4 py-3 text-right text-sm text-plum-800">£{{ number_format($report['total_net'], 2) }}</td>
                        <td class="px-4 py-3 text-right text-sm text-plum-800">£{{ number_format($report['total_vat'], 2) }}</td>
                        <td class="px-4 py-3 text-right text-sm text-plum-800">£{{ number_format($report['total_gross'], 2) }}</td>
                        <td></td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <p class="text-xs text-plum-400">Note: Most POMs are zero-rated for VAT purposes. Non-zero rates apply to non-prescription items.</p>
    </div>
</x-layouts.app>
