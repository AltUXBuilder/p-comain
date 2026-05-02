<x-layouts.app>
    <x-slot name="pageTitle">Documents — {{ $patient->full_name }}</x-slot>

    <div class="space-y-5 animate-fade-in" x-data="{ showUpload: false }">

        <div class="flex items-center justify-between">
            <a href="{{ route('patients.show', $patient) }}" class="text-sm font-medium text-lilac-600 hover:text-lilac-800">← Patient record</a>
            <button @click="showUpload = true"
                class="flex items-center gap-2 rounded-xl bg-plum-800 px-4 py-2 text-sm font-medium text-lilac-200 hover:bg-plum-900">
                + Upload Document
            </button>
        </div>

        {{-- Patient header --}}
        <div class="rounded-2xl border border-plum-100 bg-white px-5 py-4 shadow-plum-sm">
            <p class="font-semibold text-plum-800">{{ $patient->full_name }}</p>
            <p class="text-xs text-plum-400">{{ $docs->flatten()->count() }} document(s) on file</p>
        </div>

        {{-- Documents grouped by type --}}
        @if($docs->isEmpty())
            <div class="rounded-2xl border border-plum-100 bg-white p-12 text-center text-sm text-plum-400">
                No documents uploaded for this patient yet.
            </div>
        @else
            @foreach(\App\Models\Document::TYPES_GROUPED['Patient Documents'] as $typeKey)
                @if($docs->has($typeKey))
                    <div class="rounded-2xl border border-plum-100 bg-white shadow-plum-sm overflow-hidden">
                        <div class="border-b border-plum-50 bg-plum-50/60 px-5 py-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-plum-500">
                                {{ \App\Models\Document::TYPES[$typeKey] }}
                                <span class="ml-2 text-plum-400">({{ $docs->get($typeKey)->count() }})</span>
                            </p>
                        </div>
                        <div class="divide-y divide-plum-50">
                            @foreach($docs->get($typeKey) as $doc)
                                <div class="flex items-center gap-4 px-5 py-3">
                                    {{-- Icon --}}
                                    <div class="flex size-9 shrink-0 items-center justify-center rounded-xl {{ $doc->typeColour() }}">
                                        @if($doc->isPdf())
                                            <svg class="size-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg>
                                        @elseif($doc->isImage())
                                            <svg class="size-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/></svg>
                                        @else
                                            <svg class="size-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg>
                                        @endif
                                    </div>

                                    {{-- Info --}}
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-medium text-plum-800">{{ $doc->name }}</p>
                                        <p class="text-xs text-plum-400">
                                            {{ $doc->sizeFormatted() }}
                                            · Uploaded {{ $doc->created_at->format('d M Y') }}
                                            @if($doc->uploadedByStaff)
                                                by {{ $doc->uploadedByStaff->full_name }}
                                            @endif
                                        </p>
                                    </div>

                                    {{-- Actions --}}
                                    <div class="flex shrink-0 gap-2">
                                        @if($doc->isPdf() || $doc->isImage())
                                            <a href="{{ route('documents.view', $doc) }}" target="_blank"
                                                class="rounded-lg border border-plum-200 px-3 py-1.5 text-xs font-medium text-plum-600 hover:bg-plum-50">
                                                View
                                            </a>
                                        @endif
                                        <a href="{{ route('documents.download', $doc) }}"
                                            class="rounded-lg border border-plum-200 px-3 py-1.5 text-xs font-medium text-plum-600 hover:bg-plum-50">
                                            Download
                                        </a>
                                        @if($doc->type !== \App\Models\Document::TYPE_PRESCRIPTION_PDF)
                                            <form method="POST" action="{{ route('documents.destroy', $doc) }}"
                                                onsubmit="return confirm('Delete {{ $doc->name }}?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-500 hover:bg-red-50">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        @endif

        {{-- Upload modal --}}
        <div x-show="showUpload" class="fixed inset-0 z-50 flex items-center justify-center bg-plum-950/50 p-4" x-cloak>
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-plum-lg">
                <h3 class="mb-4 text-base font-semibold text-plum-800">Upload Document</h3>
                <form method="POST" action="{{ route('documents.patient.upload', $patient) }}"
                      enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="mb-1 block text-sm font-medium text-plum-700">Document type</label>
                        <select name="type" required
                            class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                            <option value="{{ \App\Models\Document::TYPE_PATIENT_UPLOAD }}">Patient Upload (general)</option>
                            <option value="{{ \App\Models\Document::TYPE_IDENTITY_DOC }}">Identity Document</option>
                            <option value="{{ \App\Models\Document::TYPE_GP_LETTER }}">GP Letter</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-plum-700">Display name <span class="text-plum-400 font-normal">(optional)</span></label>
                        <input type="text" name="name"
                            class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500"
                            placeholder="Leave blank to use filename">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-plum-700">File <span class="text-red-500">*</span></label>
                        <input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                            class="block w-full text-sm text-plum-600 file:mr-3 file:rounded-lg file:border-0 file:bg-plum-100 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-plum-700 hover:file:bg-plum-200">
                        <p class="mt-1 text-xs text-plum-400">PDF, JPG, PNG, DOC, DOCX · Max 20 MB · Stored in private encrypted storage</p>
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
