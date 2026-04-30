<x-app-layout>
<x-slot name="title">Clinical Advice</x-slot>

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    {{-- Breadcrumb --}}
    <nav class="text-xs text-plum-400 mb-8 flex gap-2">
        <a href="{{ route('home') }}" class="hover:text-plum-700">Home</a>
        <span>/</span>
        <a href="{{ route('blog.index') }}" class="hover:text-plum-700">Advice</a>
        <span>/</span>
        <span class="text-plum-700 capitalize">{{ str_replace('-', ' ', $slug) }}</span>
    </nav>

    {{-- Article placeholder --}}
    <div class="text-center py-20">
        <div class="text-5xl mb-6">📖</div>
        <h1 class="font-display text-display-sm font-bold text-plum-800 mb-4 capitalize">
            {{ str_replace('-', ' ', $slug) }}
        </h1>
        <p class="text-plum-500 mb-8">This article is coming soon. Our clinical team is working on it.</p>
        <a href="{{ route('blog.index') }}" class="btn-secondary">← Back to all articles</a>
    </div>

    {{-- CTA --}}
    <div class="card bg-plum-800 p-8 text-center mt-10">
        <p class="font-display text-xl font-bold text-white mb-2">Ready to start a consultation?</p>
        <p class="text-lilac-500/70 mb-5 text-sm">Browse our clinically reviewed treatments.</p>
        <a href="{{ route('treatments.index') }}" class="btn-lilac">View treatments</a>
    </div>

    {{-- Disclaimer --}}
    <p class="text-xs text-plum-400 text-center mt-8 leading-relaxed">
        The information on this page is intended for general informational purposes only and does not constitute medical advice. Always consult a healthcare professional before starting, stopping or changing any medication.
    </p>
</div>

</x-app-layout>
