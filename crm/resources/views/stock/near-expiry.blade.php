<x-layouts.app>
    <x-slot name="pageTitle">Near-Expiry Report</x-slot>

    <div class="space-y-5 animate-fade-in">

        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('stock.index') }}" class="text-sm font-medium text-lilac-600 hover:text-lilac-800">← Inventory</a>
                <h2 class="mt-2 text-xl font-semibold text-plum-800">Near-Expiry Report</h2>
            </div>
            {{-- Threshold selector --}}
            <div class="flex gap-2">
                @foreach([30, 60, 90] as $d)
                    <a href="{{ route('stock.near-expiry', ['days' => $d]) }}"
                        @class([
                            'rounded-xl px-4 py-2 text-sm font-medium transition',
                            'bg-plum-800 text-lilac-200' => $days == $d,
                            'border border-plum-200 text-plum-600 hover:bg-plum-50' => $days != $d,
                        ])>
                        {{ $d }} days
                    </a>
                @endforeach
            </div>
        </div>

        @if($batches->isEmpty())
            <div class="rounded-2xl border border-green-200 bg-green-50 p-10 text-center">
                <p class="text-sm font-medium text-green-700">✓ No batches expiring within {{ $days }} days.</p>
            </div>
        @else
            <div class="overflow-hidden rounded-2xl border border-plum-100 bg-white shadow-plum-sm">
                <table class="min-w-full divide-y divide-plum-100">
                    <thead class="bg-plum-50/60">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Product</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Batch</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Expiry</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Remaining</th>
                            <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 sm:table-cell">Supplier</th>
                            <th class="px-4 py-3 text-right"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-plum-50">
                        @foreach($batches as $batch)
                            @php $daysLeft = now()->diffInDays($batch->expiry_date, false); @endphp
                            <tr class="hover:bg-plum-50/10">
                                <td class="px-4 py-3">
                                    <p class="text-sm font-medium text-plum-800">{{ $batch->product?->name }}</p>
                                    @if($batch->product?->cold_chain)
                                        <span class="text-[10px] text-blue-500 font-semibold">❄ Cold chain</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-mono text-xs text-plum-600">{{ $batch->batch_number }}</td>
                                <td class="px-4 py-3">
                                    <p class="text-sm font-semibold {{ $daysLeft <= 30 ? 'text-red-600' : 'text-amber-600' }}">
                                        {{ $batch->expiry_date->format('d M Y') }}
                                    </p>
                                    <p class="text-xs text-plum-400">{{ $batch->expiry_date->diffForHumans() }}</p>
                                </td>
                                <td class="px-4 py-3 text-sm font-medium text-plum-800">{{ $batch->quantity_remaining }}</td>
                                <td class="hidden px-4 py-3 text-sm text-plum-500 sm:table-cell">{{ $batch->supplier?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('stock.show', $batch->product_id) }}" class="text-xs font-medium text-lilac-600 hover:text-lilac-800">Manage</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    </div>
</x-layouts.app>
