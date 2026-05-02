<x-layouts.app>
    <x-slot name="pageTitle">Refunds & Chargebacks</x-slot>
    <div class="space-y-5 animate-fade-in" x-data="{ showRefund: null }">

        <a href="{{ route('finance.dashboard') }}" class="text-sm font-medium text-lilac-600 hover:text-lilac-800">← Finance</a>

        {{-- Chargeback alerts --}}
        @if($chargebacks->isNotEmpty())
            <div class="rounded-2xl border border-red-200 bg-red-50 p-4 shadow-plum-sm">
                <p class="mb-3 text-sm font-semibold text-red-800">⚠ Stripe Chargeback Disputes</p>
                <div class="space-y-2">
                    @foreach($chargebacks as $cb)
                        <div class="flex items-center justify-between rounded-xl border border-red-200 bg-white px-4 py-2.5">
                            <p class="text-xs text-red-700">{{ $cb->message }}</p>
                            <p class="text-xs text-plum-400">{{ $cb->created_at->format('d M Y') }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Refunded orders --}}
        <div class="overflow-hidden rounded-2xl border border-plum-100 bg-white shadow-plum-sm">
            <div class="border-b border-plum-50 px-5 py-4">
                <h3 class="text-sm font-semibold text-plum-700">Refunded Orders</h3>
            </div>
            <table class="min-w-full divide-y divide-plum-100">
                <thead class="bg-plum-50/60"><tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Order</th>
                    <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 sm:table-cell">Patient</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Total</th>
                    <th class="px-4 py-3 text-right"></th>
                </tr></thead>
                <tbody class="divide-y divide-plum-50">
                    @forelse($refunded as $order)
                        <tr class="hover:bg-plum-50/10">
                            <td class="px-4 py-3 font-mono text-xs font-semibold text-plum-600">{{ $order->order_number }}</td>
                            <td class="hidden px-4 py-3 text-sm text-plum-700 sm:table-cell">{{ $order->patient?->full_name }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-plum-800">£{{ number_format($order->total, 2) }}</td>
                            <td class="px-4 py-3 text-right">
                                <button @click="showRefund = {{ $order->id }}"
                                    class="rounded-lg border border-plum-200 px-3 py-1.5 text-xs font-medium text-plum-600 hover:bg-plum-50">
                                    Issue refund
                                </button>

                                {{-- Inline refund form --}}
                                <div x-show="showRefund === {{ $order->id }}" x-cloak
                                     class="mt-2 rounded-xl border border-plum-100 bg-plum-50/60 p-3 text-left">
                                    <form method="POST" action="{{ route('finance.refund', $order) }}" class="space-y-2">
                                        @csrf
                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <label class="mb-0.5 block text-xs font-medium text-plum-600">Amount (£)</label>
                                                <input type="number" name="amount_pence"
                                                    step="0.01" min="0.01" :max="{{ $order->total }}"
                                                    placeholder="{{ $order->total }}"
                                                    class="block w-full rounded-lg border-plum-200 py-1.5 px-2 text-xs focus:border-lilac-500 focus:ring-lilac-500"
                                                    x-ref="amt{{ $order->id }}"
                                                    @input="$el.dataset.pence = Math.round($el.value * 100)">
                                                <p class="mt-0.5 text-[10px] text-plum-400">Value entered will be converted to pence.</p>
                                            </div>
                                            <div>
                                                <label class="mb-0.5 block text-xs font-medium text-plum-600">Reason</label>
                                                <select name="reason"
                                                    class="block w-full rounded-lg border-plum-200 py-1.5 px-2 text-xs focus:border-lilac-500 focus:ring-lilac-500">
                                                    <option value="requested_by_customer">Customer request</option>
                                                    <option value="duplicate">Duplicate charge</option>
                                                    <option value="fraudulent">Fraudulent</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="flex gap-2">
                                            <button type="button" @click="showRefund = null"
                                                class="rounded-lg border border-plum-200 px-3 py-1 text-xs text-plum-500">Cancel</button>
                                            <button type="submit"
                                                onclick="this.form.querySelector('[name=amount_pence]').value = Math.round(parseFloat(this.form.querySelector('[name=amount_pence]').value) * 100)"
                                                class="rounded-lg bg-red-600 px-3 py-1 text-xs font-medium text-white hover:bg-red-700">
                                                Issue Refund
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-sm text-plum-400">No refunded orders found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($refunded->hasPages()) <div class="flex justify-center">{{ $refunded->links() }}</div> @endif
    </div>
</x-layouts.app>
