<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $description ?? 'Prescribe & Co — UK online pharmacy. Clinically reviewed prescriptions, discreetly delivered.' }}">

    <title>{{ isset($title) ? $title . ' — Prescribe & Co' : 'Prescribe & Co — Online Pharmacy' }}</title>

    <!-- Canonical -->
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/images/brand/favicon.png">

    <!-- Fonts preconnect -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    {{ $head ?? '' }}
</head>
<body class="font-sans antialiased bg-white text-plum-800 min-h-screen flex flex-col">

    {{-- Trust bar at very top --}}
    <div class="bg-plum-800 text-lilac-500 py-2 px-4 text-xs">
        <div class="max-w-7xl mx-auto">
            <x-trust.trust-bar />
        </div>
    </div>

    {{-- Navigation --}}
    <x-nav.header />

    {{-- Flash messages --}}
    @if (session('success') || session('error') || session('status'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 w-full">
            @if (session('success'))
                <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
            @endif
            @if (session('error'))
                <x-ui.alert type="error">{{ session('error') }}</x-ui.alert>
            @endif
            @if (session('status') === 'verification-link-sent')
                <x-ui.alert type="success">A new verification link has been sent to your email address.</x-ui.alert>
            @endif
        </div>
    @endif

    {{-- Main content --}}
    <main class="flex-1">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <x-nav.footer />

    @livewireScripts
    @stack('scripts')
</body>
</html>
