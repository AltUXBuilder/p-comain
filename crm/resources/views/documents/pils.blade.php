<x-layouts.app>
    <x-slot name="pageTitle">PIL Library</x-slot>

    <div class="space-y-5 animate-fade-in" x-data="{ showUpload: false }">

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-plum-800">Patient Information Leaflets</h2>
                <p class="mt-0.5 text-xs text-plum-400">PILs are attached to products and made available to patients during checkout and in their account.</p>
            </div>
            <button @click="showUpload = true"
                class="flex items-center gap-2 rounded-xl bg-plum-800 px-4 py-2 text-sm font-medium text-lilac-200 hover:bg-plum-900">
                + Upload PIL
            </button>
        </div>

        {{-- PIL table --}}
        <div class="overflow-hidden rounded-2xl border border-plum-100 bg-white shadow-plum-sm">
            <table class="min-w-full divide-y divide-plum-100">
                <thead class="bg-plum-50/60">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">PIL Name</th>
                        <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 md:table-cell">Attached to Products</th>
                        <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 sm:table-cell">Uploaded</th>
                        <th class="px-4 py-3 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-plum-50">
                    @forelse($pils as $pil)
                        <tr class="hover:bg-plum-50/10" x-data="{ showAttach: false }">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-green-100">
                                        <svg class="size-3.5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-medium text-plum-800">{{ $pil->name }}</p>
                                </div>
                            </td>
                            <td class="hidden px-4 py-3 md:table-cell">
                                @php
                                    // Find products linked to this PIL by path
                                    $linkedProducts = \App\Models\Product::where('pil_path', $pil->path)->get();
                                @endphp
                                @if($linkedProducts->isNotEmpty())
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($linkedProducts as $prod)
                                            <span class="rounded-full bg-plum-100 px-2 py-0.5 text-[10px] font-medium text-plum-600">{{ $prod->name }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-plum-300 italic">Not attached to any product</span>
                                @endif
                            </td>
                            <td class="hidden px-4 py-3 text-xs text-plum-400 sm:table-cell">
                                {{ $pil->created_at->format('d M Y') }}
                                @if($pil->uploadedByStaff)
                                    <br>{{ $pil->uploadedByStaff->full_name }}
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('documents.view', $pil) }}" target="_blank"
                                        class="rounded-lg border border-plum-200 px-3 py-1.5 text-xs font-medium text-plum-600 hover:bg-plum-50">View</a>
                                    <button @click="showAttach = !showAttach"
                                        class="rounded-lg border border-plum-200 px-3 py-1.5 text-xs font-medium text-plum-600 hover:bg-plum-50">
                                        Attach to product
                                    </button>
                                    <form method="POST" action="{{ route('documents.destroy', $pil) }}"
                                        onsubmit="return confirm('Delete {{ $pil->name }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-red-200 px-2.5 py-1.5 text-xs text-red-500 hover:bg-red-50">✕</button>
                                    </form>
                                </div>

                                {{-- Inline attach form --}}
                                <div x-show="showAttach" x-cloak class="mt-2">
                                    <form method="POST" action="{{ route('documents.pils.attach', $pil) }}" class="flex gap-2">
                                        @csrf
                                        <select name="product_id" required
                                            class="flex-1 rounded-xl border-plum-200 py-1.5 px-2 text-xs focus:border-lilac-500 focus:ring-lilac-500">
                                            <option value="">Select product…</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="rounded-xl bg-plum-800 px-3 py-1.5 text-xs font-medium text-lilac-200 hover:bg-plum-900">Attach</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-12 text-center text-sm text-plum-400">
                                No PILs uploaded yet. Upload the first one to link it to a product.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pils->hasPages())
            <div class="flex justify-center">{{ $pils->links() }}</div>
        @endif

        {{-- Upload modal --}}
        <div x-show="showUpload" class="fixed inset-0 z-50 flex items-center justify-center bg-plum-950/50 p-4" x-cloak>
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-plum-lg">
                <h3 class="mb-4 text-base font-semibold text-plum-800">Upload PIL</h3>
                <form method="POST" action="{{ route('documents.pils.upload') }}"
                      enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="mb-1 block text-sm font-medium text-plum-700">PIL name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required
                            class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500"
                            placeholder="e.g. Mounjaro 2.5mg PIL">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-plum-700">Attach to product <span class="text-plum-400 font-normal">(optional)</span></label>
                        <select name="product_id"
                            class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                            <option value="">— Attach later —</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-plum-700">PDF file <span class="text-red-500">*</span></label>
                        <input type="file" name="file" required accept=".pdf"
                            class="block w-full text-sm text-plum-600 file:mr-3 file:rounded-lg file:border-0 file:bg-green-100 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-green-700 hover:file:bg-green-200">
                        <p class="mt-1 text-xs text-plum-400">PDF only · Max 20 MB</p>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="showUpload = false" class="rounded-xl border border-plum-200 px-4 py-2 text-sm text-plum-500">Cancel</button>
                        <button type="submit" class="rounded-xl bg-plum-800 px-4 py-2 text-sm font-medium text-lilac-200 hover:bg-plum-900">Upload PIL</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.app>
