<x-layouts.app>
    <x-slot name="pageTitle">{{ $patient->full_name }} — Messages</x-slot>
    <div class="flex flex-col h-[calc(100vh-8rem)] max-w-2xl animate-fade-in">
        <div class="mb-4 flex items-center gap-3">
            <a href="{{ route('messages.index') }}" class="text-sm font-medium text-lilac-600 hover:text-lilac-800">← Inbox</a>
            <a href="{{ route('patients.show', $patient) }}" class="text-sm text-plum-500 hover:text-plum-700">{{ $patient->full_name }} →</a>
        </div>

        {{-- Messages --}}
        <div class="flex-1 overflow-y-auto space-y-3 rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm mb-4">
            @forelse($messages as $msg)
                <div class="flex {{ $msg->isFromStaff() ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[75%] rounded-2xl px-4 py-2.5
                        {{ $msg->isFromStaff()
                            ? 'bg-plum-800 text-lilac-100'
                            : 'bg-plum-50 border border-plum-100 text-plum-800' }}
                        {{ $msg->internal_only ? 'opacity-60 border-dashed' : '' }}">
                        <p class="text-sm leading-relaxed">{{ $msg->body }}</p>
                        <div class="mt-1 flex items-center justify-between gap-3">
                            <p class="text-[10px] {{ $msg->isFromStaff() ? 'text-lilac-400' : 'text-plum-400' }}">
                                {{ $msg->senderName() }}
                                @if($msg->internal_only) · <em>internal</em> @endif
                            </p>
                            <p class="text-[10px] {{ $msg->isFromStaff() ? 'text-lilac-400' : 'text-plum-400' }}">{{ $msg->created_at->format('d M, H:i') }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center text-sm text-plum-400 py-8">No messages yet.</div>
            @endforelse
        </div>

        {{-- Reply form --}}
        <div class="rounded-2xl border border-plum-100 bg-white p-4 shadow-plum-sm">
            <form method="POST" action="{{ route('messages.send') }}" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <input type="hidden" name="patient_id" value="{{ $patient->id }}">
                <textarea name="body" rows="3" required placeholder="Type your message…"
                    class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500"></textarea>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <label class="flex items-center gap-1.5 text-xs text-plum-600 cursor-pointer">
                            <input type="checkbox" name="internal_only" value="1" class="size-3.5 rounded border-plum-300 text-lilac-500">
                            Internal only
                        </label>
                        <label class="flex items-center gap-1.5 text-xs text-plum-600 cursor-pointer">
                            <input type="file" name="attachment" class="hidden" id="attach-input" accept=".pdf,.jpg,.jpeg,.png">
                            <span @click="document.getElementById('attach-input').click()" class="cursor-pointer text-plum-400 hover:text-plum-700">📎 Attach</span>
                        </label>
                    </div>
                    <button type="submit" class="rounded-xl bg-plum-800 px-4 py-2 text-sm font-medium text-lilac-200 hover:bg-plum-900">Send</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
