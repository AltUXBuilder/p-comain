<x-app-layout>
    <x-slot name="title">Consultation submitted</x-slot>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 py-16 text-center">

        <div class="w-20 h-20 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>

        <h1 class="font-display text-display-md font-bold text-plum-800 mb-3">
            Consultation submitted
        </h1>
        <p class="text-plum-500 text-lg mb-2">
            Thank you, {{ auth()->user()->first_name }}.
        </p>
        <p class="text-plum-500 mb-10 max-w-md mx-auto">
            Your consultation for <strong>{{ $consultation->product->name }}</strong> is now with our prescribers.
            We'll email you at <strong>{{ auth()->user()->email }}</strong> once it's been reviewed — usually within a few hours during business hours.
        </p>

        {{-- Timeline --}}
        <div class="card p-8 text-left mb-10">
            <h2 class="font-display text-xl font-bold text-plum-800 mb-6 text-center">What happens next</h2>
            <div class="space-y-5">
                @foreach ([
                    ['step' => '1', 'title' => 'Prescriber review',   'body' => 'A UK-registered prescriber reviews your consultation and medical history.', 'done' => false],
                    ['step' => '2', 'title' => 'We email you',        'body' => 'You\'ll receive an email once your consultation is approved with a link to complete your purchase.', 'done' => false],
                    ['step' => '3', 'title' => 'Checkout',            'body' => 'Choose your preferred plan and complete your purchase securely.', 'done' => false],
                    ['step' => '4', 'title' => 'Dispensed & delivered', 'body' => 'Your treatment is dispensed by our pharmacist team and dispatched to your door.', 'done' => false],
                ] as $item)
                    <div class="flex gap-4">
                        <div class="w-8 h-8 rounded-full bg-plum-100 text-plum-800 flex items-center justify-center text-sm font-bold shrink-0 mt-0.5">
                            {{ $item['step'] }}
                        </div>
                        <div>
                            <p class="font-medium text-plum-800 text-sm">{{ $item['title'] }}</p>
                            <p class="text-plum-500 text-sm mt-0.5">{{ $item['body'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('patient.dashboard') }}" class="btn-primary">Go to my account</a>
            <a href="{{ route('treatments.index') }}"  class="btn-secondary">Browse other treatments</a>
        </div>

    </div>
</x-app-layout>
