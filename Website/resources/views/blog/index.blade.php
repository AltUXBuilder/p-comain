<x-app-layout>
<x-slot name="title">Clinical Advice & Blog</x-slot>

<div class="bg-plum-800 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="font-display text-display-md font-bold text-white mb-3">Clinical Advice &amp; Blog</h1>
        <p class="text-lilac-500/80 text-lg">Evidence-based health information, written by our pharmacy team.</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-7">
        @foreach ($posts as $post)
            <a href="{{ route('blog.show', $post['slug']) }}"
               class="card group hover:shadow-plum transition-all duration-200 hover:-translate-y-0.5 flex flex-col">

                {{-- Placeholder image area --}}
                <div class="h-36 bg-gradient-to-br from-plum-100 to-lilac-100 flex items-center justify-center">
                    <span class="text-4xl">
                        @php
                            $emojis = ['Weight Loss'=>'⚖️','Sexual Health'=>'💊','Skin Health'=>'✨','Hair Loss'=>'🌿','Digestive Health'=>'🌱','Advice'=>'📖'];
                        @endphp
                        {{ $emojis[$post['category']] ?? '📖' }}
                    </span>
                </div>

                <div class="p-5 flex-1 flex flex-col">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="badge-lilac text-xs">{{ $post['category'] }}</span>
                        <span class="text-xs text-plum-400">{{ $post['read_time'] }}</span>
                    </div>
                    <h2 class="font-display text-lg font-bold text-plum-800 mb-2 leading-tight group-hover:text-plum-900 flex-1">
                        {{ $post['title'] }}
                    </h2>
                    <p class="text-sm text-plum-500 leading-relaxed mb-4 line-clamp-2">{{ $post['excerpt'] }}</p>
                    <div class="flex items-center justify-between mt-auto pt-3 border-t border-plum-100">
                        <span class="text-xs text-plum-400">{{ $post['date'] }}</span>
                        <span class="text-xs font-medium text-plum-700 group-hover:text-plum-900 flex items-center gap-1 transition-colors">
                            Read more
                            <svg class="w-3 h-3 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    {{-- Disclaimer --}}
    <div class="mt-12 p-5 bg-plum-50 rounded-2xl border border-plum-200">
        <p class="text-xs text-plum-500 text-center leading-relaxed">
            <strong class="text-plum-700">Clinical disclaimer:</strong>
            The information on this blog is intended for general informational purposes only and does not constitute medical advice. Always consult a healthcare professional before starting, stopping or changing any medication.
        </p>
    </div>
</div>

</x-app-layout>
