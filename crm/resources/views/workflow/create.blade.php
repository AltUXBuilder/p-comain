<x-layouts.app>
    <x-slot name="pageTitle">{{ isset($rule) ? 'Edit Rule' : 'New Workflow Rule' }}</x-slot>

    <div class="max-w-2xl animate-fade-in"
        x-data="{
            conditions: {{ isset($rule) ? json_encode($rule->conditions ?? []) : '[]' }},
            actions: {{ isset($rule) ? json_encode($rule->actions ?? []) : '[]' }},
            addCondition() { this.conditions.push({ field: '', operator: 'equals', value: '' }); },
            removeCondition(i) { this.conditions.splice(i, 1); },
            addAction() { this.actions.push({ type: 'send_email', config: {} }); },
            removeAction(i) { this.actions.splice(i, 1); },
        }">

        <div class="mb-5">
            <a href="{{ route('workflow.index') }}" class="text-sm font-medium text-lilac-600 hover:text-lilac-800">← Workflow Rules</a>
        </div>

        <form method="POST" action="{{ isset($rule) ? route('workflow.update', $rule) : route('workflow.store') }}" class="space-y-5">
            @csrf
            @if(isset($rule)) @method('PUT') @endif

            @if($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
                </div>
            @endif

            <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm space-y-4">
                <h3 class="text-sm font-semibold text-plum-700">Rule Details</h3>

                <div>
                    <label class="mb-1 block text-xs font-medium text-plum-600">Rule Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $rule->name ?? '') }}" required
                        class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-plum-600">Description</label>
                    <textarea name="description" rows="2"
                        class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">{{ old('description', $rule->description ?? '') }}</textarea>
                </div>

                @if(! isset($rule) || ! $rule->is_system)
                <div>
                    <label class="mb-1 block text-xs font-medium text-plum-600">Trigger Event <span class="text-red-500">*</span></label>
                    <select name="trigger_event" required class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                        @foreach($triggers as $key => $label)
                            <option value="{{ $key }}" {{ old('trigger_event', $rule->trigger_event ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                @else
                    <div class="rounded-xl bg-plum-50 px-3 py-2">
                        <p class="text-xs text-plum-600">System rule — trigger cannot be changed: <strong>{{ $triggers[$rule->trigger_event] ?? $rule->trigger_event }}</strong></p>
                    </div>
                @endif

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="active" value="1" class="size-4 rounded border-plum-300 text-lilac-500"
                        {{ old('active', $rule->active ?? true) ? 'checked' : '' }}>
                    <label class="text-sm text-plum-700">Active (rule fires immediately)</label>
                </div>
            </div>

            {{-- Conditions --}}
            <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-plum-700">Conditions <span class="text-plum-400 font-normal text-xs">(all must be true)</span></h3>
                    <button type="button" @click="addCondition()" class="rounded-lg border border-plum-200 px-3 py-1 text-xs font-medium text-plum-600 hover:bg-plum-50">+ Add</button>
                </div>

                <template x-if="conditions.length === 0">
                    <p class="text-xs text-plum-400 italic">No conditions — rule fires for every matching trigger.</p>
                </template>

                <template x-for="(cond, i) in conditions" :key="i">
                    <div class="flex items-center gap-2">
                        <select :name="`conditions[${i}][field]`" x-model="cond.field"
                            class="flex-1 rounded-xl border-plum-200 py-1.5 px-2 text-xs focus:border-lilac-500 focus:ring-lilac-500">
                            <option value="">Select field…</option>
                            @foreach($fields as $k => $v)
                                <option value="{{ $k }}">{{ $v }}</option>
                            @endforeach
                        </select>
                        <select :name="`conditions[${i}][operator]`" x-model="cond.operator"
                            class="w-28 rounded-xl border-plum-200 py-1.5 px-2 text-xs focus:border-lilac-500 focus:ring-lilac-500">
                            @foreach($operators as $op)
                                <option value="{{ $op }}">{{ $op }}</option>
                            @endforeach
                        </select>
                        <input type="text" :name="`conditions[${i}][value]`" x-model="cond.value" placeholder="Value"
                            class="w-24 rounded-xl border-plum-200 py-1.5 px-2 text-xs focus:border-lilac-500 focus:ring-lilac-500">
                        <button type="button" @click="removeCondition(i)" class="text-red-400 hover:text-red-600">✕</button>
                    </div>
                </template>
            </div>

            {{-- Actions --}}
            <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-plum-700">Actions <span class="text-red-500">*</span></h3>
                    <button type="button" @click="addAction()" class="rounded-lg border border-plum-200 px-3 py-1 text-xs font-medium text-plum-600 hover:bg-plum-50">+ Add</button>
                </div>

                <template x-if="actions.length === 0">
                    <p class="text-xs text-red-500">At least one action is required.</p>
                </template>

                <template x-for="(act, i) in actions" :key="i">
                    <div class="rounded-xl border border-plum-100 bg-plum-50/50 p-3 space-y-2">
                        <div class="flex items-center gap-2">
                            <select :name="`actions[${i}][type]`" x-model="act.type"
                                class="flex-1 rounded-xl border-plum-200 py-1.5 px-2 text-xs focus:border-lilac-500 focus:ring-lilac-500">
                                @foreach($actionTypes as $k => $v)
                                    <option value="{{ $k }}">{{ $v }}</option>
                                @endforeach
                            </select>
                            <button type="button" @click="removeAction(i)" class="text-red-400 hover:text-red-600">✕</button>
                        </div>
                        {{-- send_email config --}}
                        <template x-if="act.type === 'send_email'">
                            <div>
                                <label class="mb-1 block text-[10px] font-medium text-plum-500">Email template</label>
                                <input type="text" :name="`actions[${i}][config][template]`"
                                    :value="act.config?.template ?? ''"
                                    placeholder="e.g. consultation_approved"
                                    class="block w-full rounded-lg border-plum-200 py-1 px-2 text-xs focus:border-lilac-500 focus:ring-lilac-500">
                            </div>
                        </template>
                        {{-- notify_staff config --}}
                        <template x-if="act.type === 'notify_staff' || act.type === 'create_staff_notification'">
                            <div class="space-y-2">
                                <div>
                                    <label class="mb-1 block text-[10px] font-medium text-plum-500">Roles (comma-separated)</label>
                                    <input type="text" :name="`actions[${i}][config][roles]`"
                                        :value="Array.isArray(act.config?.roles) ? act.config.roles.join(',') : (act.config?.roles ?? '')"
                                        placeholder="super_admin,superintendent_pharmacist"
                                        class="block w-full rounded-lg border-plum-200 py-1 px-2 text-xs focus:border-lilac-500 focus:ring-lilac-500">
                                </div>
                                <div>
                                    <label class="mb-1 block text-[10px] font-medium text-plum-500">Message</label>
                                    <input type="text" :name="`actions[${i}][config][message]`"
                                        :value="act.config?.message ?? ''"
                                        placeholder="Notification message…"
                                        class="block w-full rounded-lg border-plum-200 py-1 px-2 text-xs focus:border-lilac-500 focus:ring-lilac-500">
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('workflow.index') }}" class="rounded-xl border border-plum-200 px-4 py-2.5 text-sm text-plum-500 hover:bg-plum-50">Cancel</a>
                <button type="submit" class="rounded-xl bg-plum-800 px-5 py-2.5 text-sm font-semibold text-lilac-200 hover:bg-plum-900">
                    {{ isset($rule) ? 'Update Rule' : 'Create Rule' }}
                </button>
            </div>

        </form>
    </div>
</x-layouts.app>
