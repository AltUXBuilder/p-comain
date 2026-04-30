<x-patient-layout>
    <x-slot name="title">My Subscriptions</x-slot>
    <x-slot name="pageTitle">My Subscriptions</x-slot>

    @if ($subscriptions->isEmpty())
        <div class="card p-12 text-center">
            <div class="w-16 h-16 rounded-2xl bg-lilac-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-plum-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </div>
            <h2 class="font-display text-xl font-bold text-plum-800 mb-2">No active subscriptions</h2>
            <p class="text-plum-500 text-sm mb-6">Subscribe to a treatment plan for regular, automatic deliveries.</p>
            <a href="{{ route('treatments.index') }}" class="btn-primary">Browse treatments</a>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($subscriptions as $sub)
                @php
                    $isActive    = $sub->stripe_status === 'active';
                    $isPaused    = $sub->paused_from !== null;
                    $isCancelled = $sub->ends_at !== null && $sub->stripe_status === 'active';
                @endphp
                <div class="card p-6">
                    <div class="flex items-start justify-between gap-4 mb-4">
                        <div>
                            <p class="font-medium text-plum-800">{{ $sub->product->name ?? $sub->type }}</p>
                            <p class="text-xs text-plum-400 mt-0.5 capitalize">{{ str_replace('_', ' ', $sub->type) }}</p>
                        </div>
                        @if ($isPaused)
                            <span class="badge-amber">Paused</span>
                        @elseif ($isCancelled)
                            <span class="badge-amber">Cancels {{ $sub->ends_at->format('j M Y') }}</span>
                        @elseif ($isActive)
                            <span class="badge-green">Active</span>
                        @else
                            <span class="badge-red capitalize">{{ $sub->stripe_status }}</span>
                        @endif
                    </div>

                    {{-- Renewal info --}}
                    @if ($isActive && !$isCancelled && $sub->ends_at)
                        <p class="text-xs text-plum-500 mb-4">
                            Next renewal: {{ $sub->ends_at->format('j F Y') }}
                        </p>
                    @endif

                    {{-- Actions --}}
                    <div class="flex flex-wrap gap-2 pt-4 border-t border-plum-100">
                        @if ($isPaused)
                            <form method="POST" action="{{ route('patient.subscriptions.resume', $sub->id) }}">
                                @csrf
                                <button type="submit" class="btn-primary btn-sm text-xs">Resume subscription</button>
                            </form>
                        @elseif ($isActive && !$isCancelled)
                            <form method="POST" action="{{ route('patient.subscriptions.pause', $sub->id) }}">
                                @csrf
                                <button type="submit" class="btn-secondary btn-sm text-xs">Pause</button>
                            </form>
                            <form method="POST" action="{{ route('patient.subscriptions.cancel', $sub->id) }}"
                                  x-data
                                  @submit.prevent="if(confirm('Are you sure you want to cancel? Your subscription will remain active until the end of the current period.')) $el.submit()">
                                @csrf
                                <button type="submit" class="btn-ghost btn-sm text-xs text-red-600 hover:bg-red-50">Cancel</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</x-patient-layout>
