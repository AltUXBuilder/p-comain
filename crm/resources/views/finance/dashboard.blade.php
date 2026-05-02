<x-layouts.app>
    <x-slot name="pageTitle">Finance</x-slot>

    <div class="space-y-6 animate-fade-in">

        {{-- Stats — now includes ARR --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm">
                <p class="text-2xl font-bold text-plum-800">£{{ number_format($stats['total_revenue_30d'], 2) }}</p>
                <p class="text-xs text-plum-400">Revenue (30 days)</p>
            </div>
            <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm">
                <p class="text-2xl font-bold text-plum-800">£{{ number_format($stats['mrr'], 2) }}</p>
                <p class="text-xs text-plum-400">MRR (last month)</p>
            </div>
            <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm">
                <p class="text-2xl font-bold text-plum-800">£{{ number_format($stats['arr'], 2) }}</p>
                <p class="text-xs text-plum-400">ARR (MRR × 12)</p>
            </div>
            <div class="rounded-2xl border {{ $stats['dunning_count'] > 0 ? 'border-red-200 bg-red-50' : 'border-amber-100 bg-amber-50' }} p-5 shadow-plum-sm">
                <p class="text-2xl font-bold {{ $stats['dunning_count'] > 0 ? 'text-red-700' : 'text-amber-700' }}">{{ $stats['dunning_count'] }}</p>
                <p class="text-xs {{ $stats['dunning_count'] > 0 ? 'text-red-500' : 'text-amber-500' }}">Dunning queue</p>
            </div>
        </div>

        {{-- Quick links --}}
        <div class="flex gap-3 flex-wrap">
            <a href="{{ route('finance.invoices') }}"   class="rounded-xl border border-plum-200 px-4 py-2 text-sm font-medium text-plum-600 hover:bg-plum-50">Invoices</a>
            <a href="{{ route('finance.vat-report') }}" class="rounded-xl border border-plum-200 px-4 py-2 text-sm font-medium text-plum-600 hover:bg-plum-50">VAT Report</a>
            <a href="{{ route('finance.dunning') }}"    class="rounded-xl border border-plum-200 px-4 py-2 text-sm font-medium text-plum-600 hover:bg-plum-50 {{ $stats['dunning_count'] > 0 ? 'border-red-300 text-red-600' : '' }}">
                Dunning{{ $stats['dunning_count'] > 0 ? " ({$stats['dunning_count']})" : '' }}
            </a>
            <a href="{{ route('finance.refunds') }}"    class="rounded-xl border border-plum-200 px-4 py-2 text-sm font-medium text-plum-600 hover:bg-plum-50">Refunds &amp; Chargebacks</a>
        </div>

        <div class="grid gap-5 lg:grid-cols-2">

            {{-- Monthly revenue table --}}
            <div class="rounded-2xl border border-plum-100 bg-white shadow-plum-sm">
                <div class="border-b border-plum-50 px-5 py-4">
                    <h3 class="text-sm font-semibold text-plum-700">Revenue by Month (12 months)</h3>
                </div>
                <div class="divide-y divide-plum-50">
                    @forelse($revenueByMonth as $row)
                        <div class="flex items-center justify-between px-5 py-3">
                            <p class="text-sm text-plum-700">{{ \Carbon\Carbon::createFromFormat('Y-m', $row->month)->format('M Y') }}</p>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-plum-800">£{{ number_format($row->revenue, 2) }}</p>
                                <p class="text-xs text-plum-400">{{ $row->orders }} orders</p>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center text-sm text-plum-400">No revenue data yet.</div>
                    @endforelse
                </div>
            </div>

            {{-- Revenue by category --}}
            <div class="rounded-2xl border border-plum-100 bg-white shadow-plum-sm">
                <div class="border-b border-plum-50 px-5 py-4">
                    <h3 class="text-sm font-semibold text-plum-700">Revenue by Treatment Category</h3>
                </div>
                <div class="divide-y divide-plum-50">
                    @forelse($revenueByCategory as $row)
                        <div class="flex items-center justify-between px-5 py-3">
                            <p class="text-sm text-plum-700">{{ $row->category }}</p>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-plum-800">£{{ number_format($row->revenue, 2) }}</p>
                                <p class="text-xs text-plum-400">{{ $row->orders }} orders</p>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center text-sm text-plum-400">No data.</div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- Outstanding invoices --}}
        @if($outstanding->isNotEmpty())
        <div class="rounded-2xl border border-amber-200 bg-white shadow-plum-sm">
            <div class="border-b border-amber-100 px-5 py-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-amber-800">⚠ Outstanding Invoices (overdue 7+ days)</h3>
                <a href="{{ route('finance.dunning') }}" class="text-xs font-medium text-lilac-600 hover:text-lilac-800">Manage dunning →</a>
            </div>
            <table class="min-w-full divide-y divide-amber-50">
                <thead class="bg-amber-50/40"><tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-amber-600">Invoice</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-amber-600">Patient</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-amber-600">Total</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-amber-600">Issued</th>
                    <th class="px-4 py-3 text-right"></th>
                </tr></thead>
                <tbody class="divide-y divide-amber-50">
                    @foreach($outstanding->take(5) as $inv)
                        <tr class="hover:bg-amber-50/20">
                            <td class="px-4 py-3 font-mono text-xs font-semibold text-plum-700">{{ $inv->invoice_number }}</td>
                            <td class="px-4 py-3 text-sm text-plum-700">{{ $inv->patient?->full_name }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-plum-800">£{{ number_format($inv->total, 2) }}</td>
                            <td class="px-4 py-3 text-xs text-plum-400">{{ $inv->issued_at?->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('finance.invoices.pdf', $inv) }}" target="_blank" class="text-xs font-medium text-lilac-600 hover:text-lilac-800">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

    </div>
</x-layouts.app>
