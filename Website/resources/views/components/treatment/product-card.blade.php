@props(['product', 'category'])

<a href="{{ route('products.show', [$category, $product]) }}"
   class="card group hover:shadow-plum transition-all duration-200 hover:-translate-y-0.5 flex flex-col">

    {{-- Product type badge --}}
    <div class="px-5 pt-5 pb-4 flex-1">
        <div class="flex items-start justify-between gap-2 mb-3">
            <span class="badge-lilac text-xs">{{ $product->product_type }}</span>
            @if ($product->requires_cold_chain)
                <span class="badge text-xs bg-blue-50 text-blue-700">❄ Cold chain</span>
            @endif
        </div>

        <h3 class="font-display text-lg font-bold text-plum-800 group-hover:text-plum-900 mb-1 leading-tight">
            {{ $product->name }}
        </h3>
        @if ($product->generic_name && $product->generic_name !== $product->name)
            <p class="text-xs text-plum-400 mb-2">{{ $product->generic_name }}</p>
        @endif
        <p class="text-xs text-plum-500 mb-3">{{ $product->strength }} {{ $product->form }}</p>

        @if ($product->description)
            <p class="text-sm text-plum-500 leading-relaxed line-clamp-2">{{ $product->description }}</p>
        @endif
    </div>

    {{-- Pricing footer --}}
    <div class="px-5 py-4 border-t border-plum-100 bg-plum-50/50">
        @if ($product->price_one_off)
            <p class="text-sm text-plum-500 mb-0.5">From</p>
            <p class="font-display text-xl font-bold text-plum-800">£{{ number_format($product->price_one_off, 2) }}</p>
        @elseif ($product->subscription_tiers && count($product->subscription_tiers) > 0)
            @php $cheapest = collect($product->subscription_tiers)->sortBy('price')->first(); @endphp
            <p class="text-sm text-plum-500 mb-0.5">From</p>
            <p class="font-display text-xl font-bold text-plum-800">£{{ number_format($cheapest['price'], 2) }}<span class="text-sm font-sans font-normal text-plum-400">/mo</span></p>
        @endif

        <div class="flex items-center gap-1 text-xs text-plum-600 font-medium mt-2 group-hover:gap-2 transition-all">
            {{ $product->has_questionnaire ? 'Start free consultation' : 'Buy now' }}
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </div>
    </div>
</a>
