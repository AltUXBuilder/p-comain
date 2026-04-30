<footer class="bg-plum-800 text-lilac-500 mt-20">

    {{-- Main footer --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">

            {{-- Brand --}}
            <div class="lg:col-span-1">
                <img src="/images/brand/logo-dark.png"
                     alt="Prescribe &amp; Co"
                     class="h-12 w-auto mb-4">
                <p class="text-sm text-lilac-500/70 leading-relaxed mb-4">
                    A GPhC-registered online pharmacy delivering clinically reviewed prescriptions across the UK.
                </p>
                <p class="text-xs text-lilac-500/50">
                    GPhC Registration: {{ config('pharmacy.gphc_number', '9012345') }}
                </p>
            </div>

            {{-- Treatments --}}
            <div>
                <h3 class="font-display text-lg font-bold mb-4">Treatments</h3>
                <ul class="space-y-2 text-sm text-lilac-500/70">
                    <li><a href="{{ route('treatments.category', 'weight-loss') }}"       class="hover:text-lilac-400 transition-colors">Weight Loss</a></li>
                    <li><a href="{{ route('treatments.category', 'erectile-dysfunction') }}" class="hover:text-lilac-400 transition-colors">Erectile Dysfunction</a></li>
                    <li><a href="{{ route('treatments.category', 'skin-health') }}"       class="hover:text-lilac-400 transition-colors">Skin Health</a></li>
                    <li><a href="{{ route('treatments.category', 'hair-loss') }}"         class="hover:text-lilac-400 transition-colors">Hair Loss</a></li>
                    <li><a href="{{ route('treatments.category', 'digestive-health') }}"  class="hover:text-lilac-400 transition-colors">Digestive Health</a></li>
                </ul>
            </div>

            {{-- Company --}}
            <div>
                <h3 class="font-display text-lg font-bold mb-4">Company</h3>
                <ul class="space-y-2 text-sm text-lilac-500/70">
                    <li><a href="{{ route('about') }}"         class="hover:text-lilac-400 transition-colors">About Us</a></li>
                    <li><a href="{{ route('how-it-works') }}"  class="hover:text-lilac-400 transition-colors">How It Works</a></li>
                    <li><a href="{{ route('pricing') }}"       class="hover:text-lilac-400 transition-colors">Pricing</a></li>
                    <li><a href="{{ route('blog.index') }}"    class="hover:text-lilac-400 transition-colors">Clinical Advice</a></li>
                    <li><a href="{{ route('contact') }}"       class="hover:text-lilac-400 transition-colors">Contact</a></li>
                </ul>
            </div>

            {{-- Legal & Support --}}
            <div>
                <h3 class="font-display text-lg font-bold mb-4">Legal &amp; Support</h3>
                <ul class="space-y-2 text-sm text-lilac-500/70">
                    <li><a href="{{ route('faq') }}"              class="hover:text-lilac-400 transition-colors">FAQ</a></li>
                    <li><a href="{{ route('delivery-returns') }}" class="hover:text-lilac-400 transition-colors">Delivery &amp; Returns</a></li>
                    <li><a href="{{ route('privacy') }}"          class="hover:text-lilac-400 transition-colors">Privacy Policy</a></li>
                    <li><a href="{{ route('terms') }}"            class="hover:text-lilac-400 transition-colors">Terms of Service</a></li>
                    <li><a href="{{ route('cookies') }}"          class="hover:text-lilac-400 transition-colors">Cookie Policy</a></li>
                    <li><a href="{{ route('accessibility') }}"    class="hover:text-lilac-400 transition-colors">Accessibility</a></li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Trust badges --}}
    <div class="border-t border-plum-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-wrap items-center justify-center gap-6 text-xs text-lilac-500/60">
                <span class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-lilac-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    GPhC Registered Pharmacy
                </span>
                <span class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-lilac-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944z" clip-rule="evenodd"/></svg>
                    MHRA Compliant
                </span>
                <span class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-lilac-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                    SSL Secured
                </span>
                <span class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-lilac-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    ICO Registered
                </span>
                <span class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-lilac-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                    UK-Based Pharmacists
                </span>
            </div>
        </div>
    </div>

    {{-- Bottom bar --}}
    <div class="border-t border-plum-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <p class="text-center text-xs text-lilac-500/40">
                © {{ date('Y') }} Prescribe &amp; Co. All rights reserved.
                Prescribe &amp; Co is a trading name of [Company Name] Ltd. Registered in England &amp; Wales.
                Always read the label. Use only as directed.
                If symptoms persist, consult your doctor.
            </p>
        </div>
    </div>

</footer>
