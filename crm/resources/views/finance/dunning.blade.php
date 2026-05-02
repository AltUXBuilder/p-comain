<x-layouts.app>
    <x-slot name="pageTitle">Dunning Management</x-slot>
    <div class="space-y-5 animate-fade-in">

        <div class="flex items-center justify-between">
            <a href="{{ route('finance.dashboard') }}" class="text-sm font-medium text-lilac-600 hover:text-lilac-800">← Finance</a>
            <p class="text-xs text-plum-400">{{ $queue->count() }} invoice(s) overdue</p>
        </div>

        @if($queue->isEmpty())
            <div class="rounded-2xl border border-green-200 bg-green-50 p-10 text-center">
                <p class="text-sm font-medium text-green-700">✓ No overdue invoices. All payments up to date.</p>
            </div>
        @else
            <div class="overflow-hidden rounded-2xl border border-plum-100 bg-white shadow-plum-sm">
                <table class="min-w-full divide-y divide-plum-100">
                    <thead class="bg-plum-50/60"><tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Invoice</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Patient</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Amount</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Overdue</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Stage</th>
                        <th class="px-4 py-3 text-right"></th>
                    </tr></thead>
                    <tbody class="divide-y divide-plum-50">
                        @foreach($queue as $inv)
                            <tr class="hover:bg-plum-50/10">
                                <td class="px-4 py-3 font-mono text-xs font-semibold text-plum-700">{{ $inv->invoice_number }}</td>
                                <td class="px-4 py-3 text-sm text-plum-700">{{ $inv->patient?->full_name }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-plum-800">£{{ number_format($inv->total, 2) }}</td>
                                <td class="px-4 py-3 text-sm {{ $inv->days_overdue >= 30 ? 'font-semibold text-red-600' : 'text-amber-600' }}">
                                    {{ $inv->days_overdue }} days
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold
                                        {{ match($inv->dunning_tier) {
                                            'final'  => 'bg-red-100 text-red-700',
                                            'second' => 'bg-amber-100 text-amber-700',
                                            default  => 'bg-blue-100 text-blue-700',
                                        } }}">
                                        {{ ucfirst($inv->dunning_tier) }} notice
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        @if($inv->stripe_invoice_id)
                                            <form method="POST" action="{{ route('finance.dunning.retry', $inv) }}">
                                                @csrf
                                                <button type="submit" class="rounded-lg border border-green-200 bg-green-50 px-3 py-1.5 text-xs font-medium text-green-700 hover:bg-green-100">
                                                    Retry payment
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('finance.invoices.pdf', $inv) }}" target="_blank"
                                           class="rounded-lg border border-plum-200 px-3 py-1.5 text-xs font-medium text-plum-600 hover:bg-plum-50">
                                            View invoice
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-layouts.app>
