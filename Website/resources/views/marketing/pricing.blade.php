<x-app-layout>
<x-slot name="title">Pricing</x-slot>

<div class="bg-plum-800 py-16 text-center">
    <div class="max-w-2xl mx-auto px-4">
        <h1 class="font-display text-display-lg font-bold text-white mb-4">Simple, transparent pricing</h1>
        <p class="text-lilac-500/80 text-lg">No hidden fees. Consultations are always free. You only pay when your prescription is approved and you choose to proceed.</p>
    </div>
</div>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16">

    {{-- Free consultation callout --}}
    <div class="card bg-lilac-50 border-2 border-lilac-200 p-6 mb-12 text-center">
        <p class="font-display text-2xl font-bold text-plum-800 mb-1">Consultations are always free</p>
        <p class="text-plum-500">You complete your medical questionnaire for free. You only pay once your consultation is approved and you choose to complete your order.</p>
    </div>

    {{-- Products by category --}}
    @foreach ($categories as $category)
        @if ($category->treatments->flatMap->activeProducts->isNotEmpty())
            <div class="mb-14">
                <h2 class="font-display text-display-sm font-bold text-plum-800 mb-6">{{ $category->name }}</h2>
                <div class="space-y-3">
                    @foreach ($category->treatments as $treatment)
                        @foreach ($treatment->activeProducts as $product)
                            <div class="card p-5">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div>
                                        <div class="flex items-center gap-2 mb-1">
                                            <p class="font-medium text-plum-800">{{ $product->name }}</p>
                                            <span class="badge-lilac text-xs">{{ $product->product_type }}</span>
                                        </div>
                                        <p class="text-xs text-plum-400">{{ $product->strength }} {{ $product->form }}</p>
                                    </div>
                                    <div class="flex flex-wrap gap-3 shrink-0">
                                        @if ($product->price_one_off)
                                            <div class="text-center bg-plum-50 rounded-xl px-4 py-2">
                                                <p class="text-xs text-plum-400 mb-0.5">One-off</p>
                                                <p class="font-display text-xl font-bold text-plum-800">£{{ number_format($product->price_one_off, 2) }}</p>
                                            </div>
                                        @endif
                                        @foreach ($product->subscription_tiers ?? [] as $tier)
                                            <div class="text-center bg-lilac-50 border border-lilac-200 rounded-xl px-4 py-2">
                                                <p class="text-xs text-plum-400 mb-0.5">{{ $tier['label'] }}</p>
                                                <p class="font-display text-xl font-bold text-plum-800">£{{ number_format($tier['price'], 2) }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="mt-4 pt-3 border-t border-plum-100">
                                    <a href="{{ route('products.show', [$category, $product]) }}"
                                       class="text-sm text-plum-600 hover:text-plum-800 font-medium transition-colors">
                                        {{ $product->has_questionnaire ? 'Start free consultation →' : 'Learn more →' }}
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach

    {{-- What's included --}}
    <div class="card p-8 mt-4">
        <h2 class="font-display text-xl font-bold text-plum-800 mb-6 text-center">What's included with every order</h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach([
                ['emoji'=>'👨‍⚕️','title'=>'Clinical review','body'=>'Every consultation reviewed by a UK-registered prescriber.'],
                ['emoji'=>'📦','title'=>'Free UK delivery','body'=>'Discreet, plain-packaged delivery at no extra cost.'],
                ['emoji'=>'💬','title'=>'Pharmacy support','body'=>'Message our pharmacy team any time via your account.'],
                ['emoji'=>'📋','title'=>'Prescription copy','body'=>'PDF copy of your private prescription, downloadable from your account.'],
            ] as $item)
                <div class="text-center">
                    <div class="text-3xl mb-3">{{ $item['emoji'] }}</div>
                    <p class="font-medium text-plum-800 text-sm mb-1">{{ $item['title'] }}</p>
                    <p class="text-xs text-plum-500 leading-relaxed">{{ $item['body'] }}</p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- VAT note --}}
    <p class="text-xs text-plum-400 text-center mt-6">
        Prescription-only medicines (POM) are exempt from VAT under HMRC Notice 701/57. Prices shown are inclusive of any applicable VAT.
    </p>
</div>

</x-app-layout>
