<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' — My Account · Prescribe & Co' : 'My Account — Prescribe & Co' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" type="image/png" href="/images/brand/favicon.png">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-plum-50 min-h-screen">

    {{-- Top nav --}}
    <header class="bg-white border-b border-plum-100 sticky top-0 z-40 shadow-plum-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('home') }}">
                <img src="/images/brand/logo-light.png" alt="Prescribe & Co" class="h-8 w-auto">
            </a>
            <div class="flex items-center gap-4">
                <span class="hidden sm:block text-sm text-plum-500">
                    Hello, {{ auth()->user()->first_name }}
                </span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-plum-500 hover:text-plum-800 transition-colors">
                        Sign out
                    </button>
                </form>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col lg:flex-row gap-8">

            {{-- Sidebar navigation --}}
            <aside class="lg:w-60 shrink-0">
                <nav class="bg-white rounded-2xl border border-plum-100 shadow-plum-sm overflow-hidden">
                    @php
                        $navItems = [
                            ['route' => 'patient.dashboard',          'label' => 'Dashboard',       'icon' => 'grid'],
                            ['route' => 'patient.consultations.index', 'label' => 'Consultations',   'icon' => 'clipboard'],
                            ['route' => 'patient.prescriptions.index', 'label' => 'Prescriptions',   'icon' => 'document'],
                            ['route' => 'patient.orders.index',        'label' => 'Orders',          'icon' => 'shopping-bag'],
                            ['route' => 'patient.subscriptions.index', 'label' => 'Subscriptions',   'icon' => 'refresh'],
                            ['route' => 'patient.messages.index',      'label' => 'Messages',        'icon' => 'chat'],
                            ['route' => 'patient.settings',            'label' => 'Settings',        'icon' => 'cog'],
                        ];
                    @endphp

                    @foreach ($navItems as $item)
                        @php $active = request()->routeIs($item['route'] . '*'); @endphp
                        <a href="{{ route($item['route']) }}"
                           class="flex items-center gap-3 px-5 py-3.5 text-sm font-medium border-b border-plum-50 last:border-0 transition-colors
                                  {{ $active ? 'bg-plum-800 text-lilac-500' : 'text-plum-600 hover:bg-plum-50 hover:text-plum-800' }}">
                            <x-ui.nav-icon name="{{ $item['icon'] }}" class="w-4 h-4 shrink-0" />
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>

                {{-- Need help? --}}
                <div class="mt-4 bg-plum-800 rounded-2xl p-5 text-center">
                    <p class="text-lilac-500 font-display text-lg font-bold mb-1">Need help?</p>
                    <p class="text-lilac-500/70 text-xs mb-3">Our pharmacy team is here for you.</p>
                    <a href="{{ route('patient.messages.index') }}" class="btn-lilac btn-sm w-full text-xs">
                        Message us
                    </a>
                </div>
            </aside>

            {{-- Main content area --}}
            <main class="flex-1 min-w-0">

                {{-- Breadcrumb --}}
                @isset($breadcrumb)
                    <div class="mb-6 text-sm text-plum-400">
                        {{ $breadcrumb }}
                    </div>
                @endisset

                {{-- Page title --}}
                @isset($pageTitle)
                    <h1 class="font-display text-display-sm font-bold text-plum-800 mb-6">
                        {{ $pageTitle }}
                    </h1>
                @endisset

                {{-- Flash messages --}}
                @if (session('success'))
                    <x-ui.alert type="success" class="mb-6">{{ session('success') }}</x-ui.alert>
                @endif
                @if (session('error'))
                    <x-ui.alert type="error" class="mb-6">{{ session('error') }}</x-ui.alert>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
