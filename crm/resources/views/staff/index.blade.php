<x-layouts.app>
    <x-slot name="pageTitle">Staff Management</x-slot>

    <div class="space-y-5 animate-fade-in">

        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-plum-800">Staff Accounts</h2>
            <a
                href="{{ route('staff.create') }}"
                class="flex items-center gap-2 rounded-xl bg-plum-800 px-4 py-2 text-sm font-medium text-lilac-200 hover:bg-plum-900"
            >
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Add Staff Member
            </a>
        </div>

        {{-- Search --}}
        <div class="rounded-2xl border border-plum-100 bg-white p-4 shadow-plum-sm">
            <form method="GET" action="{{ route('staff.index') }}" class="flex gap-3">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search by name or email…"
                    class="flex-1 rounded-xl border-plum-200 py-2 text-sm text-plum-800 placeholder:text-plum-300 focus:border-lilac-500 focus:ring-lilac-500"
                >
                <select name="role" class="rounded-xl border-plum-200 py-2 text-sm text-plum-700 focus:border-lilac-500 focus:ring-lilac-500">
                    <option value="">All roles</option>
                    @foreach(\App\Models\Staff::ROLES as $key => $label)
                        <option value="{{ $key }}" {{ request('role') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-xl bg-plum-800 px-4 py-2 text-sm font-medium text-lilac-200 hover:bg-plum-900">Search</button>
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-hidden rounded-2xl border border-plum-100 bg-white shadow-plum-sm">
            <table class="min-w-full divide-y divide-plum-100">
                <thead class="bg-plum-50/60">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Name</th>
                        <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 sm:table-cell">Role</th>
                        <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 md:table-cell">GPhC</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-plum-500"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-plum-50">
                    @forelse($staff as $member)
                        <tr class="{{ $member->trashed() ? 'opacity-50' : 'hover:bg-plum-50/20' }} transition">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-plum-100 text-xs font-semibold text-plum-700">
                                        {{ strtoupper(substr($member->first_name, 0, 1) . substr($member->last_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-plum-800">{{ $member->full_name }}</p>
                                        <p class="text-xs text-plum-400">{{ $member->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="hidden px-4 py-3 text-sm text-plum-600 sm:table-cell">{{ $member->roleLabel() }}</td>
                            <td class="hidden px-4 py-3 text-sm font-mono text-plum-500 md:table-cell">{{ $member->gphc_number ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if($member->trashed())
                                    <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">Deactivated</span>
                                @elseif(! $member->active)
                                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700">Pending setup</span>
                                @else
                                    <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">Active</span>
                                @endif
                                @if($member->active && ! $member->hasTwoFactorEnabled())
                                    <span class="ml-1 rounded-full bg-plum-100 px-2 py-0.5 text-xs font-medium text-plum-500">2FA pending</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('staff.show', $member) }}" class="rounded-lg border border-plum-200 px-3 py-1.5 text-xs font-medium text-plum-600 hover:bg-plum-50">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-sm text-plum-400">No staff accounts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($staff->hasPages())
            <div class="flex justify-center">{{ $staff->links() }}</div>
        @endif

    </div>
</x-layouts.app>
