<x-app-layout>
    <x-slot name="title">Checkout — {{ $product->name }}</x-slot>
    <x-slot name="head">
        <script src="https://js.stripe.com/v3/"></script>
    </x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12"
         x-data="checkoutForm()"
         x-init="init()">

        <h1 class="font-display text-display-sm font-bold text-plum-800 mb-8">Complete your order</h1>

        <div class="grid lg:grid-cols-5 gap-10">

            {{-- ── Left: payment form ────────────────────────────────────── --}}
            <div class="lg:col-span-3 space-y-6">

                {{-- Plan selection --}}
                @if ($product->subscription_tiers && count($product->subscription_tiers) > 0)
                    <div class="card p-6">
                        <h2 class="font-display text-lg font-bold text-plum-800 mb-4">Choose your plan</h2>
                        <div class="space-y-3">
                            {{-- One-off --}}
                            @if ($product->price_one_off)
                                <label class="block cursor-pointer">
                                    <input type="radio" name="plan" value="one_off"
                                           x-model="planType"
                                           @change="selectOneOff()"
                                           class="sr-only peer">
                                    <div class="border-2 rounded-xl p-4 transition-all
                                                peer-checked:border-plum-800 peer-checked:bg-plum-50
                                                border-plum-200 hover:border-plum-400">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="font-medium text-plum-800 text-sm">One-off purchase</p>
                                                <p class="text-xs text-plum-500 mt-0.5">Buy once, no commitment</p>
                                            </div>
                                            <p class="font-display text-xl font-bold text-plum-800">
                                                £{{ number_format($product->price_one_off, 2) }}
                                            </p>
                                        </div>
                                    </div>
                                </label>
                            @endif

                            {{-- Subscription tiers --}}
                            @foreach ($product->subscription_tiers as $tier)
                                <label class="block cursor-pointer">
                                    <input type="radio" name="plan" value="{{ $tier['stripe_price_id'] ?? 'sub_' . $loop->index }}"
                                           x-model="selectedPriceId"
                                           @change="selectSubscription('{{ $tier['stripe_price_id'] ?? '' }}', '{{ $tier['label'] }}', {{ $tier['price'] }})"
                                           class="sr-only peer">
                                    <div class="border-2 rounded-xl p-4 transition-all
                                                peer-checked:border-plum-800 peer-checked:bg-plum-50
                                                border-plum-200 hover:border-plum-400">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <p class="font-medium text-plum-800 text-sm">{{ $tier['label'] }}</p>
                                                    <span class="badge-lilac text-xs">Subscribe &amp; save</span>
                                                </div>
                                                <p class="text-xs text-plum-500 mt-0.5">Delivered every {{ $tier['interval_count'] ?? 1 }} {{ $tier['interval'] ?? 'month' }}(s)</p>
                                            </div>
                                            <p class="font-display text-xl font-bold text-plum-800">
                                                £{{ number_format($tier['price'], 2) }}
                                            </p>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @else
                    {{-- Single price product --}}
                    <input type="hidden" x-bind:value="planType" value="one_off">
                @endif

                {{-- Delivery address --}}
                <div class="card p-6">
                    <h2 class="font-display text-lg font-bold text-plum-800 mb-4">Delivery address</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="label">Full name</label>
                            <input type="text" x-model="delivery.name"
                                   value="{{ $user->full_name }}"
                                   class="input" required>
                        </div>
                        <div>
                            <label class="label">Address line 1</label>
                            <input type="text" x-model="delivery.address_line_1"
                                   value="{{ $user->address_line_1 }}"
                                   class="input" required>
                        </div>
                        <div>
                            <label class="label">Address line 2 <span class="text-plum-300 font-normal">(optional)</span></label>
                            <input type="text" x-model="delivery.address_line_2"
                                   value="{{ $user->address_line_2 }}"
                                   class="input">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="label">City</label>
                                <input type="text" x-model="delivery.city"
                                       value="{{ $user->city }}"
                                       class="input" required>
                            </div>
                            <div>
                                <label class="label">Postcode</label>
                                <input type="text" x-model="delivery.postcode"
                                       value="{{ $user->postcode }}"
                                       class="input uppercase" required>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card details --}}
                <div class="card p-6">
                    <h2 class="font-display text-lg font-bold text-plum-800 mb-4">Payment details</h2>
                    <div id="stripe-card-element" class="input py-3.5 min-h-[44px]"></div>
                    <p id="stripe-error" class="error-message mt-2 hidden"></p>
                    <div class="mt-3 flex items-center gap-2 text-xs text-plum-400">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                        Payments are secured by Stripe. We never store your card details.
                    </div>
                </div>

                {{-- Submit --}}
                <button @click="submitPayment()"
                        :disabled="processing"
                        class="btn-primary w-full btn-lg text-base">
                    <span x-show="!processing">
                        <svg class="w-5 h-5 inline mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                        Pay £<span x-text="totalDisplay"></span>
                    </span>
                    <span x-show="processing" class="flex items-center gap-2">
                        <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Processing…
                    </span>
                </button>

                <p x-show="errorMessage" x-text="errorMessage" class="text-red-600 text-sm text-center"></p>

            </div>

            {{-- ── Right: order summary ──────────────────────────────────── --}}
            <div class="lg:col-span-2">
                <div class="card p-6 sticky top-24">
                    <h2 class="font-display text-lg font-bold text-plum-800 mb-4">Order summary</h2>

                    <div class="flex items-start gap-3 pb-4 border-b border-plum-100">
                        <div class="w-12 h-12 rounded-xl bg-lilac-100 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6 text-plum-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-plum-800 text-sm">{{ $product->name }}</p>
                            <p class="text-xs text-plum-400 mt-0.5">{{ $product->strength }} {{ $product->form }}</p>
                            <span class="badge-lilac mt-1 inline-flex text-xs">{{ $product->product_type }}</span>
                        </div>
                    </div>

                    <div class="space-y-2 py-4 border-b border-plum-100 text-sm">
                        <div class="flex justify-between text-plum-500">
                            <span>Plan</span>
                            <span x-text="planLabel">—</span>
                        </div>
                        <div class="flex justify-between text-plum-500">
                            <span>Subtotal</span>
                            <span>£<span x-text="subtotalDisplay">—</span></span>
                        </div>
                        <div class="flex justify-between text-plum-500">
                            <span>VAT</span>
                            <span>{{ $product->isPom() ? 'Exempt (POM)' : '20%' }}</span>
                        </div>
                        <div class="flex justify-between text-plum-500">
                            <span>Shipping</span>
                            <span>Free</span>
                        </div>
                    </div>

                    <div class="flex justify-between font-bold text-plum-800 pt-4 text-base">
                        <span>Total</span>
                        <span>£<span x-text="totalDisplay">—</span></span>
                    </div>

                    @if ($product->requires_cold_chain)
                        <div class="mt-4 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2.5 text-xs text-amber-800 flex items-start gap-2">
                            <svg class="w-3.5 h-3.5 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            <span>Requires refrigeration (2–8°C). Cold chain packaging included.</span>
                        </div>
                    @endif

                    <div class="mt-5 space-y-2 text-xs text-plum-400">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-3 h-3 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            GPhC registered pharmacy
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="w-3 h-3 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                            256-bit SSL encrypted payment
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="w-3 h-3 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/><path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H11a1 1 0 001-1V5a1 1 0 00-1-1H3z"/></svg>
                            Free UK delivery
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function checkoutForm() {
        return {
            stripe: null,
            elements: null,
            cardElement: null,
            clientSecret: null,
            paymentIntentId: null,
            setupIntentMode: false,
            processing: false,
            errorMessage: '',
            planType: '{{ $product->price_one_off ? "one_off" : "subscription" }}',
            selectedPriceId: null,
            planLabel: '{{ $product->price_one_off ? "One-off purchase" : "—" }}',
            amount: {{ ($product->price_one_off ?? 0) * 100 }},
            delivery: {
                name: '{{ $user->full_name }}',
                address_line_1: '{{ $user->address_line_1 }}',
                address_line_2: '{{ $user->address_line_2 ?? "" }}',
                city: '{{ $user->city }}',
                postcode: '{{ $user->postcode }}',
            },

            get totalDisplay() {
                return (this.amount / 100).toFixed(2);
            },
            get subtotalDisplay() {
                return (this.amount / 100).toFixed(2);
            },

            init() {
                this.stripe = Stripe('{{ config("cashier.key") }}');
                this.elements = this.stripe.elements();
                this.cardElement = this.elements.create('card', {
                    style: {
                        base: {
                            fontFamily: '"DM Sans", sans-serif',
                            fontSize: '15px',
                            color: '#4A3050',
                            '::placeholder': { color: '#b99ac6' },
                        },
                        invalid: { color: '#dc2626' },
                    }
                });
                this.cardElement.mount('#stripe-card-element');
                this.cardElement.on('change', ({error}) => {
                    const el = document.getElementById('stripe-error');
                    if (error) {
                        el.textContent = error.message;
                        el.classList.remove('hidden');
                    } else {
                        el.classList.add('hidden');
                    }
                });

                // Auto-initiate PaymentIntent for one-off
                if (this.planType === 'one_off') this.initiatePayment();
            },

            selectOneOff() {
                this.planType = 'one_off';
                this.planLabel = 'One-off purchase';
                this.amount = {{ ($product->price_one_off ?? 0) * 100 }};
                this.setupIntentMode = false;
                this.initiatePayment();
            },

            selectSubscription(priceId, label, price) {
                this.planType = 'subscription';
                this.selectedPriceId = priceId;
                this.planLabel = label;
                this.amount = price * 100;
                this.setupIntentMode = true;
                this.initiatePayment();
            },

            async initiatePayment() {
                try {
                    const res = await fetch('{{ route("checkout.initiate") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            product_id:       {{ $product->id }},
                            consultation_id:  {{ $consultation?->id ?? 'null' }},
                            quantity:         1,
                            plan_type:        this.planType,
                            stripe_price_id:  this.selectedPriceId,
                        })
                    });
                    const data = await res.json();
                    if (data.error) { this.errorMessage = data.error; return; }
                    this.clientSecret = data.client_secret;
                    this.paymentIntentId = data.payment_intent_id;
                    this.setupIntentMode = data.setup_intent ?? false;
                } catch(e) {
                    this.errorMessage = 'Failed to initialise payment. Please try again.';
                }
            },

            async submitPayment() {
                if (this.processing) return;
                this.processing = true;
                this.errorMessage = '';

                try {
                    let result;
                    if (this.setupIntentMode) {
                        result = await this.stripe.confirmCardSetup(this.clientSecret, {
                            payment_method: { card: this.cardElement }
                        });
                    } else {
                        result = await this.stripe.confirmCardPayment(this.clientSecret, {
                            payment_method: {
                                card: this.cardElement,
                                billing_details: { name: this.delivery.name, email: '{{ $user->email }}' }
                            }
                        });
                    }

                    if (result.error) {
                        this.errorMessage = result.error.message;
                        this.processing = false;
                        return;
                    }

                    // Confirm with our server
                    const confirmRes = await fetch(
                        this.setupIntentMode ? '{{ route("checkout.confirm-subscription") }}' : '{{ route("checkout.confirm") }}',
                        {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                payment_intent_id:  this.paymentIntentId,
                                setup_intent_id:    result.setupIntent?.id,
                                payment_method_id:  result.setupIntent?.payment_method,
                                product_id:         {{ $product->id }},
                                consultation_id:    {{ $consultation?->id ?? 'null' }},
                                stripe_price_id:    this.selectedPriceId,
                                plan_label:         this.planLabel,
                                quantity:           1,
                                delivery_address:   this.delivery,
                            })
                        }
                    );
                    const confirmData = await confirmRes.json();
                    if (confirmData.redirect) {
                        window.location.href = confirmData.redirect;
                    } else {
                        this.errorMessage = confirmData.error ?? 'Something went wrong. Please contact us.';
                        this.processing = false;
                    }
                } catch(e) {
                    this.errorMessage = 'Payment failed. Please try again or contact support.';
                    this.processing = false;
                }
            }
        }
    }
    </script>
    @endpush
</x-app-layout>
