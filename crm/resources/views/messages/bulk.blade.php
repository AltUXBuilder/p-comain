<x-layouts.app>
    <x-slot name="pageTitle">Bulk Messaging</x-slot>
    <div class="max-w-xl space-y-5 animate-fade-in">
        <a href="{{ route('messages.index') }}" class="text-sm font-medium text-lilac-600 hover:text-lilac-800">← Messages</a>
        <div class="rounded-2xl border border-plum-100 bg-white p-6 shadow-plum-sm">
            <h2 class="mb-1 text-lg font-semibold text-plum-800">Send Bulk Message</h2>
            <p class="mb-5 text-xs text-plum-400">Filters are ANDed — leave blank to message all active patients.</p>
            @if($errors->any())
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
                </div>
            @endif
            <form method="POST" action="{{ route('messages.bulk.send') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-medium text-plum-700">Subject (email subject line)</label>
                    <input type="text" name="subject" required value="{{ old('subject') }}"
                        class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-plum-700">Message</label>
                    <textarea name="body" rows="6" required class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4 rounded-xl bg-plum-50/60 p-4">
                    <p class="col-span-2 text-xs font-semibold text-plum-600 mb-1">Filters (optional)</p>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-plum-500">Treatment category</label>
                        <select name="filter_category_id" class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                            <option value="">All categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-plum-500">Subscription status</label>
                        <select name="filter_status" class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                            <option value="">Any</option>
                            <option value="active">Active</option>
                            <option value="past_due">Past due</option>
                            <option value="canceled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('messages.index') }}" class="rounded-xl border border-plum-200 px-4 py-2.5 text-sm text-plum-500 hover:bg-plum-50">Cancel</a>
                    <button type="submit" class="rounded-xl bg-plum-800 px-5 py-2.5 text-sm font-semibold text-lilac-200 hover:bg-plum-900">Send Bulk Message</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
