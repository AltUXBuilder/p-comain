<x-layouts.app>
    <x-slot name="pageTitle">Messages — {{ $consultation->patient?->full_name }}</x-slot>

    <div class="flex flex-col h-[calc(100vh-8rem)] max-w-2xl animate-fade-in">

        {{-- Breadcrumb --}}
        <div class="mb-4 flex items-center gap-3 flex-wrap">
            <a href="{{ route('messages.index') }}" class="text-sm font-medium text-lilac-600 hover:text-lilac-800">← Inbox</a>
            <span class="text-plum-300">·</span>
            <a href="{{ route('consultations.show', $consultation) }}" class="text-sm text-plum-500 hover:text-plum-700">
                Consultation #{{ $consultation->id }} — {{ $consultation->product?->name }}
            </a>
            <span class="text-plum-300">·</span>
            <a href="{{ route('patients.show', $consultation->patient) }}" class="text-sm text-plum-500 hover:text-plum-700">
                {{ $consultation->patient?->full_name }}
            </a>
        </div>

        {{-- Consultation context strip --}}
        <div class="mb-3 flex items-center gap-3 rounded-xl border border-plum-100 bg-plum-50/60 px-4 py-2.5">
            <div class="flex-1 min-w-0">
                <p class="text-xs font-medium text-plum-700">{{ $consultation->product?->name }}</p>
                <p class="text-xs text-plum-400">Consultation #{{ $consultation->id }} · Status:
                    <span class="font-medium {{ match($consultation->status) {
                        'approved' => 'text-green-600',
                        'rejected' => 'text-red-600',
                        'flagged'  => 'text-amber-600',
                        default    => 'text-plum-600',
                    } }}">{{ ucfirst(str_replace('_', ' ', $consultation->status)) }}</span>
                </p>
            </div>
            <a href="{{ route('consultations.show', $consultation) }}" class="shrink-0 rounded-lg border border-plum-200 px-3 py-1 text-xs font-medium text-plum-600 hover:bg-plum-50">
                View consultation →
            </a>
        </div>

        {{-- Messages --}}
        <div class="flex-1 overflow-y-auto space-y-3 rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm mb-4" id="messages-container">
            @forelse($messages as $msg)
                <div class="flex {{ $msg->isFromStaff() ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[75%] rounded-2xl px-4 py-2.5
                        {{ $msg->isFromStaff()
                            ? 'bg-plum-800 text-lilac-100'
                            : 'bg-plum-50 border border-plum-100 text-plum-800' }}
                        {{ $msg->internal_only ? 'ring-2 ring-amber-300/40' : '' }}">

                        <p class="text-sm leading-relaxed whitespace-pre-wrap">{{ $msg->body }}</p>

                        @if($msg->attachment_path)
                            <a href="#" class="mt-1.5 flex items-center gap-1.5 text-xs font-medium
                                {{ $msg->isFromStaff() ? 'text-lilac-300 hover:text-lilac-200' : 'text-lilac-600 hover:text-lilac-800' }}">
                                📎 Attachment
                            </a>
                        @endif

                        <div class="mt-1 flex items-center justify-between gap-3">
                            <p class="text-[10px] {{ $msg->isFromStaff() ? 'text-lilac-400' : 'text-plum-400' }}">
                                {{ $msg->senderName() }}
                                @if($msg->internal_only)
                                    <span class="italic"> · internal only</span>
                                @endif
                            </p>
                            <p class="text-[10px] {{ $msg->isFromStaff() ? 'text-lilac-400' : 'text-plum-400' }}">
                                {{ $msg->created_at->format('d M, H:i') }}
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="flex h-full items-center justify-center py-8 text-center">
                    <p class="text-sm text-plum-400">No messages in this consultation thread yet.</p>
                </div>
            @endforelse
        </div>

        {{-- Reply form --}}
        <div class="rounded-2xl border border-plum-100 bg-white p-4 shadow-plum-sm">
            <form method="POST" action="{{ route('messages.send') }}" enctype="multipart/form-data"
                  class="space-y-3" x-data="{ internal: false }">
                @csrf
                <input type="hidden" name="patient_id" value="{{ $consultation->patient_id }}">
                <input type="hidden" name="consultation_id" value="{{ $consultation->id }}">
                <input type="hidden" name="internal_only" :value="internal ? '1' : '0'">

                <textarea
                    name="body"
                    rows="3"
                    required
                    placeholder="Type a message…"
                    :class="internal ? 'border-amber-300 bg-amber-50/30' : ''"
                    class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500 transition"
                ></textarea>

                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        {{-- Internal toggle --}}
                        <label class="flex cursor-pointer items-center gap-2 text-xs text-plum-600 select-none">
                            <button type="button"
                                @click="internal = !internal"
                                :class="internal ? 'bg-amber-400' : 'bg-plum-200'"
                                class="relative inline-flex h-5 w-9 shrink-0 rounded-full transition-colors focus:outline-none">
                                <span :class="internal ? 'translate-x-4' : 'translate-x-0.5'"
                                    class="mt-0.5 inline-block size-4 transform rounded-full bg-white shadow transition-transform"></span>
                            </button>
                            <span x-text="internal ? 'Internal only (staff)' : 'Visible to patient'"></span>
                        </label>

                        {{-- Attachment --}}
                        <label class="cursor-pointer text-xs text-plum-400 hover:text-plum-700">
                            📎 Attach file
                            <input type="file" name="attachment" class="hidden" accept=".pdf,.jpg,.jpeg,.png">
                        </label>
                    </div>

                    <button
                        type="submit"
                        class="flex items-center gap-2 rounded-xl bg-plum-800 px-4 py-2 text-sm font-medium text-lilac-200 hover:bg-plum-900"
                    >
                        Send
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12zm0 0h7.5"/>
                        </svg>
                    </button>
                </div>
            </form>
        </div>

    </div>

    @push('scripts')
    <script>
        // Scroll to bottom of messages on load
        document.addEventListener('DOMContentLoaded', function() {
            const c = document.getElementById('messages-container');
            if (c) c.scrollTop = c.scrollHeight;
        });
    </script>
    @endpush

</x-layouts.app>
