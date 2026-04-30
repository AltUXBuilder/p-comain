<x-app-layout>
<x-slot name="title">{{ $product->name }}</x-slot>
<x-slot name="description">{{ $product->name }} — {{ $product->strength }} {{ $product->form }}. Available from Prescribe & Co, GPhC registered online pharmacy.</x-slot>

{{-- Breadcrumb --}}
<div class="bg-plum-50 border-b border-plum-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
        <nav class="flex items-center gap-2 text-xs text-plum-400">
            <a href="{{ route('home') }}" class="hover:text-plum-700">Home</a>
            <span>/</span>
            <a href="{{ route('treatments.category', $category) }}" class="hover:text-plum-700">{{ $category->name }}</a>
            <span>/</span>
            <span class="text-plum-700">{{ $product->name }}</span>
        </nav>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid lg:grid-cols-5 gap-12">

        {{-- ── Main content ─────────────────────────────────────────────── --}}
        <div class="lg:col-span-3 space-y-8">

            {{-- Header --}}
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="badge-plum text-xs">{{ $product->product_type }}</span>
                    @if ($product->requires_cold_chain)
                        <span class="badge text-xs bg-blue-50 text-blue-700">❄ Cold chain</span>
                    @endif
                    @if ($product->requires_age_verification)
                        <span class="badge text-xs bg-amber-50 text-amber-700">18+ only</span>
                    @endif
                </div>
                <h1 class="font-display text-display-md font-bold text-plum-800 mb-2">{{ $product->name }}</h1>
                @if ($product->brand_name && $product->brand_name !== $product->name)
                    <p class="text-plum-500 text-sm mb-1">Brand: {{ $product->brand_name }}</p>
                @endif
                <p class="text-plum-500">{{ $product->strength }} · {{ ucfirst($product->form) }}</p>
            </div>

            {{-- Description --}}
            @if ($product->description)
                <div class="prose prose-sm max-w-none text-plum-600">
                    <p class="text-base leading-relaxed">{{ $product->description }}</p>
                </div>
            @endif

            {{-- Dosage --}}
            @if ($product->dosage_instructions)
                <div class="card p-5">
                    <h3 class="font-display text-base font-bold text-plum-800 mb-2">Dosage instructions</h3>
                    <p class="text-sm text-plum-600 leading-relaxed">{{ $product->dosage_instructions }}</p>
                </div>
            @endif

            {{-- How it works section --}}
            <div>
                <h2 class="font-display text-xl font-bold text-plum-800 mb-4">How the consultation works</h2>
                <div class="space-y-3">
                    @foreach([
                        ['n'=>'1','t'=>'Start your free consultation','b'=>'Answer a short medical questionnaire. No appointment needed, no waiting room.'],
                        ['n'=>'2','t'=>'Reviewed by a UK prescriber','b'=>'A GPhC-registered prescriber reviews your answers and medical history.'],
                        ['n'=>'3','t'=>'Prescription issued','b'=>'If appropriate, a private prescription is issued and sent to our dispensing team.'],
                        ['n'=>'4','t'=>'Delivered to your door','b'=>'Your treatment arrives in plain, discreet packaging. Usually within 1–3 working days.'],
                    ] as $s)
                        <div class="flex gap-4">
                            <div class="w-7 h-7 rounded-full bg-plum-800 text-lilac-400 text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">{{ $s['n'] }}</div>
                            <div>
                                <p class="font-medium text-plum-800 text-sm">{{ $s['t'] }}</p>
                                <p class="text-plum-500 text-sm mt-0.5">{{ $s['b'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- PIL download --}}
            @if ($product->pil_path)
                <div class="flex items-center gap-3 p-4 bg-plum-50 rounded-xl border border-plum-200">
                    <svg class="w-5 h-5 text-plum-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <div>
                        <p class="text-sm font-medium text-plum-800">Patient Information Leaflet</p>
                        <a href="{{ Storage::url($product->pil_path) }}" target="_blank" class="text-xs text-plum-500 hover:text-plum-700 underline">Download PDF</a>
                    </div>
                </div>
            @endif

        </div>

        {{-- ── Sidebar: buy/consult panel ───────────────────────────────── --}}
        <div class="lg:col-span-2">
            <div class="card p-6 sticky top-24 space-y-5">

                {{-- Price --}}
                @if ($product->price_one_off)
                    <div>
                        <p class="text-xs text-plum-400 mb-1">One-off price</p>
                        <p class="font-display text-3xl font-bold text-plum-800">£{{ number_format($product->price_one_off, 2) }}</p>
                    </div>
                @endif

                @if ($product->subscription_tiers && count($product->subscription_tiers) > 0)
                    <div>
                        <p class="text-xs text-plum-400 mb-2">Subscription plans</p>
                        <div class="space-y-2">
                            @foreach ($product->subscription_tiers as $tier)
                                <div class="flex justify-between items-center py-2 border-b border-plum-100 last:border-0">
                                    <span class="text-sm text-plum-700">{{ $tier['label'] }}</span>
                                    <span class="font-medium text-plum-800">£{{ number_format($tier['price'], 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- CTA --}}
                @if ($product->has_questionnaire)
                    <a href="{{ route('consultation.start', $product) }}" class="btn-primary w-full justify-center btn-lg">
                        Start free consultation →
                    </a>
                    <p class="text-xs text-plum-400 text-center">Free consultation · No obligation · UK prescribers</p>
                @else
                    @auth
                        <a href="{{ route('checkout.index', ['product_id' => $product->id]) }}" class="btn-primary w-full justify-center btn-lg">
                            Buy now — £{{ number_format($product->price_one_off, 2) }}
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="btn-primary w-full justify-center btn-lg">
                            Buy now
                        </a>
                    @endauth
                @endif

                {{-- Trust --}}
                <div class="pt-3 border-t border-plum-100 space-y-2 text-xs text-plum-400">
                    @foreach(['GPhC registered pharmacy','Private prescription issued','Discreet packaging','Free UK delivery','Cancel anytime (subscriptions)'] as $t)
                        <div class="flex items-center gap-2">
                            <svg class="w-3 h-3 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            {{ $t }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Related products --}}
    @if ($related->isNotEmpty())
        <div class="mt-16 pt-12 border-t border-plum-100">
            <h2 class="font-display text-display-sm font-bold text-plum-800 mb-6">Other {{ $category->name }} treatments</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach ($related as $rel)
                    <x-treatment.product-card :product="$rel" :category="$category" />
                @endforeach
            </div>
        </div>
    @endif
</div>

</x-app-layout>
