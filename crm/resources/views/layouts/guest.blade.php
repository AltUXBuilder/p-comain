<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Sign In' }} — Prescribe & Co CRM</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full flex-col bg-crm-canvas font-sans antialiased">

    {{-- Split layout: plum left panel + white right form --}}
    <div class="flex min-h-screen">

        {{-- Left — brand panel (hidden on small screens) --}}
        <div class="hidden w-1/2 flex-col justify-between bg-plum-gradient px-12 py-12 lg:flex xl:px-16">
            <div>
                <img src="{{ asset('images/brand/logo-dark.png') }}" alt="Prescribe & Co" class="h-10 w-auto">
            </div>
            <div class="space-y-4">
                <p class="font-display text-display-sm font-bold leading-tight text-white">
                    Internal Clinical<br>Management System
                </p>
                <p class="text-base text-lilac-300">
                    Secure, GPhC-compliant. Authorised staff only.
                </p>
            </div>
            <div class="space-y-2">
                <p class="text-xs text-plum-400">GPhC Registered Online Pharmacy · MHRA Compliant · ICO Registered</p>
                <p class="text-xs text-plum-500">Access to this system is monitored and audited. Unauthorised access is prohibited.</p>
            </div>
        </div>

        {{-- Right — form panel --}}
        <div class="flex flex-1 flex-col items-center justify-center px-6 py-12 lg:px-12 xl:px-16">

            {{-- Mobile logo --}}
            <div class="mb-8 lg:hidden">
                <img src="{{ asset('images/brand/logo-light.png') }}" alt="Prescribe & Co" class="h-8 w-auto">
            </div>

            <div class="w-full max-w-sm">
                {{ $slot }}
            </div>

        </div>

    </div>

</body>
</html>
