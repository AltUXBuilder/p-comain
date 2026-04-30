<x-app-layout>
    <x-slot name="title">{{ $product->name }} Consultation</x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <livewire:consultation.consultation-form
            :product="$product"
            :draft-uuid="$draftUuid ?? null" />
    </div>

</x-app-layout>
