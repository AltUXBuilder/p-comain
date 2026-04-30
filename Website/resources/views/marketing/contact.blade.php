<x-app-layout>
<x-slot name="title">Contact Us</x-slot>

<div class="bg-plum-800 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="font-display text-display-md font-bold text-white mb-3">Get in touch</h1>
        <p class="text-lilac-500/80 text-lg">Our pharmacy team typically responds within one working day.</p>
    </div>
</div>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="grid lg:grid-cols-3 gap-12">

        {{-- Contact form --}}
        <div class="lg:col-span-2">
            <h2 class="font-display text-xl font-bold text-plum-800 mb-6">Send us a message</h2>

            @if (session('success'))
                <x-ui.alert type="success" class="mb-6">{{ session('success') }}</x-ui.alert>
            @endif

            <form method="POST" action="{{ route('contact.submit') }}" class="space-y-5">
                @csrf
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="label">Your name</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}"
                               class="input @error('name') input-error @enderror" required>
                        @error('name')<p class="error-message">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="email" class="label">Email address</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}"
                               class="input @error('email') input-error @enderror" required>
                        @error('email')<p class="error-message">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label for="subject" class="label">Subject</label>
                    <select id="subject" name="subject" class="input @error('subject') input-error @enderror">
                        <option value="">Select a topic…</option>
                        <option>Question about a treatment</option>
                        <option>Order or delivery enquiry</option>
                        <option>My consultation</option>
                        <option>Prescription query</option>
                        <option>Subscription management</option>
                        <option>Data or privacy request</option>
                        <option>Other</option>
                    </select>
                    @error('subject')<p class="error-message">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="message" class="label">Message</label>
                    <textarea id="message" name="message" rows="5"
                              class="input resize-none @error('message') input-error @enderror"
                              required maxlength="2000">{{ old('message') }}</textarea>
                    @error('message')<p class="error-message">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-center gap-4">
                    <button type="submit" class="btn-primary">Send message</button>
                    <p class="text-xs text-plum-400">We reply within one working day.</p>
                </div>
            </form>
        </div>

        {{-- Contact info --}}
        <div class="space-y-6">
            <div class="card p-6">
                <h3 class="font-display text-base font-bold text-plum-800 mb-4">Contact information</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex gap-3">
                        <svg class="w-4 h-4 text-plum-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <a href="mailto:{{ config('pharmacy.email') }}" class="text-plum-600 hover:text-plum-800">{{ config('pharmacy.email') }}</a>
                    </div>
                    @if (config('pharmacy.phone'))
                        <div class="flex gap-3">
                            <svg class="w-4 h-4 text-plum-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span class="text-plum-600">{{ config('pharmacy.phone') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card p-6">
                <h3 class="font-display text-base font-bold text-plum-800 mb-3">Response times</h3>
                <div class="space-y-2 text-sm text-plum-500">
                    <div class="flex justify-between"><span>General enquiries</span><span class="font-medium text-plum-700">1 working day</span></div>
                    <div class="flex justify-between"><span>Prescription queries</span><span class="font-medium text-plum-700">Same day</span></div>
                    <div class="flex justify-between"><span>Order issues</span><span class="font-medium text-plum-700">Same day</span></div>
                </div>
            </div>

            <div class="card p-6">
                <h3 class="font-display text-base font-bold text-plum-800 mb-2">Already a patient?</h3>
                <p class="text-sm text-plum-500 mb-4">Log in to message our pharmacy team directly about your treatment or order.</p>
                <a href="{{ route('patient.messages.index') }}" class="btn-primary btn-sm w-full justify-center text-sm">Message our team</a>
            </div>
        </div>
    </div>
</div>

</x-app-layout>
