<x-app-layout>
<x-slot name="title">All Treatments</x-slot>
<x-slot name="description">Browse all prescription treatments available from Prescribe & Co. GPhC registered pharmacy with UK-based prescribers.</x-slot>

{{-- Header --}}
<div class="bg-plum-800 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="font-display text-display-md font-bold text-white mb-3">Our treatments</h1>
        <p class="text-lilac-500/80 text-lg max-w-xl">Evidence-based prescription treatments, reviewed by UK-registered prescribers and delivered to your door.</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="space-y-16">
        @foreach ($categories as $category)
            <div>
                <div class="flex items-center justify-between mb-6">
                    <h2 class="font-display text-display-sm font-bold text-plum-800">{{ $category->name }}</h2>
                    <a href="{{ route('treatments.category', $category) }}" class="text-sm text-plum-500 hover:text-plum-800 transition-colors">
                        View all {{ $category->name }} →
                    </a>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                    @foreach ($category->treatments as $treatment)
                        @foreach ($treatment->activeProducts as $product)
                            <x-treatment.product-card :product="$product" :category="$category" />
                        @endforeach
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>

</x-app-layout>
