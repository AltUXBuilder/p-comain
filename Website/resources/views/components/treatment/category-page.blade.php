@props([
    'category',
    'heroHeading',
    'heroBody',
    'heroEmoji'    => '💊',
    'faqs'         => [],
    'conditions'   => [],
])

<x-app-layout>
<x-slot name="title">{{ $category->name }} Treatments</x-slot>
<x-slot name="description">{{ $heroBody }}</x-slot>

{{-- Hero --}}
<div class="bg-plum-800 py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl">
            <div class="text-5xl mb-4">{{ $heroEmoji }}</div>
            <nav class="text-xs text-lilac-500/50 mb-4 flex gap-2">
                <a href="{{ route('treatments.index') }}" class="hover:text-lilac-400">Treatments</a>
                <span>/</span>
                <span class="text-lilac-400">{{ $category->name }}</span>
            </nav>
            <h1 class="font-display text-display-lg font-bold text-white mb-4 text-balance">{{ $heroHeading }}</h1>
            <p class="text-lilac-500/80 text-lg leading-relaxed">{{ $heroBody }}</p>
        </div>
    </div>
</div>

{{-- Products --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">

    @if ($conditions)
        <p class="text-sm text-plum-500 mb-8">We treat: {{ implode(' · ', $conditions) }}</p>
    @endif

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5 mb-16">
        @foreach ($category->treatments as $treatment)
            @foreach ($treatment->activeProducts as $product)
                <x-treatment.product-card :product="$product" :category="$category" />
            @endforeach
        @endforeach
    </div>

    {{-- How it works --}}
    <div class="card bg-plum-800 p-8 mb-16">
        <h2 class="font-display text-2xl font-bold text-white mb-6 text-center">How it works</h2>
        <div class="grid sm:grid-cols-4 gap-6">
            @foreach([
                ['n'=>'1','t'=>'Free consultation','b'=>'Answer a short medical questionnaire at your own pace.'],
                ['n'=>'2','t'=>'Prescriber review','b'=>'A UK-registered prescriber reviews your answers.'],
                ['n'=>'3','t'=>'Prescription issued','b'=>'If appropriate, a private prescription is written.'],
                ['n'=>'4','t'=>'Delivered','b'=>'Dispatched in discreet packaging, usually next day.'],
            ] as $s)
                <div class="text-center">
                    <div class="w-10 h-10 rounded-full bg-lilac-500/20 text-lilac-400 font-display text-lg font-bold flex items-center justify-center mx-auto mb-3">{{ $s['n'] }}</div>
                    <p class="font-medium text-lilac-400 text-sm mb-1">{{ $s['t'] }}</p>
                    <p class="text-lilac-500/60 text-xs leading-relaxed">{{ $s['b'] }}</p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- FAQ --}}
    @if ($faqs)
        <div class="max-w-3xl">
            <h2 class="font-display text-display-sm font-bold text-plum-800 mb-6">Common questions</h2>
            <div class="space-y-3" x-data="{open: null}">
                @foreach ($faqs as $i => $faq)
                    <div class="card overflow-hidden">
                        <button @click="open === {{ $i }} ? open = null : open = {{ $i }}"
                                class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-plum-50 transition-colors">
                            <span class="font-medium text-plum-800 text-sm pr-4">{{ $faq['q'] }}</span>
                            <svg class="w-4 h-4 text-plum-500 shrink-0 transition-transform"
                                 :class="open === {{ $i }} ? 'rotate-180' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open === {{ $i }}" x-collapse>
                            <p class="px-6 pb-4 text-sm text-plum-500 leading-relaxed">{{ $faq['a'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Trust footer --}}
    <div class="mt-16 pt-8 border-t border-plum-100">
        <x-trust.trust-bar />
    </div>
</div>

</x-app-layout>
