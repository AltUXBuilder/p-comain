<x-patient-layout>
    <x-slot name="title">Dashboard</x-slot>
    <x-slot name="pageTitle">Welcome back, {{ auth()->user()->first_name }}</x-slot>

    {{-- ── Pending consultation alerts ─────────────────────────────────────── --}}
    @foreach ($pendingConsultations as $consultation)
        <div class="alert-warning mb-4 flex items-start gap-3">
            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            <div>
                <p class="font-medium text-sm">{{ $consultation->product->name }} — consultation under review</p>
                <p class="text-xs mt-0.5 opacity-80">Submitted {{ $consultation->submitted_at?->diffForHumans() }}. We'll email you once approved.</p>
            </div>
        </div>
    @endforeach

    {{-- ── Unread messages banner ───────────────────────────────────────────── --}}
    @if ($unreadMessages > 0)
        <a href="{{ route('patient.messages.index') }}"
           class="block alert-info mb-6 hover:bg-lilac-100 transition-colors no-underline">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/></svg>
            <span class="text-sm font-medium">You have {{ $unreadMessages }} unread {{ Str::plural('message', $unreadMessages) }} from our team.</span>
        </a>
    @endif

    {{-- ── Stat cards ───────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @foreach ([
            ['label' => 'Active treatments',    'value' => $subscriptions->count(),                          'route' => 'patient.subscriptions.index', 'color' => 'bg-plum-800 text-lilac-400'],
            ['label' => 'Consultations',        'value' => auth()->user()->consultations()->count(),           'route' => 'patient.consultations.index', 'color' => 'bg-lilac-100 text-plum-800'],
            ['label' => 'Prescriptions',        'value' => auth()->user()->prescriptions()->count(),           'route' => 'patient.prescriptions.index', 'color' => 'bg-lilac-100 text-plum-800'],
            ['label' => 'Orders placed',        'value' => auth()->user()->orders()->count(),                  'route' => 'patient.orders.index',        'color' => 'bg-lilac-100 text-plum-800'],
        ] as $stat)
            <a href="{{ route($stat['route']) }}"
               class="card p-5 hover:shadow-plum transition-shadow group {{ $stat['color'] }}">
                <p class="text-3xl font-display font-bold mb-1 group-hover:scale-105 transition-transform inline-block">{{ $stat['value'] }}</p>
                <p class="text-xs font-medium opacity-80">{{ $stat['label'] }}</p>
            </a>
        @endforeach
    </div>

    <div class="grid lg:grid-cols-2 gap-6">

        {{-- ── Active subscriptions ─────────────────────────────────────────── --}}
        <div class="card">
            <div class="px-6 py-4 border-b border-plum-100 flex items-center justify-between">
                <h2 class="font-display text-lg font-bold text-plum-800">Active treatments</h2>
                <a href="{{ route('patient.subscriptions.index') }}" class="text-xs text-plum-500 hover:text-plum-800">View all</a>
            </div>
            <div class="divide-y divide-plum-50">
                @forelse ($subscriptions as $sub)
                    <div class="px-6 py-4 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium text-plum-800">{{ $sub->product->name ?? $sub->type }}</p>
                            <p class="text-xs text-plum-400 mt-0.5 capitalize">{{ $sub->stripe_status }} · {{ $sub->type }}</p>
                        </div>
                        <span class="badge-green shrink-0">Active</span>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center">
                        <p class="text-sm text-plum-400 mb-3">No active treatments yet.</p>
                        <a href="{{ route('treatments.index') }}" class="btn-primary btn-sm text-xs">Browse treatments</a>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ── Active order tracker ─────────────────────────────────────────── --}}
        <div class="card">
            <div class="px-6 py-4 border-b border-plum-100 flex items-center justify-between">
                <h2 class="font-display text-lg font-bold text-plum-800">Latest order</h2>
                <a href="{{ route('patient.orders.index') }}" class="text-xs text-plum-500 hover:text-plum-800">View all</a>
            </div>
            @if ($activeOrder)
                <div class="px-6 py-5">
                    <livewire:patient.order-tracker :order="$activeOrder" />
                </div>
            @else
                @php $lastOrder = auth()->user()->orders()->latest()->first(); @endphp
                @if ($lastOrder)
                    <div class="px-6 py-5">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-sm font-medium text-plum-800">{{ $lastOrder->order_number }}</p>
                            <span class="badge-lilac capitalize">{{ str_replace('_', ' ', $lastOrder->status) }}</span>
                        </div>
                        <p class="text-xs text-plum-400">{{ $lastOrder->created_at->format('j M Y') }}</p>
                    </div>
                @else
                    <div class="px-6 py-8 text-center">
                        <p class="text-sm text-plum-400">No orders yet.</p>
                    </div>
                @endif
            @endif
        </div>

        {{-- ── Recent consultations ─────────────────────────────────────────── --}}
        <div class="card lg:col-span-2">
            <div class="px-6 py-4 border-b border-plum-100 flex items-center justify-between">
                <h2 class="font-display text-lg font-bold text-plum-800">Recent consultations</h2>
                <a href="{{ route('patient.consultations.index') }}" class="text-xs text-plum-500 hover:text-plum-800">View all</a>
            </div>
            <div class="divide-y divide-plum-50">
                @forelse (auth()->user()->consultations()->with('product')->latest()->limit(4)->get() as $c)
                    <a href="{{ route('patient.consultations.show', $c) }}"
                       class="px-6 py-4 flex items-center justify-between gap-3 hover:bg-plum-50 transition-colors group">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-plum-800 truncate">{{ $c->product->name }}</p>
                            <p class="text-xs text-plum-400 mt-0.5">{{ $c->submitted_at?->format('j M Y') ?? $c->created_at->format('j M Y') }}</p>
                        </div>
                        <x-patient.consultation-status-badge :status="$c->status" />
                    </a>
                @empty
                    <div class="px-6 py-8 text-center">
                        <p class="text-sm text-plum-400 mb-3">No consultations yet.</p>
                        <a href="{{ route('treatments.index') }}" class="btn-primary btn-sm text-xs">Start a consultation</a>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</x-patient-layout>
