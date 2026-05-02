<x-layouts.app>
    <x-slot name="pageTitle">Regulatory Vault</x-slot>

    <div class="space-y-5 animate-fade-in" x-data="{ showUpload: false }">

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-plum-800">Regulatory Vault</h2>
                <p class="mt-0.5 text-xs text-plum-400">SOPs, GPhC documents and MHRA documentation — accessible to clinical leads only</p>
            </div>
            <button @click="showUpload = true"
                class="flex items-center gap-2 rounded-xl bg-plum-800 px-4 py-2 text-sm font-medium text-lilac-200 hover:bg-plum-900">
                + Upload Document
            </button>
        </div>

        {{-- Type filter tabs --}}
        <div class="flex gap-2">
            <a href="{{ route('documents.vault.index') }}"
                @class(['rounded-xl px-4 py-2 text-sm font-medium border transition',
                    'bg-plum-800 text-lilac-200 border-plum-800' => ! request('type'),
                    'border-plum-200 text-plum-600 hover:bg-plum-50' => request('type')])>
                All
            </a>
            @foreach($types as $key => $label)
                <a href="{{ route('documents.vault.index', ['type' => $key]) }}"
                    @class(['rounded-xl px-4 py-2 text-sm font-medium border transition shrink-0',
                        'bg-plum-800 text-lilac-200 border-plum-800' => request('type') === $key,
                        'border-plum-200 text-plum-600 hover:bg-plum-50' => request('type') !== $key])>
                    {{ $label }}
                </a>
            @endforeach
        </div>

        {{-- Search --}}
        <form method="GET" action="{{ route('documents.vault.index') }}" class="flex gap-3">
            @if(request('type')) <input type="hidden" name="type" value="{{ request('type') }}"> @endif
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Search documents…"
                class="flex-1 rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
            <button type="submit" class="rounded-xl bg-plum-800 px-4 py-2 text-sm font-medium text-lilac-200 hover:bg-plum-900">Search</button>
        </form>

        {{-- Documents grid --}}
        @if($docs->isEmpty())
            <div class="rounded-2xl border border-plum-100 bg-white p-12 text-center text-sm text-plum-400">
                No documents in the vault yet. Upload your first SOPs and GPhC documents.
            </div>
        @else
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($docs as $doc)
                    <div class="flex flex-col rounded-2xl border border-plum-100 bg-white p-4 shadow-plum-sm">
                        <div class="mb-3 flex items-start gap-3">
                            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl {{ $doc->typeColour() }}">
                                <svg class="size-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-plum-800">{{ $doc->name }}</p>
                                <span class="mt-0.5 inline-block rounded-full {{ $doc->typeColour() }} px-2 py-0.5 text-[10px] font-semibold">
                                    {{ $doc->typeLabel() }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-auto border-t border-plum-50 pt-3">
                            <p class="mb-2 text-xs text-plum-400">
                                {{ $doc->sizeFormatted() }} ·
                                Uploaded {{ $doc->created_at->format('d M Y') }}
                                @if($doc->uploadedByStaff)
                                    by {{ $doc->uploadedByStaff->full_name }}
                                @endif
                            </p>
                            <div class="flex gap-2">
                                <a href="{{ route('documents.view', $doc) }}" target="_blank"
                                    class="flex-1 rounded-lg border border-plum-200 py-1.5 text-center text-xs font-medium text-plum-600 hover:bg-plum-50">
                                    View
                                </a>
                                <a href="{{ route('documents.download', $doc) }}"
                                    class="flex-1 rounded-lg border border-plum-200 py-1.5 text-center text-xs font-medium text-plum-600 hover:bg-plum-50">
                                    Download
                                </a>
                                <form method="POST" action="{{ route('documents.destroy', $doc) }}"
                                    onsubmit="return confirm('Delete {{ $doc->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="rounded-lg border border-red-200 px-2.5 py-1.5 text-xs text-red-500 hover:bg-red-50">✕</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($docs->hasPages())
                <div class="flex justify-center">{{ $docs->links() }}</div>
            @endif
        @endif

        {{-- Upload modal --}}
        <div x-show="showUpload" class="fixed inset-0 z-50 flex items-center justify-center bg-plum-950/50 p-4" x-cloak>
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-plum-lg">
                <h3 class="mb-4 text-base font-semibold text-plum-800">Upload to Regulatory Vault</h3>
                <form method="POST" action="{{ route('documents.vault.upload') }}"
                      enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="mb-1 block text-sm font-medium text-plum-700">Document name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required
                            class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500"
                            placeholder="e.g. Dispensing SOP v3.2">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-plum-700">Document type</label>
                        <select name="type" required
                            class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                            @foreach($types as $k => $v)
                                <option value="{{ $k }}" {{ request('type') === $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-plum-700">File <span class="text-red-500">*</span></label>
                        <input type="file" name="file" required accept=".pdf,.doc,.docx,.xls,.xlsx"
                            class="block w-full text-sm text-plum-600 file:mr-3 file:rounded-lg file:border-0 file:bg-plum-100 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-plum-700 hover:file:bg-plum-200">
                        <p class="mt-1 text-xs text-plum-400">PDF, DOC, DOCX, XLS, XLSX · Max 50 MB</p>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="showUpload = false" class="rounded-xl border border-plum-200 px-4 py-2 text-sm text-plum-500">Cancel</button>
                        <button type="submit" class="rounded-xl bg-plum-800 px-4 py-2 text-sm font-medium text-lilac-200 hover:bg-plum-900">Upload</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.app>
