<x-patient-layout>
    <x-slot name="title">Messages — {{ $consultation->product->name }}</x-slot>
    <x-slot name="pageTitle">{{ $consultation->product->name }}</x-slot>

    <div class="max-w-2xl">

        {{-- Back link --}}
        <a href="{{ route('patient.messages.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-plum-500 hover:text-plum-800 mb-5 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            All messages
        </a>

        {{-- Conversation --}}
        <div class="card">
            {{-- Header --}}
            <div class="px-6 py-4 border-b border-plum-100 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-plum-800 flex items-center justify-center shrink-0">
                    <span class="text-lilac-400 text-xs font-bold">P&amp;C</span>
                </div>
                <div>
                    <p class="text-sm font-medium text-plum-800">Prescribe &amp; Co</p>
                    <p class="text-xs text-plum-400">Re: {{ $consultation->product->name }}</p>
                </div>
            </div>

            {{-- Messages --}}
            <div class="px-6 py-5 space-y-5 min-h-64 max-h-[32rem] overflow-y-auto"
                 id="messageContainer">
                @forelse ($messages as $message)
                    @php $isPatient = $message->isFromPatient(); @endphp
                    <div class="flex gap-3 {{ $isPatient ? 'flex-row-reverse' : '' }} animate-fade-in">
                        {{-- Avatar --}}
                        <div class="w-7 h-7 rounded-full shrink-0 flex items-center justify-center text-xs font-bold mt-1
                            {{ $isPatient ? 'bg-plum-800 text-lilac-400' : 'bg-lilac-200 text-plum-800' }}">
                            {{ $isPatient ? auth()->user()->first_name[0] : 'P&C' }}
                        </div>
                        {{-- Bubble --}}
                        <div class="max-w-xs lg:max-w-sm">
                            <div class="rounded-2xl px-4 py-3
                                {{ $isPatient
                                    ? 'bg-plum-800 text-lilac-50 rounded-tr-sm'
                                    : 'bg-plum-50 text-plum-800 rounded-tl-sm' }}">
                                <p class="text-sm leading-relaxed">{{ $message->body }}</p>
                            </div>
                            <p class="text-xs text-plum-400 mt-1 {{ $isPatient ? 'text-right' : '' }}">
                                {{ $message->created_at->format('j M, g:ia') }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <p class="text-sm text-plum-400">No messages yet. Send us a message below.</p>
                    </div>
                @endforelse
            </div>

            {{-- Compose --}}
            <div class="px-6 py-4 border-t border-plum-100 bg-plum-50/50">
                <form method="POST" action="{{ route('patient.messages.send', $consultation) }}"
                      class="flex gap-3 items-end">
                    @csrf
                    <div class="flex-1">
                        <textarea name="body"
                                  rows="2"
                                  placeholder="Type your message…"
                                  maxlength="2000"
                                  required
                                  class="input resize-none text-sm"></textarea>
                    </div>
                    <button type="submit" class="btn-primary shrink-0 self-end">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Send
                    </button>
                </form>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        // Auto-scroll to bottom of messages on load
        const container = document.getElementById('messageContainer');
        if (container) container.scrollTop = container.scrollHeight;
    </script>
    @endpush
</x-patient-layout>
