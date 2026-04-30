<x-patient-layout>
    <x-slot name="title">Messages</x-slot>
    <x-slot name="pageTitle">Messages</x-slot>

    @if ($threads->isEmpty())
        <div class="card p-12 text-center">
            <div class="w-16 h-16 rounded-2xl bg-lilac-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-plum-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </div>
            <h2 class="font-display text-xl font-bold text-plum-800 mb-2">No messages yet</h2>
            <p class="text-plum-500 text-sm">Messages from our pharmacy team will appear here. You can also send us a message via a consultation thread.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($threads as $thread)
                @php $latest = $thread->messages->first(); @endphp
                <a href="{{ route('patient.messages.thread', $thread) }}"
                   class="card px-6 py-5 flex items-center gap-4 hover:shadow-plum transition-shadow group">

                    {{-- Avatar --}}
                    <div class="w-10 h-10 rounded-full bg-plum-800 flex items-center justify-center shrink-0">
                        <span class="text-lilac-400 text-xs font-bold">P&amp;C</span>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2 mb-0.5">
                            <p class="text-sm font-medium text-plum-800 truncate">{{ $thread->product->name }}</p>
                            @if ($latest)
                                <span class="text-xs text-plum-400 shrink-0">{{ $latest->created_at->diffForHumans() }}</span>
                            @endif
                        </div>
                        @if ($latest)
                            <p class="text-xs text-plum-500 truncate">{{ Str::limit($latest->body, 80) }}</p>
                        @endif
                    </div>

                    {{-- Unread dot --}}
                    @if ($thread->messages->contains(fn($m) => $m->sender_type === 'staff' && !$m->read_at))
                        <div class="w-2 h-2 rounded-full bg-plum-800 shrink-0"></div>
                    @endif

                    <svg class="w-4 h-4 text-plum-300 group-hover:text-plum-600 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            @endforeach
        </div>
    @endif

</x-patient-layout>
