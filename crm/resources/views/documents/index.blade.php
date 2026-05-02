<x-layouts.app>
    <x-slot name="pageTitle">Documents</x-slot>

    <div class="space-y-5 animate-fade-in">

        <div>
            <h2 class="text-xl font-semibold text-plum-800">Document Management</h2>
            <p class="mt-0.5 text-sm text-plum-400">Central access to all document types across the platform.</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

            {{-- Prescription PDF Archive --}}
            <a href="{{ route('documents.prescriptions') }}"
               class="group rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm transition hover:shadow-plum hover:-translate-y-0.5">
                <div class="mb-3 flex size-10 items-center justify-center rounded-xl bg-plum-100">
                    <svg class="size-5 text-plum-700" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <p class="font-semibold text-plum-800">Prescription Archive</p>
                <p class="mt-0.5 text-xs text-plum-400">All signed prescription PDFs</p>
                <p class="mt-3 text-xs font-medium text-lilac-600 group-hover:text-lilac-800">
                    {{ \App\Models\Document::where('type', \App\Models\Document::TYPE_PRESCRIPTION_PDF)->count() }} documents →
                </p>
            </a>

            {{-- Patient Documents --}}
            <a href="{{ route('patients.index') }}"
               class="group rounded-2xl border border-blue-100 bg-blue-50/40 p-5 shadow-plum-sm transition hover:shadow-plum hover:-translate-y-0.5">
                <div class="mb-3 flex size-10 items-center justify-center rounded-xl bg-blue-100">
                    <svg class="size-5 text-blue-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z"/>
                    </svg>
                </div>
                <p class="font-semibold text-plum-800">Patient Documents</p>
                <p class="mt-0.5 text-xs text-plum-400">Uploads, identity docs, GP letters</p>
                <p class="mt-3 text-xs font-medium text-lilac-600 group-hover:text-lilac-800">
                    {{ \App\Models\Document::whereIn('type', [\App\Models\Document::TYPE_PATIENT_UPLOAD, \App\Models\Document::TYPE_IDENTITY_DOC, \App\Models\Document::TYPE_GP_LETTER])->count() }} documents →
                </p>
            </a>

            {{-- Regulatory Vault --}}
            <a href="{{ route('documents.vault.index') }}"
               class="group rounded-2xl border border-amber-100 bg-amber-50/40 p-5 shadow-plum-sm transition hover:shadow-plum hover:-translate-y-0.5">
                <div class="mb-3 flex size-10 items-center justify-center rounded-xl bg-amber-100">
                    <svg class="size-5 text-amber-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                    </svg>
                </div>
                <p class="font-semibold text-plum-800">Regulatory Vault</p>
                <p class="mt-0.5 text-xs text-plum-400">SOPs, GPhC and MHRA documents</p>
                <p class="mt-3 text-xs font-medium text-lilac-600 group-hover:text-lilac-800">
                    {{ \App\Models\Document::regulatoryVault()->count() }} documents →
                </p>
            </a>

            {{-- PIL Library --}}
            <a href="{{ route('documents.pils.index') }}"
               class="group rounded-2xl border border-green-100 bg-green-50/40 p-5 shadow-plum-sm transition hover:shadow-plum hover:-translate-y-0.5">
                <div class="mb-3 flex size-10 items-center justify-center rounded-xl bg-green-100">
                    <svg class="size-5 text-green-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                    </svg>
                </div>
                <p class="font-semibold text-plum-800">PIL Library</p>
                <p class="mt-0.5 text-xs text-plum-400">Patient Information Leaflets</p>
                <p class="mt-3 text-xs font-medium text-lilac-600 group-hover:text-lilac-800">
                    {{ \App\Models\Document::pils()->count() }} PILs →
                </p>
            </a>

        </div>

        {{-- Recent uploads --}}
        <div class="rounded-2xl border border-plum-100 bg-white shadow-plum-sm">
            <div class="border-b border-plum-50 px-5 py-4">
                <h3 class="text-sm font-semibold text-plum-700">Recent Uploads</h3>
            </div>
            @php
                $recent = \App\Models\Document::with(['patient', 'uploadedByStaff'])
                    ->orderByDesc('created_at')
                    ->limit(8)
                    ->get();
            @endphp
            @forelse($recent as $doc)
                <div class="flex items-center gap-4 border-b border-plum-50 px-5 py-3 last:border-0">
                    <span class="rounded-full {{ $doc->typeColour() }} px-2 py-0.5 text-[10px] font-semibold shrink-0">
                        {{ $doc->typeLabel() }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-plum-800">{{ $doc->name }}</p>
                        @if($doc->patient)
                            <p class="text-xs text-plum-400">{{ $doc->patient->full_name }}</p>
                        @endif
                    </div>
                    <div class="flex shrink-0 items-center gap-3">
                        <p class="text-xs text-plum-400">{{ $doc->created_at->diffForHumans() }}</p>
                        <a href="{{ route('documents.view', $doc) }}" target="_blank"
                           class="text-xs font-medium text-lilac-600 hover:text-lilac-800">View →</a>
                    </div>
                </div>
            @empty
                <div class="px-5 py-8 text-center text-sm text-plum-400">No documents uploaded yet.</div>
            @endforelse
        </div>

    </div>
</x-layouts.app>
