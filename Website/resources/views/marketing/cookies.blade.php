<x-app-layout>
<x-slot name="title">Cookie Policy</x-slot>
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <h1 class="font-display text-display-md font-bold text-plum-800 mb-8">Cookie Policy</h1>
    <p class="text-sm text-plum-400 mb-8">Last updated: {{ date('j F Y') }}</p>
    <div class="prose prose-sm max-w-none text-plum-600 space-y-6">
        <h2 class="font-display text-xl font-bold text-plum-800">What are cookies?</h2>
        <p>Cookies are small text files stored on your device when you visit a website. We use cookies to make our website work correctly and to improve your experience.</p>
        <h2 class="font-display text-xl font-bold text-plum-800">Cookies we use</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <thead><tr class="bg-plum-100"><th class="text-left px-3 py-2 text-plum-800">Cookie</th><th class="text-left px-3 py-2 text-plum-800">Purpose</th><th class="text-left px-3 py-2 text-plum-800">Duration</th></tr></thead>
                <tbody>
                    <tr class="border-b border-plum-100"><td class="px-3 py-2 font-mono text-xs">pando_session</td><td class="px-3 py-2">Session management and authentication</td><td class="px-3 py-2">Session</td></tr>
                    <tr class="border-b border-plum-100"><td class="px-3 py-2 font-mono text-xs">XSRF-TOKEN</td><td class="px-3 py-2">Security — cross-site request forgery protection</td><td class="px-3 py-2">Session</td></tr>
                    <tr class="border-b border-plum-100"><td class="px-3 py-2 font-mono text-xs">pando_draft_uuid</td><td class="px-3 py-2">Saves your consultation progress if you log out mid-questionnaire</td><td class="px-3 py-2">48 hours</td></tr>
                    <tr class="border-b border-plum-100"><td class="px-3 py-2 font-mono text-xs">pando_fp</td><td class="px-3 py-2">Device fingerprint for security (2FA new device detection)</td><td class="px-3 py-2">Session</td></tr>
                    <tr><td class="px-3 py-2 font-mono text-xs">_ga, _gid</td><td class="px-3 py-2">Google Analytics — anonymous usage statistics</td><td class="px-3 py-2">2 years</td></tr>
                </tbody>
            </table>
        </div>
        <h2 class="font-display text-xl font-bold text-plum-800">Managing cookies</h2>
        <p>You can disable cookies in your browser settings. Note that disabling session cookies will prevent you from logging in to your account. Analytics cookies can be opted out of at <a href="https://tools.google.com/dlpage/gaoptout" target="_blank" class="text-plum-800 underline">tools.google.com/dlpage/gaoptout</a>.</p>
    </div>
</div>
</x-app-layout>
