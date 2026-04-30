<x-app-layout>
<x-slot name="title">How It Works</x-slot>

<div class="bg-plum-800 py-20 text-center">
    <div class="max-w-2xl mx-auto px-4">
        <h1 class="font-display text-display-lg font-bold text-white mb-4">How it works</h1>
        <p class="text-lilac-500/80 text-lg">From first consultation to delivery — here's exactly what happens at every step.</p>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">

    {{-- Steps --}}
    <div class="relative">
        {{-- Vertical line --}}
        <div class="absolute left-6 top-0 bottom-0 w-px bg-plum-200 hidden sm:block"></div>

        <div class="space-y-10">
            @php
                $steps = [
                    ['n'=>'1','emoji'=>'🔍','title'=>'Choose a treatment','body'=>'Browse our five treatment categories and select the product you\'re interested in. Each product page explains exactly what it is, how it works and what to expect.','detail'=>'You don\'t need to create an account to start browsing. When you\'re ready, click "Start free consultation".'],
                    ['n'=>'2','emoji'=>'📋','title'=>'Complete your consultation','body'=>'Answer a short medical questionnaire — usually 5–10 questions. We ask about your health history, current medications and any relevant symptoms.','detail'=>'You can save your progress and come back if needed. Your answers are stored securely and only visible to our clinical team.'],
                    ['n'=>'3','emoji'=>'👤','title'=>'Create or log in to your account','body'=>'When you\'ve finished the questionnaire, you\'ll be asked to create an account (or log in if you already have one). This is needed to securely submit your consultation.','detail'=>'We collect your name, email, date of birth, address and password. Your email must be verified before your consultation is submitted.'],
                    ['n'=>'4','emoji'=>'🏥','title'=>'Prescriber review','body'=>'A UK-registered prescriber reviews your consultation answers. They check for any contraindications and assess clinical appropriateness.','detail'=>'We aim to review consultations within a few hours during business hours. You\'ll receive an email notification once your consultation has been reviewed.'],
                    ['n'=>'5','emoji'=>'✅','title'=>'Prescription approved','body'=>'If your consultation is approved, you\'ll receive an email with a link to complete your purchase. Your prescription has been issued and is ready.','detail'=>'If your consultation is not approved, you\'ll receive an email explaining why. You may be advised to see your GP or consult a specialist.'],
                    ['n'=>'6','emoji'=>'💳','title'=>'Complete your order','body'=>'Choose your preferred plan — one-off purchase or a subscription for regular deliveries. Pay securely via Stripe.','detail'=>'All payments are secured by Stripe\'s 256-bit SSL encryption. We accept all major credit and debit cards. We never store your card details.'],
                    ['n'=>'7','emoji'=>'📦','title'=>'Dispensed and dispatched','body'=>'Your prescription is dispensed by our registered pharmacy team and dispatched in discreet, plain packaging.','detail'=>'Cold chain products (such as GLP-1 injectables) are shipped with appropriate cooling. You\'ll receive a dispatch notification with your tracking number.'],
                    ['n'=>'8','emoji'=>'🔄','title'=>'Ongoing care','body'=>'Manage your treatments from your account — reorder, pause or cancel subscriptions, message our team and view your prescription history.','detail'=>'Repeat prescriptions are reviewed by a prescriber at each renewal to ensure treatment is still appropriate for you.'],
                ];
            @endphp

            @foreach ($steps as $step)
                <div class="flex gap-6 sm:gap-10">
                    {{-- Circle --}}
                    <div class="w-12 h-12 rounded-full bg-plum-800 flex items-center justify-center shrink-0 relative z-10">
                        <span class="text-lilac-400 font-display font-bold text-lg">{{ $step['n'] }}</span>
                    </div>
                    {{-- Content --}}
                    <div class="flex-1 pb-6">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="text-2xl">{{ $step['emoji'] }}</span>
                            <h2 class="font-display text-xl font-bold text-plum-800">{{ $step['title'] }}</h2>
                        </div>
                        <p class="text-plum-700 mb-2 leading-relaxed">{{ $step['body'] }}</p>
                        <p class="text-sm text-plum-500 leading-relaxed">{{ $step['detail'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Ready CTA --}}
    <div class="mt-16 card bg-plum-800 p-10 text-center">
        <h2 class="font-display text-display-sm font-bold text-white mb-4">Ready to get started?</h2>
        <p class="text-lilac-500/70 mb-8 max-w-md mx-auto">Choose a treatment below. Your consultation is free — you only pay if your prescription is approved and you choose to proceed.</p>
        <a href="{{ route('treatments.index') }}" class="btn-lilac btn-lg">Browse treatments</a>
    </div>
</div>

</x-app-layout>
