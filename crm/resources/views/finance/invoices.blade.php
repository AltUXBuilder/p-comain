<x-layouts.app>
    <x-slot name="pageTitle">Invoices</x-slot>
    <div class="space-y-5 animate-fade-in">
        <div class="flex items-center justify-between">
            <a href="{{ route('finance.dashboard') }}" class="text-sm font-medium text-lilac-600 hover:text-lilac-800">← Finance</a>
        </div>
        <div class="rounded-2xl border border-plum-100 bg-white p-4 shadow-plum-sm">
            <form method="GET" action="{{ route('finance.invoices') }}" class="flex gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Invoice number or patient…" class="flex-1 rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                <select name="status" class="rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                    <option value="">All statuses</option>
                    @foreach(\App\Models\Invoice::STATUSES as $k => $v)
                        <option value="{{ $k }}" {{ request('status') === $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-xl bg-plum-800 px-4 py-2 text-sm font-medium text-lilac-200 hover:bg-plum-900">Search</button>
            </form>
        </div>
        <div class="overflow-hidden rounded-2xl border border-plum-100 bg-white shadow-plum-sm">
            <table class="min-w-full divide-y divide-plum-100">
                <thead class="bg-plum-50/60"><tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Invoice</th>
                    <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 sm:table-cell">Patient</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Total</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Status</th>
                    <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 md:table-cell">Issued</th>
                    <th class="px-4 py-3 text-right"></th>
                </tr></thead>
                <tbody class="divide-y divide-plum-50">
                    @forelse($invoices as $inv)
                        <tr class="hover:bg-plum-50/10">
                            <td class="px-4 py-3 font-mono text-xs font-semibold text-plum-700">{{ $inv->invoice_number }}</td>
                            <td class="hidden px-4 py-3 text-sm text-plum-700 sm:table-cell">{{ $inv->patient?->full_name }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-plum-800">£{{ number_format($inv->total, 2) }}</td>
                            <td class="px-4 py-3"><span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $inv->statusColour() }}">{{ $inv->status }}</span></td>
                            <td class="hidden px-4 py-3 text-xs text-plum-400 md:table-cell">{{ $inv->issued_at?->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-right flex justify-end gap-2">
                                <a href="{{ route('finance.invoices.pdf', $inv) }}" target="_blank" class="text-xs font-medium text-lilac-600 hover:text-lilac-800">PDF</a>
                                @if($inv->status !== 'void' && $inv->status !== 'paid')
                                <form method="POST" action="{{ route('finance.invoices.void', $inv) }}">
                                    @csrf
                                    <button type="submit" class="text-xs text-red-500 hover:text-red-700">Void</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-sm text-plum-400">No invoices found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($invoices->hasPages()) <div class="flex justify-center">{{ $invoices->links() }}</div> @endif
    </div>
</x-layouts.app>
