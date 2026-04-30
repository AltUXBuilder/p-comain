<header
    x-data="{ open: false, scrolled: false }"
    x-init="window.addEventListener('scroll', () => scrolled = window.scrollY > 20)"
    :class="scrolled ? 'bg-white shadow-plum-sm' : 'bg-white'"
    class="sticky top-0 z-50 transition-shadow duration-300 border-b border-plum-100">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-18">

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="shrink-0">
                <img src="/images/brand/logo-light.png"
                     alt="Prescribe &amp; Co"
                     class="h-9 w-auto">
            </a>

            {{-- Desktop nav --}}
            <nav class="hidden lg:flex items-center gap-1">
                @php
                    $navLinks = [
                        ['route' => 'treatments.index', 'label' => 'Treatments'],
                        ['route' => 'how-it-works',     'label' => 'How It Works'],
                        ['route' => 'pricing',          'label' => 'Pricing'],
                        ['route' => 'about',            'label' => 'About'],
                        ['route' => 'blog.index',       'label' => 'Advice'],
                    ];
                @endphp
                @foreach ($navLinks as $link)
                    <a href="{{ route($link['route']) }}"
                       class="px-4 py-2 text-sm font-medium rounded-lg transition-colors
                              {{ request()->routeIs($link['route'] . '*') ? 'text-plum-800 bg-plum-50' : 'text-plum-600 hover:text-plum-800 hover:bg-plum-50' }}">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </nav>

            {{-- Desktop CTA --}}
            <div class="hidden lg:flex items-center gap-3">
                @auth
                    <a href="{{ route('patient.dashboard') }}" class="btn-ghost btn-sm text-sm">
                        My Account
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="btn-secondary btn-sm text-sm">Sign out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}"    class="btn-ghost btn-sm text-sm">Sign in</a>
                    <a href="{{ route('treatments.index') }}" class="btn-primary btn-sm text-sm">Start consultation</a>
                @endauth
            </div>

            {{-- Mobile hamburger --}}
            <button @click="open = !open"
                    class="lg:hidden p-2 rounded-lg text-plum-700 hover:bg-plum-50 transition-colors"
                    :aria-expanded="open"
                    aria-label="Toggle menu">
                <svg x-show="!open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg x-show="open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div x-show="open"
         x-collapse
         class="lg:hidden border-t border-plum-100 bg-white">
        <div class="px-4 py-4 space-y-1">
            @foreach ($navLinks as $link)
                <a href="{{ route($link['route']) }}"
                   class="block px-4 py-2.5 text-sm font-medium rounded-lg text-plum-700 hover:bg-plum-50 transition-colors">
                    {{ $link['label'] }}
                </a>
            @endforeach
            <div class="pt-3 border-t border-plum-100 space-y-2 mt-2">
                @auth
                    <a href="{{ route('patient.dashboard') }}" class="btn-secondary w-full text-sm justify-center">My Account</a>
                @else
                    <a href="{{ route('login') }}"    class="btn-secondary w-full text-sm justify-center">Sign in</a>
                    <a href="{{ route('treatments.index') }}" class="btn-primary w-full text-sm justify-center">Start consultation</a>
                @endauth
            </div>
        </div>
    </div>
</header>
