<x-layouts.app>
    <x-slot name="pageTitle">{{ $staff->full_name }}</x-slot>

    <div class="max-w-2xl space-y-5 animate-fade-in">

        <a href="{{ route('staff.index') }}" class="text-sm font-medium text-lilac-600 hover:text-lilac-800">← Staff</a>

        {{-- Profile card --}}
        <div class="rounded-2xl border border-plum-100 bg-white p-6 shadow-plum-sm">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-plum-800 text-xl font-bold text-lilac-300">
                        {{ strtoupper(substr($staff->first_name, 0, 1) . substr($staff->last_name, 0, 1)) }}
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-plum-800">{{ $staff->full_name }}</h2>
                        <p class="text-sm text-plum-400">{{ $staff->email }}</p>
                        <p class="mt-1 text-xs text-plum-500">{{ $staff->roleLabel() }}</p>
                    </div>
                </div>
                <div class="flex flex-col items-end gap-2">
                    @if($staff->trashed())
                        <span class="rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700">Deactivated</span>
                    @elseif(! $staff->active)
                        <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-700">Pending setup</span>
                    @else
                        <span class="rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">Active</span>
                    @endif
                    @if($staff->hasTwoFactorEnabled())
                        <span class="rounded-full bg-plum-100 px-2.5 py-0.5 text-xs font-medium text-plum-600">2FA ✓</span>
                    @else
                        <span class="rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-600">2FA pending</span>
                    @endif
                </div>
            </div>

            <dl class="mt-5 grid grid-cols-2 gap-x-6 gap-y-3 border-t border-plum-50 pt-4 text-sm">
                @if($staff->gphc_number)
                    <div>
                        <dt class="text-xs text-plum-400">GPhC number</dt>
                        <dd class="font-mono font-medium text-plum-800">{{ $staff->gphc_number }}</dd>
                    </div>
                @endif
                <div>
                    <dt class="text-xs text-plum-400">Account created</dt>
                    <dd class="text-plum-700">{{ $staff->created_at->format('d M Y') }}</dd>
                </div>
                @if($staff->welcome_completed_at)
                    <div>
                        <dt class="text-xs text-plum-400">Setup completed</dt>
                        <dd class="text-plum-700">{{ $staff->welcome_completed_at->format('d M Y') }}</dd>
                    </div>
                @endif
                @if($staff->max_daily_consultations)
                    <div>
                        <dt class="text-xs text-plum-400">Max consultations/day</dt>
                        <dd class="text-plum-700">{{ $staff->max_daily_consultations }}</dd>
                    </div>
                @endif
                @if($staff->out_of_office)
                    <div class="col-span-2">
                        <dt class="text-xs text-plum-400">Status</dt>
                        <dd class="font-medium text-amber-600">Out of office</dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- Admin actions --}}
        <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm">
            <h3 class="mb-4 text-sm font-semibold text-plum-700">Actions</h3>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('staff.edit', $staff) }}"
                    class="rounded-xl border border-plum-200 px-4 py-2 text-sm text-plum-600 hover:bg-plum-50">
                    Edit account
                </a>

                @if(! $staff->welcome_completed_at)
                    <form method="POST" action="{{ route('staff.resend-welcome', $staff) }}">
                        @csrf
                        <button type="submit" class="rounded-xl border border-plum-200 px-4 py-2 text-sm text-plum-600 hover:bg-plum-50">
                            Resend welcome email
                        </button>
                    </form>
                @endif

                @if($staff->active && ! $staff->trashed())
                    <form method="POST" action="{{ route('staff.force-logout', $staff) }}">
                        @csrf
                        <button type="submit" class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-2 text-sm text-amber-700 hover:bg-amber-100">
                            Force sign out
                        </button>
                    </form>
                @endif

                @if($staff->id !== auth('staff')->id())
                    @if(! $staff->trashed())
                        <form method="POST" action="{{ route('staff.deactivate', $staff) }}"
                            onsubmit="return confirm('Deactivate {{ $staff->full_name }}? They will be signed out immediately.')">
                            @csrf
                            <button type="submit" class="rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-600 hover:bg-red-100">
                                Deactivate account
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('staff.reactivate', $staff) }}">
                            @csrf
                            <button type="submit" class="rounded-xl border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-700 hover:bg-green-100">
                                Reactivate account
                            </button>
                        </form>
                    @endif
                @endif
            </div>
        </div>

        {{-- Suspicious login alerts --}}
        @if($suspiciousAlerts->isNotEmpty())
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 shadow-plum-sm">
                <p class="mb-3 text-sm font-semibold text-amber-800">⚠ Suspicious Login Alerts (last 30 days)</p>
                <div class="space-y-2">
                    @foreach($suspiciousAlerts as $alert)
                        <div class="flex items-center justify-between rounded-xl border border-amber-100 bg-white px-4 py-2">
                            <div class="flex items-center gap-2">
                                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-amber-700">
                                    {{ ucfirst(str_replace(['suspicious_login.', '_'], ['', ' '], $alert->action)) }}
                                </span>
                                <p class="text-xs text-plum-600">IP: {{ $alert->ip_address }}</p>
                            </div>
                            <p class="text-xs text-plum-400">{{ $alert->created_at->format('d M Y, H:i') }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Paginated session log --}}
        <div class="rounded-2xl border border-plum-100 bg-white shadow-plum-sm">
            <div class="flex items-center justify-between border-b border-plum-50 px-5 py-4">
                <h3 class="text-sm font-semibold text-plum-700">Login Activity</h3>
                <a href="{{ route('staff.sessions.index') }}" class="text-xs font-medium text-lilac-600 hover:text-lilac-800">All active sessions →</a>
            </div>
            @forelse($sessionLogs as $log)
                <div class="flex items-center justify-between border-b border-plum-50 px-5 py-3 last:border-0">
                    <div class="flex items-center gap-3">
                        <span class="size-2 rounded-full {{ $log->success ? 'bg-green-500' : 'bg-red-400' }}"></span>
                        <div>
                            <p class="text-xs font-medium text-plum-700">
                                {{ $log->success ? 'Successful login' : 'Failed attempt' }}
                                @if($log->failure_reason)
                                    <span class="font-normal text-plum-400">— {{ str_replace('_', ' ', $log->failure_reason) }}</span>
                                @endif
                            </p>
                            <p class="text-xs text-plum-400">{{ $log->ip_address }}</p>
                        </div>
                    </div>
                    <p class="text-xs text-plum-400">{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y, H:i') }}</p>
                </div>
            @empty
                <div class="px-5 py-8 text-center text-sm text-plum-400">No login activity recorded.</div>
            @endforelse
            @if($sessionLogs->hasPages())
                <div class="border-t border-plum-50 px-5 py-3">
                    {{ $sessionLogs->links() }}
                </div>
            @endif
        </div>

    </div>
</x-layouts.app>
