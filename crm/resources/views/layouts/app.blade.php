<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'CRM' }} — Prescribe & Co</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>
<body class="h-full bg-crm-canvas font-sans antialiased">

<div x-data="{ sidebarOpen: false }" class="flex h-full">

    {{-- ── Mobile sidebar overlay ───────────────────────────────────────── --}}
    <div
        x-show="sidebarOpen"
        x-transition:enter="transition-opacity ease-linear duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="sidebarOpen = false"
        class="fixed inset-0 z-40 bg-plum-950/60 lg:hidden"
        x-cloak
    ></div>

    {{-- ── Sidebar ─────────────────────────────────────────────────────── --}}
    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-sidebar-gradient transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 lg:transition-none"
    >
        @include('layouts.partials.sidebar')
    </aside>

    {{-- ── Main area ───────────────────────────────────────────────────── --}}
    <div class="flex min-w-0 flex-1 flex-col overflow-hidden">

        {{-- Topbar --}}
        <header class="sticky top-0 z-30 flex h-16 shrink-0 items-center gap-4 border-b border-plum-100 bg-white px-4 shadow-topbar sm:px-6 lg:px-8">
            @include('layouts.partials.topbar')
        </header>

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="mx-4 mt-4 flex items-start gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 sm:mx-6 lg:mx-8" role="alert">
                <svg class="mt-0.5 size-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mx-4 mt-4 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 sm:mx-6 lg:mx-8" role="alert">
                <svg class="mt-0.5 size-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1.293-9.707a1 1 0 011.414 0L10 9.586l-.879.879a1 1 0 01-1.414-1.414L8.586 8l-.879-.879a1 1 0 010-1.414zM10 13a1 1 0 110 2 1 1 0 010-2z" clip-rule="evenodd"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if(session('info'))
            <div class="mx-4 mt-4 flex items-start gap-3 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800 sm:mx-6 lg:mx-8" role="alert">
                <svg class="mt-0.5 size-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd"/></svg>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        {{-- Page content --}}
        <main class="flex-1 overflow-y-auto px-4 py-6 sm:px-6 lg:px-8">
            {{ $slot }}
        </main>

    </div>{{-- /main area --}}

</div>{{-- /flex wrapper --}}

@stack('scripts')
</body>
</html>
