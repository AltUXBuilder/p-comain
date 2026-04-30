<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' — Prescribe & Co' : 'Prescribe & Co' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" type="image/png" href="/images/brand/favicon.png">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-plum-50 min-h-screen">

    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 px-4">

        {{-- Logo --}}
        <div class="mb-8">
            <a href="{{ route('home') }}" class="block">
                <img src="/images/brand/logo-light.png"
                     alt="Prescribe & Co"
                     class="h-12 w-auto mx-auto">
            </a>
        </div>

        {{-- Card --}}
        <div class="w-full sm:max-w-md">
            <div class="bg-white shadow-plum rounded-2xl px-8 py-8 border border-plum-100">

                {{-- Title --}}
                @isset($title)
                    <h1 class="font-display text-2xl font-bold text-plum-800 mb-1 text-center">
                        {{ $title }}
                    </h1>
                @endisset

                @isset($description)
                    <p class="text-sm text-plum-500 text-center mb-6">{{ $description }}</p>
                @endisset

                {{-- Flash --}}
                @if (session('success'))
                    <x-ui.alert type="success" class="mb-4">{{ session('success') }}</x-ui.alert>
                @endif
                @if (session('status'))
                    <x-ui.alert type="success" class="mb-4">{{ session('status') }}</x-ui.alert>
                @endif

                {{ $slot }}
            </div>
        </div>

        {{-- Back to site --}}
        <p class="mt-6 text-sm text-plum-400">
            <a href="{{ route('home') }}" class="hover:text-plum-700 transition-colors">
                ← Back to Prescribe & Co
            </a>
        </p>

        {{-- Trust line --}}
        <p class="mt-4 text-xs text-plum-300 flex items-center gap-2">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
            GPhC Registered Pharmacy · SSL Secured · ICO Registered
        </p>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
