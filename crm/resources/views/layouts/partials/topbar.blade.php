{{-- Topbar: white, plum border bottom --}}

{{-- Mobile hamburger --}}
<button
    @click="sidebarOpen = true"
    class="-m-2 p-2 text-plum-400 hover:text-plum-800 lg:hidden"
    aria-label="Open sidebar"
>
    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
    </svg>
</button>

{{-- Divider (mobile) --}}
<div class="h-6 w-px bg-plum-200 lg:hidden" aria-hidden="true"></div>

{{-- Page title / breadcrumb --}}
<div class="flex min-w-0 flex-1 items-center gap-2">
    @isset($pageTitle)
        <h1 class="truncate text-base font-semibold text-plum-800">{{ $pageTitle }}</h1>
    @endisset
    @isset($breadcrumb)
        {{ $breadcrumb }}
    @endisset
</div>

{{-- Right-side controls --}}
<div class="flex shrink-0 items-center gap-3">

    {{-- Out-of-office badge --}}
    @if(auth('staff')->user()->out_of_office)
        <span class="hidden rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-700 sm:inline-flex">
            Out of office
        </span>
    @endif

    {{-- Notifications --}}
    <div x-data="{ open: false }" class="relative">
        <button
            @click="open = !open"
            class="relative rounded-lg p-1.5 text-plum-400 hover:bg-plum-50 hover:text-plum-800"
            aria-label="Notifications"
        >
            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
            </svg>
            @php
                $unread = auth('staff')->user()->notifications()->whereNull('read_at')->count();
            @endphp
            @if($unread > 0)
                <span class="absolute -right-0.5 -top-0.5 flex size-4 items-center justify-center rounded-full bg-lilac-500 text-[10px] font-bold text-white">
                    {{ $unread > 9 ? '9+' : $unread }}
                </span>
            @endif
        </button>

        {{-- Notifications dropdown --}}
        <div
            x-show="open"
            @click.outside="open = false"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95 translate-y-1"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-1"
            class="absolute right-0 top-full z-50 mt-2 w-80 origin-top-right rounded-2xl border border-plum-100 bg-white shadow-plum"
            x-cloak
        >
            <div class="flex items-center justify-between border-b border-plum-100 px-4 py-3">
                <p class="text-sm font-semibold text-plum-800">Notifications</p>
                <a href="{{ route('notifications.index') }}" class="text-xs font-medium text-lilac-600 hover:text-lilac-800">View all</a>
            </div>
            <div class="max-h-80 divide-y divide-plum-50 overflow-y-auto">
                @forelse(auth('staff')->user()->notifications()->latest()->limit(8)->get() as $notification)
                    <a
                        href="{{ route('notifications.read', $notification) }}"
                        class="flex items-start gap-3 px-4 py-3 hover:bg-plum-50 {{ is_null($notification->read_at) ? 'bg-lilac-50/40' : '' }}"
                    >
                        <span class="mt-0.5 size-2 shrink-0 rounded-full {{ is_null($notification->read_at) ? 'bg-lilac-500' : 'bg-transparent' }}"></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm text-plum-700">{{ $notification->message }}</p>
                            <p class="mt-0.5 text-xs text-plum-400">{{ $notification->created_at->diffForHumans() }}</p>
                        </div>
                    </a>
                @empty
                    <div class="px-4 py-6 text-center text-sm text-plum-400">No notifications</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Profile dropdown --}}
    <div x-data="{ open: false }" class="relative">
        <button
            @click="open = !open"
            class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm hover:bg-plum-50"
            aria-label="Profile menu"
        >
            <div class="flex size-7 items-center justify-center rounded-full bg-plum-800 text-xs font-semibold text-lilac-300">
                {{ strtoupper(substr(auth('staff')->user()->first_name, 0, 1) . substr(auth('staff')->user()->last_name, 0, 1)) }}
            </div>
            <span class="hidden text-sm font-medium text-plum-700 sm:block">{{ auth('staff')->user()->first_name }}</span>
            <svg class="hidden size-4 text-plum-400 sm:block" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
            </svg>
        </button>

        <div
            x-show="open"
            @click.outside="open = false"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95 translate-y-1"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-cloak
            class="absolute right-0 top-full z-50 mt-2 w-52 origin-top-right rounded-xl border border-plum-100 bg-white py-1 shadow-plum"
        >
            <div class="border-b border-plum-100 px-4 py-3">
                <p class="text-sm font-medium text-plum-800">{{ auth('staff')->user()->full_name }}</p>
                <p class="text-xs text-plum-400">{{ auth('staff')->user()->email }}</p>
            </div>

            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-plum-700 hover:bg-plum-50">
                <svg class="size-4 text-plum-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                </svg>
                My Profile
            </a>

            <a href="{{ route('two-factor.recovery-codes') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-plum-700 hover:bg-plum-50">
                <svg class="size-4 text-plum-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                </svg>
                Recovery Codes
            </a>

            <div class="border-t border-plum-100 pt-1">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75"/>
                        </svg>
                        Sign out
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
