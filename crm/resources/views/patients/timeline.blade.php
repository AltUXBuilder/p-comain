<x-layouts.app>
    <x-slot name="pageTitle">{{ $patient->full_name }} — Timeline</x-slot>

    <div class="space-y-5 animate-fade-in">

        <div class="flex items-center gap-3">
            <a href="{{ route('patients.show', $patient) }}" class="text-sm font-medium text-lilac-600 hover:text-lilac-800">← Back to record</a>
        </div>

        <h2 class="text-xl font-semibold text-plum-800">{{ $patient->full_name }}'s Timeline</h2>

        @if($events->isEmpty())
            <div class="rounded-2xl border border-plum-100 bg-white p-12 text-center text-sm text-plum-400">
                No events on record.
            </div>
        @else
            <div class="relative ml-3 space-y-0">
                {{-- Vertical line --}}
                <div class="absolute left-3 top-0 h-full w-px bg-plum-200"></div>

                @foreach($events as $event)
                    <div class="relative flex gap-4 pb-6">
                        {{-- Dot --}}
                        <div class="relative z-10 flex size-7 shrink-0 items-center justify-center rounded-full border-2 border-white
                            {{ match($event['type']) {
                                'consultation' => 'bg-lilac-500',
                                'prescription' => 'bg-plum-600',
                                'order'        => 'bg-green-500',
                                'note'         => 'bg-amber-400',
                                default        => 'bg-plum-300',
                            } }}">
                            <svg class="size-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <circle cx="10" cy="10" r="4"/>
                            </svg>
                        </div>

                        <div class="min-w-0 flex-1 rounded-xl border border-plum-100 bg-white px-4 py-3 shadow-plum-sm">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-sm font-medium text-plum-800">{{ $event['label'] }}</p>
                                <time class="shrink-0 text-xs text-plum-400">{{ \Carbon\Carbon::parse($event['date'])->format('d M Y, H:i') }}</time>
                            </div>
                            @if($event['meta'])
                                <p class="mt-0.5 text-xs text-plum-500">{{ $event['meta'] }}</p>
                            @endif
                            @if($event['status'])
                                <span class="mt-1 inline-flex rounded-full bg-plum-100 px-2 py-0.5 text-[10px] font-medium text-plum-600">{{ ucfirst($event['status']) }}</span>
                            @endif
                            @if($event['link'])
                                <a href="{{ $event['link'] }}" class="mt-1 block text-xs font-medium text-lilac-600 hover:text-lilac-800">View →</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</x-layouts.app>
