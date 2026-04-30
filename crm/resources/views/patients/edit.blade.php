<x-layouts.app>
    <x-slot name="pageTitle">Edit Patient — {{ $patient->full_name }}</x-slot>

    <div class="max-w-xl animate-fade-in">

        <div class="mb-5">
            <a href="{{ route('patients.show', $patient) }}" class="text-sm font-medium text-lilac-600 hover:text-lilac-800">← Back to record</a>
        </div>

        <div class="rounded-2xl border border-plum-100 bg-white p-6 shadow-plum-sm">
            <h2 class="mb-5 text-lg font-semibold text-plum-800">Edit Demographics</h2>

            @if($errors->any())
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 space-y-1">
                    @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('patients.update', $patient) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-plum-700">First name</label>
                        <input type="text" name="first_name" value="{{ old('first_name', $patient->first_name) }}" required
                            class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-plum-700">Last name</label>
                        <input type="text" name="last_name" value="{{ old('last_name', $patient->last_name) }}" required
                            class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-plum-700">Email</label>
                    <input type="email" name="email" value="{{ old('email', $patient->email) }}" required
                        class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-plum-700">Date of birth</label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $patient->date_of_birth?->format('Y-m-d')) }}"
                            class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-plum-700">Mobile</label>
                        <input type="text" name="mobile" value="{{ old('mobile', $patient->mobile) }}"
                            class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-plum-700">Address line 1</label>
                    <input type="text" name="address_line_1" value="{{ old('address_line_1', $patient->address_line_1) }}"
                        class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-plum-700">Address line 2</label>
                    <input type="text" name="address_line_2" value="{{ old('address_line_2', $patient->address_line_2) }}"
                        class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-plum-700">City</label>
                        <input type="text" name="city" value="{{ old('city', $patient->city) }}"
                            class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-plum-700">County</label>
                        <input type="text" name="county" value="{{ old('county', $patient->county) }}"
                            class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-plum-700">Postcode</label>
                        <input type="text" name="postcode" value="{{ old('postcode', $patient->postcode) }}"
                            class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-plum-700">GP Surgery</label>
                    <select name="gp_surgery_id" class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                        <option value="">— Not linked —</option>
                        @foreach($surgeries as $surgery)
                            <option value="{{ $surgery->id }}" {{ old('gp_surgery_id', $patient->gp_surgery_id) == $surgery->id ? 'selected' : '' }}>
                                {{ $surgery->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <label class="flex items-center gap-2 text-sm text-plum-700">
                    <input type="checkbox" name="identity_verified" value="1"
                        class="size-4 rounded border-plum-300 text-lilac-500"
                        {{ old('identity_verified', $patient->identity_verified) ? 'checked' : '' }}>
                    Identity verified
                </label>

                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('patients.show', $patient) }}" class="rounded-xl border border-plum-200 px-4 py-2.5 text-sm text-plum-500 hover:bg-plum-50">Cancel</a>
                    <button type="submit" class="rounded-xl bg-plum-800 px-5 py-2.5 text-sm font-semibold text-lilac-200 hover:bg-plum-900">Save Changes</button>
                </div>

            </form>
        </div>
    </div>
</x-layouts.app>
