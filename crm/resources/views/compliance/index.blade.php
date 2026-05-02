<x-layouts.app>
    <x-slot name="pageTitle">Compliance & Governance</x-slot>
    <div class="space-y-5 animate-fade-in">
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('compliance.log') }}" class="rounded-xl border border-plum-200 px-4 py-2 text-sm font-medium text-plum-600 hover:bg-plum-50">Compliance Log</a>
            <a href="{{ route('compliance.rejections') }}" class="rounded-xl border border-plum-200 px-4 py-2 text-sm font-medium text-plum-600 hover:bg-plum-50">Rejection Register</a>
            <a href="{{ route('compliance.audit-log') }}" class="rounded-xl border border-plum-200 px-4 py-2 text-sm font-medium text-plum-600 hover:bg-plum-50">Audit Log</a>
        </div>

        {{-- GPhC Readiness Checklist --}}
        <div class="rounded-2xl border border-plum-100 bg-white shadow-plum-sm overflow-hidden">
            <div class="border-b border-plum-50 px-5 py-4">
                <h3 class="text-sm font-semibold text-plum-700">GPhC Inspection Readiness</h3>
                <p class="text-xs text-plum-400 mt-0.5">All registers and logs required for a GPhC inspection.</p>
            </div>
            <div class="divide-y divide-plum-50">
                @foreach($checklist as $item)
                    <div class="flex items-center justify-between px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="flex size-6 shrink-0 items-center justify-center rounded-full {{ $item['ok'] ? 'bg-green-100' : 'bg-red-100' }}">
                                <svg class="size-3.5 {{ $item['ok'] ? 'text-green-600' : 'text-red-500' }}" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    @if($item['ok'])
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0zm-9 3.75h.008v.008H12v-.008z"/>
                                    @endif
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-plum-800">{{ $item['label'] }}</p>
                                <p class="text-xs text-plum-400">{{ $item['detail'] }}</p>
                            </div>
                        </div>
                        <a href="{{ $item['route'] }}" class="text-xs font-medium text-lilac-600 hover:text-lilac-800">View →</a>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Log a compliance event --}}
        <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm" x-data="{ open: false }">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-plum-700">Log Compliance Event</h3>
                <button @click="open = !open" class="text-xs font-medium text-lilac-600 hover:text-lilac-800">+ New entry</button>
            </div>
            <div x-show="open" x-cloak class="mt-4 space-y-3">
                <form method="POST" action="{{ route('compliance.log.store') }}" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-plum-500">Type</label>
                            <select name="type" required class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                                @foreach(\App\Models\ComplianceLog::TYPES_GROUPED as $group => $types)
                                    <optgroup label="{{ $group }}">
                                        @foreach($types as $k)
                                            <option value="{{ $k }}">{{ \App\Models\ComplianceLog::TYPES[$k] }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-plum-500">Patient ID (optional)</label>
                            <input type="number" name="user_id" placeholder="Patient ID" class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-plum-500">Notes <span class="text-red-500">*</span></label>
                        <textarea name="notes" rows="3" required class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500"></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="rounded-xl bg-plum-800 px-4 py-2 text-sm font-medium text-lilac-200 hover:bg-plum-900">Save Record</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Recent compliance events --}}
        <div class="rounded-2xl border border-plum-100 bg-white shadow-plum-sm">
            <div class="border-b border-plum-50 px-5 py-4">
                <h3 class="text-sm font-semibold text-plum-700">Recent Compliance Events</h3>
            </div>
            @forelse($recentLogs as $log)
                <div class="flex items-start justify-between border-b border-plum-50 px-5 py-3 last:border-0">
                    <div>
                        <span class="text-xs font-semibold text-plum-700">{{ $log->typeLabel() }}</span>
                        @if($log->patient) <span class="text-xs text-plum-400">· {{ $log->patient->full_name }}</span> @endif
                        <p class="mt-0.5 text-xs text-plum-500">{{ Str::limit($log->notes, 100) }}</p>
                    </div>
                    <p class="shrink-0 text-xs text-plum-400">{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y') }}</p>
                </div>
            @empty
                <div class="px-5 py-8 text-center text-sm text-plum-400">No compliance events recorded yet.</div>
            @endforelse
        </div>

    </div>
</x-layouts.app>
