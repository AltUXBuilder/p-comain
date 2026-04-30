{{-- Sidebar: plum background, lilac active states --}}

{{-- ── Logo ──────────────────────────────────────────────────────────────── --}}
<div class="flex h-16 shrink-0 items-center border-b border-plum-700/60 px-6">
    <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
        <img src="{{ asset('images/brand/logo-dark.png') }}" alt="Prescribe & Co" class="h-8 w-auto">
    </a>

    {{-- Close button (mobile only) --}}
    <button
        @click="sidebarOpen = false"
        class="ml-auto rounded-lg p-1.5 text-lilac-400 hover:bg-plum-700 hover:text-lilac-300 lg:hidden"
        aria-label="Close sidebar"
    >
        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
        </svg>
    </button>
</div>

{{-- ── Navigation ──────────────────────────────────────────────────────── --}}
<nav class="flex flex-1 flex-col overflow-y-auto px-4 py-4" aria-label="Sidebar navigation">

    {{-- Staff member pill --}}
    <div class="mb-4 flex items-center gap-3 rounded-xl bg-plum-700/50 px-3 py-2.5">
        <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-lilac-500/20 text-xs font-semibold text-lilac-300">
            {{ strtoupper(substr(auth('staff')->user()->first_name, 0, 1) . substr(auth('staff')->user()->last_name, 0, 1)) }}
        </div>
        <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-medium text-lilac-200">{{ auth('staff')->user()->full_name }}</p>
            <p class="truncate text-xs text-lilac-400">{{ auth('staff')->user()->roleLabel() }}</p>
        </div>
    </div>

    {{-- Nav items --}}
    <ul role="list" class="space-y-0.5">

        {{-- Dashboard — all roles --}}
        <x-crm.nav-item :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="home">
            Dashboard
        </x-crm.nav-item>

        {{-- Clinical section --}}
        @if(auth('staff')->user()->hasAnyRole(['super_admin', 'superintendent_pharmacist', 'prescriber']))
            <li class="mt-4 mb-1">
                <span class="px-3 text-[10px] font-semibold uppercase tracking-widest text-plum-400">Clinical</span>
            </li>

            <x-crm.nav-item :href="route('patients.index')" :active="request()->routeIs('patients.*')" icon="users">
                Patients
            </x-crm.nav-item>

            <x-crm.nav-item :href="route('consultations.queue')" :active="request()->routeIs('consultations.*')" icon="clipboard-document-list">
                Consultation Queue
            </x-crm.nav-item>

            <x-crm.nav-item :href="route('prescriptions.index')" :active="request()->routeIs('prescriptions.*')" icon="document-text">
                Prescriptions
            </x-crm.nav-item>
        @endif

        {{-- Dispensing section --}}
        @if(auth('staff')->user()->hasAnyRole(['super_admin', 'superintendent_pharmacist', 'dispenser']))
            <li class="mt-4 mb-1">
                <span class="px-3 text-[10px] font-semibold uppercase tracking-widest text-plum-400">Dispensing</span>
            </li>

            <x-crm.nav-item :href="route('dispensing.queue')" :active="request()->routeIs('dispensing.*')" icon="beaker">
                Dispensing Queue
            </x-crm.nav-item>

            <x-crm.nav-item :href="route('labels.index')" :active="request()->routeIs('labels.*')" icon="tag">
                Labels
            </x-crm.nav-item>
        @endif

        {{-- Orders & fulfilment --}}
        @if(auth('staff')->user()->hasAnyRole(['super_admin', 'superintendent_pharmacist', 'dispenser', 'customer_support']))
            <li class="mt-4 mb-1">
                <span class="px-3 text-[10px] font-semibold uppercase tracking-widest text-plum-400">Orders</span>
            </li>

            <x-crm.nav-item :href="route('orders.index')" :active="request()->routeIs('orders.*')" icon="shopping-bag">
                Orders
            </x-crm.nav-item>

            <x-crm.nav-item :href="route('messages.index')" :active="request()->routeIs('messages.*')" icon="chat-bubble-left-right">
                Messages
            </x-crm.nav-item>
        @endif

        {{-- Inventory --}}
        @if(auth('staff')->user()->hasAnyRole(['super_admin', 'superintendent_pharmacist', 'dispenser']))
            <li class="mt-4 mb-1">
                <span class="px-3 text-[10px] font-semibold uppercase tracking-widest text-plum-400">Inventory</span>
            </li>

            <x-crm.nav-item :href="route('stock.index')" :active="request()->routeIs('stock.*')" icon="cube">
                Stock
            </x-crm.nav-item>
        @endif

        {{-- Finance --}}
        @if(auth('staff')->user()->hasAnyRole(['super_admin', 'finance']))
            <li class="mt-4 mb-1">
                <span class="px-3 text-[10px] font-semibold uppercase tracking-widest text-plum-400">Finance</span>
            </li>

            <x-crm.nav-item :href="route('finance.dashboard')" :active="request()->routeIs('finance.*')" icon="banknotes">
                Finance
            </x-crm.nav-item>
        @endif

        {{-- Admin section --}}
        @if(auth('staff')->user()->isSuperAdmin())
            <li class="mt-4 mb-1">
                <span class="px-3 text-[10px] font-semibold uppercase tracking-widest text-plum-400">Admin</span>
            </li>

            <x-crm.nav-item :href="route('staff.index')" :active="request()->routeIs('staff.*')" icon="identification">
                Staff
            </x-crm.nav-item>

            <x-crm.nav-item :href="route('compliance.index')" :active="request()->routeIs('compliance.*')" icon="shield-check">
                Compliance
            </x-crm.nav-item>

            <x-crm.nav-item :href="route('settings.index')" :active="request()->routeIs('settings.*')" icon="cog-6-tooth">
                Settings
            </x-crm.nav-item>
        @endif

    </ul>

</nav>

{{-- ── Logout at the bottom ────────────────────────────────────────────── --}}
<div class="shrink-0 border-t border-plum-700/60 p-4">
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button
            type="submit"
            class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-plum-400 transition hover:bg-plum-700/50 hover:text-lilac-300"
        >
            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75"/>
            </svg>
            Sign out
        </button>
    </form>
</div>
