<x-layouts.app>
    <x-slot name="pageTitle">New Questionnaire</x-slot>
    <div class="max-w-lg animate-fade-in">
        <a href="{{ route('questionnaire.index') }}" class="text-sm font-medium text-lilac-600 hover:text-lilac-800">← Questionnaires</a>
        <div class="mt-5 rounded-2xl border border-plum-100 bg-white p-6 shadow-plum-sm">
            <h2 class="mb-5 text-lg font-semibold text-plum-800">New Questionnaire</h2>
            @if($errors->any())
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
                </div>
            @endif
            <form method="POST" action="{{ route('questionnaire.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-medium text-plum-700">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500"
                        placeholder="e.g. GLP-1 Weight Loss Questionnaire">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-plum-700">Description</label>
                    <textarea name="description" rows="2"
                        class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500"
                        placeholder="Internal notes about this questionnaire…">{{ old('description') }}</textarea>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-plum-700">Assign to products (optional — can be done later)</label>
                    <select name="product_ids[]" multiple
                        class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500 h-28">
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" {{ in_array($p->id, old('product_ids', [])) ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-plum-400">Hold Ctrl/Cmd to select multiple</p>
                </div>
                <label class="flex items-center gap-2 text-sm text-plum-700">
                    <input type="checkbox" name="active" value="1" class="size-4 rounded border-plum-300 text-lilac-500">
                    Make active immediately
                </label>
                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('questionnaire.index') }}" class="rounded-xl border border-plum-200 px-4 py-2.5 text-sm text-plum-500">Cancel</a>
                    <button type="submit" class="rounded-xl bg-plum-800 px-5 py-2.5 text-sm font-semibold text-lilac-200 hover:bg-plum-900">
                        Create &amp; Add Questions →
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
