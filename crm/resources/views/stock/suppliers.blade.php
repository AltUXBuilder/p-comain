<x-layouts.app>
    <x-slot name="pageTitle">Suppliers</x-slot>

    <div class="space-y-5 animate-fade-in" x-data="{ showAdd: false }">

        <div class="flex items-center justify-between">
            <a href="{{ route('stock.index') }}" class="text-sm font-medium text-lilac-600 hover:text-lilac-800">← Inventory</a>
            <button @click="showAdd = true" class="rounded-xl bg-plum-800 px-4 py-2 text-sm font-medium text-lilac-200 hover:bg-plum-900">+ Add Supplier</button>
        </div>

        <div class="overflow-hidden rounded-2xl border border-plum-100 bg-white shadow-plum-sm">
            <table class="min-w-full divide-y divide-plum-100">
                <thead class="bg-plum-50/60">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Supplier</th>
                        <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 sm:table-cell">Contact</th>
                        <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 md:table-cell">Batches</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-plum-50">
                    @forelse($suppliers as $s)
                        <tr class="hover:bg-plum-50/10">
                            <td class="px-4 py-3">
                                <p class="text-sm font-medium text-plum-800">{{ $s->name }}</p>
                                @if($s->email) <p class="text-xs text-plum-400">{{ $s->email }}</p> @endif
                            </td>
                            <td class="hidden px-4 py-3 text-sm text-plum-600 sm:table-cell">{{ $s->contact_name ?? '—' }}</td>
                            <td class="hidden px-4 py-3 text-sm text-plum-600 md:table-cell">{{ $s->batches_count }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $s->active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $s->active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-10 text-center text-sm text-plum-400">No suppliers yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($suppliers->hasPages())
            <div class="flex justify-center">{{ $suppliers->links() }}</div>
        @endif

        {{-- Add supplier modal --}}
        <div x-show="showAdd" class="fixed inset-0 z-50 flex items-center justify-center bg-plum-950/50 p-4" x-cloak>
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-plum-lg">
                <h3 class="mb-4 text-base font-semibold text-plum-800">Add Supplier</h3>
                <form method="POST" action="{{ route('stock.suppliers.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="mb-1 block text-xs font-medium text-plum-600">Company name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-plum-600">Contact name</label>
                            <input type="text" name="contact_name" class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-plum-600">Phone</label>
                            <input type="text" name="phone" class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-plum-600">Email</label>
                        <input type="email" name="email" class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-plum-600">Address</label>
                        <textarea name="address" rows="2" class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="showAdd = false" class="rounded-xl border border-plum-200 px-4 py-2 text-sm text-plum-500">Cancel</button>
                        <button type="submit" class="rounded-xl bg-plum-800 px-4 py-2 text-sm font-medium text-lilac-200 hover:bg-plum-900">Save Supplier</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.app>
