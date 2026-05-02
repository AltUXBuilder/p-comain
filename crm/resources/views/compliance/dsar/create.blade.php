<x-layouts.app>
    <x-slot name="pageTitle">Log DSAR Request</x-slot>
    <div class="max-w-lg animate-fade-in">
        <a href="{{ route('compliance.dsar.index') }}" class="text-sm font-medium text-lilac-600 hover:text-lilac-800">← DSAR Requests</a>
        <div class="mt-5 rounded-2xl border border-plum-100 bg-white p-6 shadow-plum-sm">
            <h2 class="mb-1 text-lg font-semibold text-plum-800">New DSAR Request</h2>
            <p class="mb-5 text-xs text-plum-400">GDPR requires a response within 30 calendar days. A due date will be set automatically.</p>
            @if($errors->any())
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
                </div>
            @endif
            <form method="POST" action="{{ route('compliance.dsar.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-medium text-plum-700">Request Type <span class="text-red-500">*</span></label>
                    <select name="type" required class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                        @foreach($types as $k => $v)
                            <option value="{{ $k }}" {{ old('type') === $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-plum-700">Requestor Email <span class="text-red-500">*</span></label>
                    <input type="email" name="requestor_email" value="{{ old('requestor_email') }}" required
                        class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                    <p class="mt-1 text-xs text-plum-400">Email provided in the request (may differ from account email).</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-plum-700">Linked Patient ID <span class="text-plum-400 font-normal">(optional)</span></label>
                    <input type="number" name="user_id" value="{{ old('user_id') }}"
                        class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500" placeholder="Patient ID from patient record">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-plum-700">Notes</label>
                    <textarea name="notes" rows="4"
                        class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500"
                        placeholder="Any additional context or actions taken…">{{ old('notes') }}</textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('compliance.dsar.index') }}" class="rounded-xl border border-plum-200 px-4 py-2.5 text-sm text-plum-500 hover:bg-plum-50">Cancel</a>
                    <button type="submit" class="rounded-xl bg-plum-800 px-5 py-2.5 text-sm font-semibold text-lilac-200 hover:bg-plum-900">Log Request</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
