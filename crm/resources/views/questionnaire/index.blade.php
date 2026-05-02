<x-layouts.app>
    <x-slot name="pageTitle">Questionnaire Builder</x-slot>
    <div class="space-y-5 animate-fade-in">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-plum-800">Questionnaires</h2>
                <p class="mt-0.5 text-xs text-plum-400">Reusable questionnaires assignable to multiple products. No code required to modify question logic.</p>
            </div>
            <a href="{{ route('questionnaire.create') }}"
               class="flex items-center gap-2 rounded-xl bg-plum-800 px-4 py-2 text-sm font-medium text-lilac-200 hover:bg-plum-900">
                + New Questionnaire
            </a>
        </div>

        @if($questionnaires->isEmpty())
            <div class="rounded-2xl border border-plum-100 bg-white p-12 text-center text-sm text-plum-400">
                No questionnaires yet. Create one to assign to products.
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($questionnaires as $q)
                    <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm flex flex-col">
                        <div class="mb-3 flex items-start justify-between gap-2">
                            <div>
                                <p class="font-semibold text-plum-800">{{ $q->name }}</p>
                                @if($q->description)
                                    <p class="mt-0.5 text-xs text-plum-400">{{ Str::limit($q->description, 60) }}</p>
                                @endif
                            </div>
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $q->active ? 'bg-green-100 text-green-700' : 'bg-plum-100 text-plum-500' }}">
                                {{ $q->active ? 'Active' : 'Draft' }}
                            </span>
                        </div>

                        <div class="mt-auto space-y-2 border-t border-plum-50 pt-3">
                            <div class="flex flex-wrap gap-1">
                                <span class="rounded-full bg-plum-100 px-2 py-0.5 text-[10px] font-medium text-plum-600">{{ $q->questions_count }} questions</span>
                                @foreach($q->products->take(2) as $product)
                                    <span class="rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-medium text-blue-700">{{ $product->name }}</span>
                                @endforeach
                                @if($q->products->count() > 2)
                                    <span class="rounded-full bg-plum-100 px-2 py-0.5 text-[10px] text-plum-400">+{{ $q->products->count() - 2 }} more</span>
                                @endif
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('questionnaire.show', $q) }}"
                                   class="flex-1 rounded-xl border border-plum-200 py-1.5 text-center text-xs font-medium text-plum-600 hover:bg-plum-50">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('questionnaire.duplicate', $q) }}">
                                    @csrf
                                    <button type="submit" class="rounded-xl border border-plum-200 px-3 py-1.5 text-xs text-plum-500 hover:bg-plum-50">Duplicate</button>
                                </form>
                                <form method="POST" action="{{ route('questionnaire.destroy', $q) }}"
                                      onsubmit="return confirm('Delete this questionnaire?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="rounded-xl border border-red-200 px-3 py-1.5 text-xs text-red-500 hover:bg-red-50">✕</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.app>
