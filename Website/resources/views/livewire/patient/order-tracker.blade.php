<div>
    {{-- Status header --}}
    <div class="flex items-center justify-between mb-4">
        <div>
            <p class="text-xs text-plum-400">{{ $order->order_number }}</p>
            @if ($order->dispatched_at)
                <p class="text-xs text-plum-500 mt-0.5">Dispatched {{ $order->dispatched_at->format('j M Y') }}</p>
            @endif
        </div>
        @if ($order->tracking_url)
            <a href="{{ $order->tracking_url }}" target="_blank" class="btn-secondary btn-sm text-xs">
                Track →
            </a>
        @endif
    </div>

    {{-- Step indicators --}}
    <div class="relative">
        {{-- Connector line --}}
        <div class="absolute top-3 left-3 right-3 h-0.5 bg-plum-100"></div>

        <div class="relative flex justify-between">
            @foreach ($this->steps as $step)
                <div class="flex flex-col items-center gap-1.5 flex-1">
                    <div class="w-6 h-6 rounded-full flex items-center justify-center z-10
                        {{ $step['done'] ? 'bg-plum-800' : 'bg-white border-2 border-plum-200' }}">
                        @if ($step['done'])
                            <svg class="w-3 h-3 text-lilac-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        @elseif ($step['current'])
                            <div class="w-2 h-2 rounded-full bg-plum-400"></div>
                        @endif
                    </div>
                    <p class="text-center text-xs {{ $step['done'] ? 'text-plum-700 font-medium' : 'text-plum-400' }} leading-tight max-w-[3.5rem]">
                        {{ $step['label'] }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Cold chain notice --}}
    @if ($order->requires_cold_chain && $order->status === 'dispatched')
        <div class="mt-4 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2.5 text-xs text-amber-800 flex items-start gap-2">
            <svg class="w-3.5 h-3.5 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            <span>Refrigerate at 2–8°C immediately upon receipt. Do not freeze.</span>
        </div>
    @endif
</div>
