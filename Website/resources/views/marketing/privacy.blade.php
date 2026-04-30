<x-app-layout>
<x-slot name="title">Privacy Policy</x-slot>
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <h1 class="font-display text-display-md font-bold text-plum-800 mb-8">Privacy Policy</h1>
    <div class="prose prose-sm max-w-none text-plum-600 space-y-6">
        <p class="text-sm text-plum-400 mb-8">Last updated: {{ date('j F Y') }}</p>

        <h2 class="font-display text-xl font-bold text-plum-800">1. Who we are</h2>
        <p>Prescribe &amp; Co is a GPhC-registered online pharmacy. We are the data controller for the personal information we collect about you. Our ICO registration number is {{ config('pharmacy.regulatory.ico_number') ?: '[ICO number]' }}.</p>

        <h2 class="font-display text-xl font-bold text-plum-800">2. What data we collect</h2>
        <p>We collect the following categories of personal data: name and contact details; date of birth; delivery address; medical and health information provided through consultations; payment information (processed by Stripe — we never store card details); device and usage data (IP address, browser type, pages visited).</p>

        <h2 class="font-display text-xl font-bold text-plum-800">3. How we use your data</h2>
        <p>We use your data to: provide our pharmacy services and process your prescriptions; communicate with you about your orders and consultations; comply with our regulatory obligations as a GPhC-registered pharmacy; improve our services. We do not sell your data to third parties. We do not use your data for advertising purposes.</p>

        <h2 class="font-display text-xl font-bold text-plum-800">4. Legal basis for processing</h2>
        <p>We process your health data under Article 9(2)(h) GDPR — processing necessary for the provision of health or social care treatment. We process your contact and payment data on the basis of contract performance and, where applicable, your consent.</p>

        <h2 class="font-display text-xl font-bold text-plum-800">5. Data retention</h2>
        <p>Prescription records and clinical data are retained for a minimum of 8 years in accordance with NHS and GPhC guidance. Other personal data is retained for the duration of your account plus 3 years, unless you request erasure.</p>

        <h2 class="font-display text-xl font-bold text-plum-800">6. Your rights</h2>
        <p>Under UK GDPR you have the right to: access your personal data; correct inaccurate data; request erasure (subject to legal retention obligations); restrict or object to processing; data portability. To exercise any of these rights, contact us at {{ config('pharmacy.email') }}.</p>

        <h2 class="font-display text-xl font-bold text-plum-800">7. Cookies</h2>
        <p>We use essential cookies for site functionality and session management. See our <a href="{{ route('cookies') }}" class="text-plum-800 underline">Cookie Policy</a> for details.</p>

        <h2 class="font-display text-xl font-bold text-plum-800">8. Contact</h2>
        <p>For any privacy-related queries, please contact us at <a href="mailto:{{ config('pharmacy.email') }}" class="text-plum-800 underline">{{ config('pharmacy.email') }}</a>. You also have the right to lodge a complaint with the ICO at <a href="https://ico.org.uk" class="text-plum-800 underline" target="_blank">ico.org.uk</a>.</p>
    </div>
</div>
</x-app-layout>
