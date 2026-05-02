<x-layouts.app>
    <x-slot name="pageTitle">Workflow Automation</x-slot>

    <div class="space-y-5 animate-fade-in">

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-plum-800">Workflow Rules</h2>
                <p class="mt-0.5 text-xs text-plum-400">Rules fire automatically when their trigger event occurs. No code required.</p>
            </div>
            <a href="{{ route('workflow.create') }}" class="flex items-center gap-2 rounded-xl bg-plum-800 px-4 py-2 text-sm font-medium text-lilac-200 hover:bg-plum-900">
                + New Rule
            </a>
        </div>

        @foreach($rules as $triggerEvent => $ruleGroup)
            <div class="rounded-2xl border border-plum-100 bg-white shadow-plum-sm overflow-hidden">
                <div class="border-b border-plum-50 bg-plum-50/60 px-5 py-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-plum-500">
                        Trigger: {{ $triggers[$triggerEvent] ?? $triggerEvent }}
                    </p>
                </div>
                <div class="divide-y divide-plum-50">
                    @foreach($ruleGroup as $rule)
                        <div class="flex items-start justify-between gap-4 px-5 py-4">
                            <div class="flex items-start gap-3 min-w-0">
                                {{-- Active indicator --}}
                                <div class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full {{ $rule->active ? 'bg-green-100' : 'bg-gray-100' }}">
                                    <span class="size-2 rounded-full {{ $rule->active ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-plum-800">
                                        {{ $rule->name }}
                                        @if($rule->is_system)
                                            <span class="ml-1.5 rounded-full bg-lilac-100 px-2 py-0.5 text-[10px] font-semibold text-lilac-700">System</span>
                                        @endif
                                    </p>
                                    @if($rule->description)
                                        <p class="mt-0.5 text-xs text-plum-400">{{ $rule->description }}</p>
                                    @endif
                                    {{-- Actions summary --}}
                                    <div class="mt-1.5 flex flex-wrap gap-1">
                                        @foreach($rule->actions as $action)
                                            <span class="rounded-full bg-plum-100 px-2 py-0.5 text-[10px] font-medium text-plum-600">
                                                {{ \App\Models\WorkflowRule::ACTION_TYPES[$action['type']] ?? $action['type'] }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                {{-- Toggle --}}
                                <form method="POST" action="{{ route('workflow.toggle', $rule) }}">
                                    @csrf
                                    <button type="submit"
                                        class="rounded-lg border px-3 py-1.5 text-xs font-medium transition
                                            {{ $rule->active
                                                ? 'border-green-200 bg-green-50 text-green-700 hover:bg-green-100'
                                                : 'border-plum-200 text-plum-500 hover:bg-plum-50' }}">
                                        {{ $rule->active ? 'Enabled' : 'Disabled' }}
                                    </button>
                                </form>

                                <a href="{{ route('workflow.edit', $rule) }}" class="rounded-lg border border-plum-200 px-3 py-1.5 text-xs font-medium text-plum-600 hover:bg-plum-50">Edit</a>

                                @if(! $rule->is_system)
                                    <form method="POST" action="{{ route('workflow.destroy', $rule) }}"
                                        onsubmit="return confirm('Delete rule \'{{ $rule->name }}\'?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs text-red-600 hover:bg-red-50">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        @if($rules->isEmpty())
            <div class="rounded-2xl border border-plum-100 bg-white p-12 text-center text-sm text-plum-400">
                No workflow rules yet. <a href="{{ route('workflow.create') }}" class="text-lilac-600 hover:text-lilac-800">Create one →</a>
            </div>
        @endif

    </div>
</x-layouts.app>
