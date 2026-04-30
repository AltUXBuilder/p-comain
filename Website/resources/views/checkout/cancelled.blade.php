<x-app-layout>
    <x-slot name="title">Payment cancelled</x-slot>
    <div class="max-w-lg mx-auto px-4 py-20 text-center">
        <div class="w-16 h-16 rounded-full bg-plum-100 flex items-center justify-center mx-auto mb-6">
            <svg class="w-8 h-8 text-plum-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </div>
        <h1 class="font-display text-display-sm font-bold text-plum-800 mb-3">Payment cancelled</h1>
        <p class="text-plum-500 mb-8">Your payment was not completed. No charge has been made.</p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('treatments.index') }}" class="btn-primary">Browse treatments</a>
            <a href="{{ route('patient.dashboard') }}" class="btn-secondary">My account</a>
        </div>
    </div>
</x-app-layout>
