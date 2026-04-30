<li>
    <a
        href="{{ $href }}"
        @class([
            'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
            'bg-plum-600 text-lilac-200 shadow-sm' => $active,
            'text-plum-300 hover:bg-plum-700/60 hover:text-lilac-300' => ! $active,
        ])
        @if($active) aria-current="page" @endif
    >
        {{-- Heroicon (outline) mapped from $icon name --}}
        @include('components.crm.icons.' . $icon, ['class' => 'size-5 shrink-0'])

        <span class="truncate">{{ $slot }}</span>
    </a>
</li>
