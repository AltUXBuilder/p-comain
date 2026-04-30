<x-app-layout>
<x-slot name="title">Accessibility Statement</x-slot>
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <h1 class="font-display text-display-md font-bold text-plum-800 mb-8">Accessibility Statement</h1>
    <p class="text-sm text-plum-400 mb-8">Last updated: {{ date('j F Y') }}</p>
    <div class="prose prose-sm max-w-none text-plum-600 space-y-6">
        <p>Prescribe &amp; Co is committed to making this website accessible to as many people as possible. We aim to conform to the Web Content Accessibility Guidelines (WCAG) 2.1 at Level AA.</p>
        <h2 class="font-display text-xl font-bold text-plum-800">What we've done</h2>
        <ul class="list-disc list-inside space-y-1">
            <li>Semantic HTML structure with appropriate heading hierarchy</li>
            <li>Sufficient colour contrast throughout (minimum 4.5:1 for body text)</li>
            <li>All interactive elements are keyboard accessible</li>
            <li>Form fields have visible, associated labels</li>
            <li>Error messages are linked to their relevant fields</li>
            <li>The site is fully responsive and functional on mobile devices</li>
        </ul>
        <h2 class="font-display text-xl font-bold text-plum-800">Known limitations</h2>
        <p>We are continuously improving the accessibility of this site. If you encounter any barriers, please contact us at <a href="mailto:{{ config('pharmacy.email') }}" class="text-plum-800 underline">{{ config('pharmacy.email') }}</a> and we will do our best to assist you.</p>
        <h2 class="font-display text-xl font-bold text-plum-800">Reporting issues</h2>
        <p>If you have difficulty accessing any content on this site, please email us at <a href="mailto:{{ config('pharmacy.email') }}" class="text-plum-800 underline">{{ config('pharmacy.email') }}</a>. We aim to respond within 2 working days.</p>
    </div>
</div>
</x-app-layout>
