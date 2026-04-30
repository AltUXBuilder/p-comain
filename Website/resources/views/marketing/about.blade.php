<x-app-layout>
<x-slot name="title">About Us</x-slot>

<div class="bg-plum-800 py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 max-w-3xl">
        <h1 class="font-display text-display-lg font-bold text-white mb-4">About Prescribe &amp; Co</h1>
        <p class="text-lilac-500/80 text-lg leading-relaxed">We believe that access to high-quality prescription healthcare shouldn't be complicated, time-consuming or embarrassing. Prescribe &amp; Co was built to change that.</p>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 space-y-16">

    {{-- Mission --}}
    <div class="grid md:grid-cols-2 gap-10 items-center">
        <div>
            <h2 class="font-display text-display-sm font-bold text-plum-800 mb-4">Our mission</h2>
            <p class="text-plum-600 leading-relaxed mb-4">We're a GPhC-registered online pharmacy staffed entirely by UK-trained pharmacists, prescribers and dispensers. Every consultation is reviewed by a real clinician — not an algorithm — before any prescription is issued.</p>
            <p class="text-plum-600 leading-relaxed">We treat patients across five clinical areas: weight management, sexual health, skin health, hair loss and digestive health. Our focus is on long-term, evidence-based care rather than quick fixes.</p>
        </div>
        <div class="space-y-4">
            @foreach(['GPhC registered and regulated','MHRA compliant — all products are licensed UK medicines','ICO registered — your data is protected','All prescribers hold valid UK registration numbers'] as $p)
                <div class="flex gap-3 items-start">
                    <div class="w-5 h-5 rounded-full bg-plum-800 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-3 h-3 text-lilac-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <p class="text-sm text-plum-700">{{ $p }}</p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Regulatory --}}
    <div class="card p-8">
        <h2 class="font-display text-xl font-bold text-plum-800 mb-6">Regulatory information</h2>
        <div class="grid sm:grid-cols-3 gap-6 text-sm">
            <div>
                <p class="font-semibold text-plum-800 mb-1">GPhC Registration</p>
                <p class="text-plum-500">{{ config('pharmacy.gphc_number') ?: '####' }}</p>
                <p class="text-plum-400 text-xs mt-1">General Pharmaceutical Council</p>
            </div>
            <div>
                <p class="font-semibold text-plum-800 mb-1">ICO Registration</p>
                <p class="text-plum-500">{{ config('pharmacy.regulatory.ico_number') ?: '####' }}</p>
                <p class="text-plum-400 text-xs mt-1">Information Commissioner's Office</p>
            </div>
            <div>
                <p class="font-semibold text-plum-800 mb-1">Regulated by</p>
                <p class="text-plum-500">GPhC, MHRA and ICO</p>
                <p class="text-plum-400 text-xs mt-1">UK regulatory authorities</p>
            </div>
        </div>
    </div>

    {{-- Values --}}
    <div>
        <h2 class="font-display text-display-sm font-bold text-plum-800 mb-8 text-center">What we stand for</h2>
        <div class="grid sm:grid-cols-3 gap-6">
            @foreach([
                ['emoji'=>'🔬','title'=>'Evidence first','body'=>'We only offer treatments with robust clinical evidence. Every product on our platform is a licensed UK medicine.'],
                ['emoji'=>'🤝','title'=>'Patient safety','body'=>'Every consultation is reviewed by a qualified prescriber. We\'d rather decline a consultation than prescribe inappropriately.'],
                ['emoji'=>'🔒','title'=>'Privacy','body'=>'Your health data belongs to you. We\'re ICO registered and GDPR compliant. We never sell or share your data.'],
            ] as $v)
                <div class="card p-6 text-center">
                    <div class="text-3xl mb-4">{{ $v['emoji'] }}</div>
                    <h3 class="font-display text-lg font-bold text-plum-800 mb-2">{{ $v['title'] }}</h3>
                    <p class="text-sm text-plum-500 leading-relaxed">{{ $v['body'] }}</p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- CTA --}}
    <div class="text-center">
        <a href="{{ route('treatments.index') }}" class="btn-primary btn-lg">Browse treatments</a>
    </div>
</div>

</x-app-layout>
