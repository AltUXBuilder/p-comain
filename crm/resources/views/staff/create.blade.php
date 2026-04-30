<x-layouts.app>
    <x-slot name="pageTitle">Add Staff Member</x-slot>

    <div class="max-w-xl animate-fade-in">

        <div class="mb-5">
            <a href="{{ route('staff.index') }}" class="text-sm font-medium text-lilac-600 hover:text-lilac-800">← Back to staff</a>
        </div>

        <div class="rounded-2xl border border-plum-100 bg-white p-6 shadow-plum-sm">
            <h2 class="mb-5 text-lg font-semibold text-plum-800">New Staff Account</h2>

            @if($errors->any())
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 space-y-1">
                    @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('staff.store') }}"
                class="space-y-5"
                x-data="{
                    role: '{{ old('role') }}',
                    requiresGphc() {
                        return ['superintendent_pharmacist', 'prescriber'].includes(this.role);
                    }
                }"
            >
                @csrf

                {{-- Name --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-plum-700">First name</label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}" required
                            class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm text-plum-800 focus:border-lilac-500 focus:ring-lilac-500">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-plum-700">Last name</label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}" required
                            class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm text-plum-800 focus:border-lilac-500 focus:ring-lilac-500">
                    </div>
                </div>

                {{-- Email --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-plum-700">Email address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm text-plum-800 focus:border-lilac-500 focus:ring-lilac-500"
                        placeholder="staff@prescribeandco.co.uk">
                </div>

                {{-- Role --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-plum-700">Role</label>
                    <select
                        name="role"
                        x-model="role"
                        required
                        class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm text-plum-800 focus:border-lilac-500 focus:ring-lilac-500"
                    >
                        <option value="">Select a role…</option>
                        @foreach($roles as $key => $label)
                            <option value="{{ $key }}" {{ old('role') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- GPhC number — required for clinical roles --}}
                <div x-show="requiresGphc()" x-cloak>
                    <label class="mb-1.5 block text-sm font-medium text-plum-700">
                        GPhC Registration Number
                        <span class="ml-1 text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="gphc_number"
                        value="{{ old('gphc_number') }}"
                        :required="requiresGphc()"
                        maxlength="7"
                        pattern="\d{7}"
                        class="block w-full rounded-xl border-plum-200 py-2.5 px-3 font-mono text-sm text-plum-800 focus:border-lilac-500 focus:ring-lilac-500"
                        placeholder="1234567"
                    >
                    <p class="mt-1 text-xs text-plum-400">7-digit numeric GPhC registration number. Mandatory for clinical roles.</p>
                </div>

                {{-- Max consultations --}}
                <div x-show="['superintendent_pharmacist', 'prescriber'].includes(role)" x-cloak>
                    <label class="mb-1.5 block text-sm font-medium text-plum-700">Max daily consultations <span class="text-plum-400 font-normal">(optional)</span></label>
                    <input type="number" name="max_daily_consultations" value="{{ old('max_daily_consultations') }}"
                        min="1" max="500"
                        class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm text-plum-800 focus:border-lilac-500 focus:ring-lilac-500"
                        placeholder="Leave blank for no limit">
                </div>

                {{-- Info box --}}
                <div class="rounded-xl border border-plum-100 bg-plum-50/60 px-4 py-3 text-xs text-plum-500">
                    A welcome email will be dispatched immediately. The staff member will set their own password and enrol in two-factor authentication before their account activates.
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('staff.index') }}" class="rounded-xl border border-plum-200 px-4 py-2.5 text-sm text-plum-500 hover:bg-plum-50">Cancel</a>
                    <button type="submit" class="rounded-xl bg-plum-800 px-5 py-2.5 text-sm font-semibold text-lilac-200 hover:bg-plum-900">
                        Create Account &amp; Send Welcome Email
                    </button>
                </div>

            </form>
        </div>
    </div>
</x-layouts.app>
