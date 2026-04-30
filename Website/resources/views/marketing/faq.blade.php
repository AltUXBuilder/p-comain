<x-app-layout>
<x-slot name="title">Frequently Asked Questions</x-slot>

<div class="bg-plum-800 py-16 text-center">
    <div class="max-w-2xl mx-auto px-4">
        <h1 class="font-display text-display-md font-bold text-white mb-4">Frequently asked questions</h1>
        <p class="text-lilac-500/80">Everything you need to know about Prescribe &amp; Co.</p>
    </div>
</div>

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16" x-data="{open: null}">

    @php
        $sections = [
            'Getting started' => [
                ['q'=>'Is Prescribe & Co a real pharmacy?','a'=>'Yes. Prescribe & Co is a GPhC-registered online pharmacy. All prescriptions are issued by UK-registered prescribers and dispensed by our registered pharmacy team. Our GPhC registration number is '.config('pharmacy.gphc_number', '[see our website footer]').'.'],
                ['q'=>'How does an online consultation work?','a'=>'You complete a short medical questionnaire for the treatment you\'re interested in. A UK-registered prescriber reviews your answers and — if clinically appropriate — issues a private prescription. You then complete your order and we dispatch your treatment.'],
                ['q'=>'Is the consultation really free?','a'=>'Yes, completely free. You only pay when your consultation is approved and you choose to complete your purchase.'],
                ['q'=>'Do I need a GP referral?','a'=>'No. You can complete a consultation directly on our platform. However, for some treatments, our prescribers may recommend you also speak with your GP.'],
            ],
            'Prescriptions & safety' => [
                ['q'=>'Who reviews my consultation?','a'=>'Your consultation is reviewed by a UK-registered prescriber — either an Independent Prescriber (IP) or a doctor (GMC registered). We never use automated systems to approve prescriptions.'],
                ['q'=>'What if my consultation is not approved?','a'=>'You\'ll receive an email explaining the outcome. A refusal is always made for clinical reasons — for example, a contraindication, an incomplete medical history, or because the treatment is not appropriate for your situation. We may recommend you see your GP.'],
                ['q'=>'Will my GP be told about my prescription?','a'=>'We do not routinely notify your GP. If our prescribers identify a significant clinical concern during your consultation, they may recommend you discuss this with your GP.'],
                ['q'=>'Are the medications genuine?','a'=>'All medications dispensed by Prescribe & Co are licensed UK medicines sourced from MHRA-approved suppliers. We do not dispense unlicensed or counterfeit medicines.'],
            ],
            'Orders & delivery' => [
                ['q'=>'How long does delivery take?','a'=>'Most orders are dispatched within 1–2 working days of payment. Standard delivery typically arrives within 1–3 working days after dispatch.'],
                ['q'=>'Is the packaging discreet?','a'=>'Yes. All orders are sent in plain, brown packaging with no external indication of contents. Our pharmacy name is not printed on the outside.'],
                ['q'=>'Do you offer free delivery?','a'=>'Yes — all orders include free standard UK delivery.'],
                ['q'=>'How are cold chain products delivered?','a'=>'Products requiring refrigeration (such as GLP-1 injectable pens) are shipped in insulated packaging with cooling elements to maintain the 2–8°C cold chain during transit.'],
            ],
            'Account & subscriptions' => [
                ['q'=>'Can I cancel a subscription?','a'=>'Yes, at any time from your account. You can also pause or change your subscription. Cancellations take effect at the end of the current billing period.'],
                ['q'=>'How do I view my prescription?','a'=>'Log in to your account, go to Prescriptions, and download the PDF. Accessing your prescription triggers a verification step for your security.'],
                ['q'=>'Is my data safe?','a'=>'Yes. We are registered with the ICO and fully GDPR compliant. Your health data is encrypted, never sold, and never shared without your consent. You have the right to request a copy of your data or request erasure at any time.'],
                ['q'=>'How do I contact support?','a'=>'You can message our pharmacy team directly through your account, or use the contact form on our website. We typically respond within one working day.'],
            ],
        ];
    @endphp

    @php $i = 0; @endphp
    @foreach ($sections as $sectionTitle => $questions)
        <div class="mb-10">
            <h2 class="font-display text-xl font-bold text-plum-800 mb-4 pb-3 border-b border-plum-200">{{ $sectionTitle }}</h2>
            <div class="space-y-2">
                @foreach ($questions as $faq)
                    <div class="card overflow-hidden">
                        <button @click="open === {{ $i }} ? open = null : open = {{ $i }}"
                                class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-plum-50 transition-colors gap-4">
                            <span class="font-medium text-plum-800 text-sm">{{ $faq['q'] }}</span>
                            <svg class="w-4 h-4 text-plum-400 shrink-0 transition-transform"
                                 :class="open === {{ $i }} ? 'rotate-180' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open === {{ $i }}" x-collapse>
                            <p class="px-6 pb-5 text-sm text-plum-500 leading-relaxed">{{ $faq['a'] }}</p>
                        </div>
                    </div>
                    @php $i++; @endphp
                @endforeach
            </div>
        </div>
    @endforeach

    <div class="card bg-plum-800 p-8 text-center mt-8">
        <p class="font-display text-xl font-bold text-white mb-2">Still have a question?</p>
        <p class="text-lilac-500/70 mb-5 text-sm">Our pharmacy team is happy to help.</p>
        <a href="{{ route('contact') }}" class="btn-lilac">Contact us</a>
    </div>
</div>

</x-app-layout>
