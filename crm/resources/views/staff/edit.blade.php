<x-layouts.app>
    <x-slot name="pageTitle">Edit — {{ $staff->full_name }}</x-slot>

    <div class="max-w-xl animate-fade-in"
        x-data="{
            role: '{{ old('role', $staff->role) }}',
            requiresGphc() {
                return ['superintendent_pharmacist', 'prescriber'].includes(this.role);
            }
        }">

        <div class="mb-5">
            <a href="{{ route('staff.show', $staff) }}" class="text-sm font-medium text-lilac-600 hover:text-lilac-800">← Back to {{ $staff->full_name }}</a>
        </div>

        @if($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 space-y-1">
                @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('staff.update', $staff) }}" class="space-y-5">
            @csrf
            @method('PUT')

            {{-- Personal details --}}
            <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm space-y-4">
                <h3 class="text-sm font-semibold text-plum-700">Personal Details</h3>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-plum-700">First name</label>
                        <input type="text" name="first_name" value="{{ old('first_name', $staff->first_name) }}" required
                            class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-plum-700">Last name</label>
                        <input type="text" name="last_name" value="{{ old('last_name', $staff->last_name) }}" required
                            class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-plum-700">Email address</label>
                    <input type="email" name="email" value="{{ old('email', $staff->email) }}" required
                        class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                </div>
            </div>

            {{-- Role & clinical --}}
            <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm space-y-4">
                <h3 class="text-sm font-semibold text-plum-700">Role & Permissions</h3>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-plum-700">Role</label>
                    <select name="role" x-model="role" required
                        class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                        @foreach($roles as $key => $label)
                            <option value="{{ $key }}" {{ old('role', $staff->role) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @if($staff->id === auth('staff')->id())
                        <p class="mt-1 text-xs text-amber-600">⚠ You are editing your own account. Role changes take effect on next login.</p>
                    @endif
                </div>

                <div x-show="requiresGphc()" x-cloak>
                    <label class="mb-1.5 block text-sm font-medium text-plum-700">
                        GPhC Registration Number <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="gphc_number"
                        value="{{ old('gphc_number', $staff->gphc_number) }}"
                        :required="requiresGphc()"
                        maxlength="7"
                        pattern="\d{7}"
                        class="block w-full rounded-xl border-plum-200 py-2.5 px-3 font-mono text-sm focus:border-lilac-500 focus:ring-lilac-500"
                        placeholder="1234567"
                    >
                </div>

                <div x-show="['superintendent_pharmacist', 'prescriber'].includes(role)" x-cloak>
                    <label class="mb-1.5 block text-sm font-medium text-plum-700">Max daily consultations <span class="text-plum-400 font-normal">(blank = no limit)</span></label>
                    <input type="number" name="max_daily_consultations"
                        value="{{ old('max_daily_consultations', $staff->max_daily_consultations) }}"
                        min="1" max="500"
                        class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                </div>
            </div>

            {{-- Out of office --}}
            <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm space-y-4"
                 x-data="{ ooo: {{ $staff->out_of_office ? 'true' : 'false' }} }">
                <h3 class="text-sm font-semibold text-plum-700">Availability</h3>

                <label class="flex items-center gap-3 cursor-pointer">
                    <button type="button"
                        @click="ooo = !ooo"
                        :class="ooo ? 'bg-amber-400' : 'bg-plum-200'"
                        class="relative inline-flex h-6 w-11 shrink-0 rounded-full transition-colors focus:outline-none">
                        <span :class="ooo ? 'translate-x-5' : 'translate-x-0.5'"
                            class="mt-0.5 inline-block size-5 transform rounded-full bg-white shadow transition-transform"></span>
                    </button>
                    <span class="text-sm text-plum-700">Out of office</span>
                    <input type="hidden" name="out_of_office" :value="ooo ? '1' : '0'">
                </label>

                <div x-show="ooo" x-cloak>
                    <label class="mb-1.5 block text-sm font-medium text-plum-700">Reassign consultations to</label>
                    <select name="out_of_office_reassign_to"
                        class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                        <option value="">— No reassignment —</option>
                        @foreach($prescribers as $p)
                            @if($p->id !== $staff->id)
                                <option value="{{ $p->id }}"
                                    {{ old('out_of_office_reassign_to', $staff->out_of_office_reassign_to) == $p->id ? 'selected' : '' }}>
                                    {{ $p->full_name }} ({{ $p->roleLabel() }})
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('staff.show', $staff) }}" class="rounded-xl border border-plum-200 px-4 py-2.5 text-sm text-plum-500 hover:bg-plum-50">Cancel</a>
                <button type="submit" class="rounded-xl bg-plum-800 px-5 py-2.5 text-sm font-semibold text-lilac-200 hover:bg-plum-900">
                    Save Changes
                </button>
            </div>

        </form>
    </div>
</x-layouts.app>
