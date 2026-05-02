<x-layouts.app>
    <x-slot name="pageTitle">Active Sessions</x-slot>

    <div class="space-y-5 animate-fade-in">

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-plum-800">Active Staff Sessions</h2>
                <p class="mt-0.5 text-xs text-plum-400">{{ $sessions->count() }} active session(s) in the last 8 hours</p>
            </div>
            <a href="{{ route('staff.index') }}" class="text-sm font-medium text-lilac-600 hover:text-lilac-800">← Staff</a>
        </div>

        {{-- Suspicious login alerts --}}
        @if($suspiciousAlerts->isNotEmpty())
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 shadow-plum-sm">
                <p class="mb-3 text-sm font-semibold text-amber-800">⚠ Suspicious Login Alerts (last 7 days)</p>
                <div class="space-y-2">
                    @foreach($suspiciousAlerts as $alert)
                        @php
                            $typeLabel = match(str_replace('suspicious_login.', '', $alert->action)) {
                                'new_device'    => 'New device',
                                'unusual_ip'    => 'Unusual IP',
                                'outside_hours' => 'Outside hours',
                                default         => 'Suspicious',
                            };
                        @endphp
                        <div class="flex items-center justify-between rounded-xl border border-amber-200 bg-white px-4 py-2.5">
                            <div class="flex items-center gap-3">
                                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-amber-700">{{ $typeLabel }}</span>
                                <div>
                                    <p class="text-xs font-medium text-plum-800">{{ $alert->staff?->full_name ?? 'Unknown' }}</p>
                                    <p class="text-[10px] text-plum-400">
                                        IP: {{ $alert->ip_address }}
                                        @if(isset($alert->metadata['device_fingerprint']))
                                            · New device fingerprint
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <p class="text-xs text-plum-400">{{ $alert->created_at->format('d M Y, H:i') }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Active sessions table --}}
        <div class="overflow-hidden rounded-2xl border border-plum-100 bg-white shadow-plum-sm">
            <table class="min-w-full divide-y divide-plum-100">
                <thead class="bg-plum-50/60">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Staff Member</th>
                        <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 sm:table-cell">Role</th>
                        <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 md:table-cell">IP Address</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Last Active</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-plum-500"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-plum-50">
                    @forelse($sessions as $session)
                        <tr class="{{ $session->is_current ? 'bg-lilac-50/30' : 'hover:bg-plum-50/10' }} transition">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="relative">
                                        <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-plum-100 text-xs font-semibold text-plum-700">
                                            {{ $session->staff ? strtoupper(substr($session->staff->first_name, 0, 1) . substr($session->staff->last_name, 0, 1)) : '?' }}
                                        </div>
                                        {{-- Online indicator --}}
                                        @if($session->last_activity->gt(now()->subMinutes(15)))
                                            <span class="absolute -bottom-0.5 -right-0.5 size-2.5 rounded-full border-2 border-white bg-green-500"></span>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-plum-800">
                                            {{ $session->staff?->full_name ?? 'Unknown staff' }}
                                            @if($session->is_current)
                                                <span class="ml-1 text-xs font-normal text-lilac-500">(you)</span>
                                            @endif
                                        </p>
                                        <p class="text-xs text-plum-400 truncate max-w-40">{{ Str::limit($session->user_agent, 40) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="hidden px-4 py-3 text-sm text-plum-600 sm:table-cell">
                                {{ $session->staff?->roleLabel() ?? '—' }}
                            </td>
                            <td class="hidden px-4 py-3 font-mono text-xs text-plum-500 md:table-cell">
                                {{ $session->ip_address ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-xs text-plum-500">
                                {{ $session->last_activity->diffForHumans() }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if(! $session->is_current)
                                    <form method="POST" action="{{ route('staff.sessions.terminate', $session->id) }}"
                                        onsubmit="return confirm('Terminate this session?')">
                                        @csrf
                                        <button type="submit"
                                            class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">
                                            Terminate
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-plum-300">Current</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-sm text-plum-400">No active sessions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</x-layouts.app>
