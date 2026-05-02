<x-layouts.app>
    <x-slot name="pageTitle">Messages</x-slot>
    <div class="space-y-5 animate-fade-in">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-plum-800">Message Inbox</h2>
            <a href="{{ route('messages.bulk') }}" class="rounded-xl border border-plum-200 px-4 py-2 text-sm font-medium text-plum-600 hover:bg-plum-50">Bulk Message</a>
        </div>
        <div class="overflow-hidden rounded-2xl border border-plum-100 bg-white shadow-plum-sm divide-y divide-plum-50">
            @forelse($threads as $thread)
                @php $msg = $latestMsgs[$thread->latest_message_id] ?? null; $unread = $unreadCounts[$thread->user_id] ?? 0; @endphp
                @if($msg)
                <a href="{{ route('messages.patient', $msg->patient) }}" class="flex items-start gap-4 px-5 py-4 hover:bg-plum-50/20 transition {{ $unread > 0 ? 'bg-lilac-50/30' : '' }}">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-plum-100 text-sm font-bold text-plum-700">
                        {{ strtoupper(substr($msg->patient?->first_name ?? '?', 0, 1) . substr($msg->patient?->last_name ?? '', 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-sm font-medium text-plum-800">{{ $msg->patient?->full_name ?? 'Unknown' }}</p>
                            <p class="shrink-0 text-xs text-plum-400">{{ $msg->created_at->diffForHumans() }}</p>
                        </div>
                        <p class="mt-0.5 truncate text-xs text-plum-500">{{ Str::limit($msg->body, 80) }}</p>
                    </div>
                    @if($unread > 0)
                        <span class="flex size-5 shrink-0 items-center justify-center rounded-full bg-lilac-500 text-[10px] font-bold text-white">{{ $unread }}</span>
                    @endif
                </a>
                @endif
            @empty
                <div class="px-5 py-12 text-center text-sm text-plum-400">No messages yet.</div>
            @endforelse
        </div>
        @if($threads->hasPages()) <div class="flex justify-center">{{ $threads->links() }}</div> @endif
    </div>
</x-layouts.app>
