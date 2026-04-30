<x-layouts.app>
    <x-slot name="pageTitle">Signature Settings</x-slot>

    <div class="max-w-lg animate-fade-in" x-data="{ saved: false }">

        <div class="mb-5">
            <h2 class="text-xl font-semibold text-plum-800">Prescriber Signature</h2>
            <p class="mt-1 text-sm text-plum-400">
                Your signature is embedded automatically on every prescription PDF you sign. Draw it once and it applies to all future prescriptions.
            </p>
        </div>

        @if(session('info'))
            <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                {{ session('info') }}
            </div>
        @endif

        @if(session('success'))
            <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        {{-- Existing signature --}}
        @if($staff->signature_path)
        <div class="mb-5 rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-plum-700">Current Signature</h3>
                <p class="text-xs text-plum-400">Saved {{ $staff->signature_set_at?->format('d M Y') }}</p>
            </div>
            <div class="flex items-center justify-between">
                <img
                    src="{{ route('profile.signature.preview') }}"
                    alt="Your saved signature"
                    class="h-20 rounded-lg border border-plum-100 object-contain p-2"
                >
                <form method="POST" action="{{ route('profile.signature.destroy') }}"
                    onsubmit="return confirm('Remove your saved signature? You will need to draw a new one before signing prescriptions.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-xl border border-red-200 px-3 py-2 text-xs font-medium text-red-600 hover:bg-red-50">Remove</button>
                </form>
            </div>
        </div>
        @endif

        {{-- Canvas draw pad --}}
        <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm">
            <h3 class="mb-4 text-sm font-semibold text-plum-700">
                {{ $staff->signature_path ? 'Update Signature' : 'Draw Your Signature' }}
            </h3>

            <form method="POST" action="{{ route('profile.signature.save') }}" id="sig-form">
                @csrf
                <input type="hidden" name="signature" id="sig-data">

                <x-signature-canvas input-id="sig-data" height="180" />

                <div class="mt-4 flex justify-end">
                    <button
                        type="submit"
                        class="rounded-xl bg-plum-800 px-5 py-2.5 text-sm font-semibold text-lilac-200 hover:bg-plum-900"
                        onclick="if(!document.getElementById('sig-data').value){alert('Please draw your signature first.');return false;}"
                    >
                        Save Signature
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-4 rounded-xl border border-plum-100 bg-plum-50/60 px-4 py-3 text-xs text-plum-500">
            Your signature is stored securely in private storage and never publicly accessible. It is embedded into prescription PDFs as a PNG image at the point of signing. All signature saves and updates are recorded in the audit trail.
        </div>

    </div>
</x-layouts.app>
