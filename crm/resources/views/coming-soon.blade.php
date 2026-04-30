<x-layouts.app>
    <x-slot name="pageTitle">{{ $module }}</x-slot>

    <div class="flex flex-col items-center justify-center py-20 text-center animate-fade-in">
        <div class="mb-5 flex size-16 items-center justify-center rounded-2xl bg-plum-100">
            <svg class="size-8 text-plum-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>
        </div>
        <h2 class="font-display text-display-sm font-bold text-plum-800">{{ $module }}</h2>
        <p class="mt-2 max-w-sm text-sm text-plum-400">
            This module is coming in a future build phase. The CRM architecture and routing is ready for it.
        </p>
        <a href="{{ route('dashboard') }}" class="mt-6 text-sm font-medium text-lilac-600 hover:text-lilac-800">← Back to dashboard</a>
    </div>
</x-layouts.app>
