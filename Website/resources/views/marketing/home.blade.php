<x-app-layout>
<x-slot name="title">Online Pharmacy — GPhC Registered</x-slot>
<x-slot name="description">Prescribe & Co — UK online pharmacy. Clinically reviewed prescriptions for weight loss, ED, skin health, hair loss and digestive health. Discreetly delivered.</x-slot>

{{-- ── Hero ─────────────────────────────────────────────────────────────────── --}}
<section class="relative bg-plum-800 overflow-hidden">
    {{-- Background texture --}}
    <div class="absolute inset-0 opacity-5" style="background-image:radial-gradient(circle at 2px 2px, #C9A8D4 1px, transparent 0); background-size:32px 32px;"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 lg:py-32">
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 bg-lilac-500/10 border border-lilac-500/20 rounded-full px-4 py-1.5 mb-6">
                <div class="w-2 h-2 rounded-full bg-lilac-400 animate-pulse"></div>
                <span class="text-lilac-400 text-xs font-medium">GPhC Registered Pharmacy · UK Prescribers</span>
            </div>

            <h1 class="font-display text-display-xl font-bold text-white mb-6 text-balance leading-tight">
                Prescription treatments,<br>
                <span class="text-lilac-400">clinically reviewed</span><br>
                and delivered to you.
            </h1>

            <p class="text-lilac-500/80 text-lg mb-10 leading-relaxed max-w-xl">
                Start a free online consultation. Our UK-registered prescribers review your answers and — if appropriate — issue a prescription delivered discreetly to your door.
            </p>

            <div class="flex flex-wrap gap-4">
                <a href="{{ route('treatments.index') }}" class="btn-lilac btn-lg">
                    Start a consultation
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
                <a href="{{ route('how-it-works') }}" class="inline-flex items-center gap-2 text-lilac-400 hover:text-lilac-300 font-medium text-lg transition-colors">
                    How it works
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            {{-- Trust micro-signals --}}
            <div class="mt-12 flex flex-wrap gap-6">
                @foreach(['GPhC Registered','MHRA Compliant','ICO Registered','SSL Secured','UK Pharmacists'] as $t)
                    <div class="flex items-center gap-1.5 text-lilac-500/60 text-xs">
                        <svg class="w-3 h-3 text-lilac-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        {{ $t }}
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ── How it works strip ───────────────────────────────────────────────────── --}}
<section class="bg-white border-b border-plum-100 py-14">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-0 lg:divide-x divide-plum-100">
            @foreach([
                ['step'=>'01','title'=>'Choose a treatment','body'=>'Browse our clinically approved treatments and select what\'s right for you.'],
                ['step'=>'02','title'=>'Complete a consultation','body'=>'Answer a short questionnaire reviewed by a UK-registered prescriber.'],
                ['step'=>'03','title'=>'Get prescribed','body'=>'If appropriate, a prescription is issued and sent to our dispensing team.'],
                ['step'=>'04','title'=>'Delivered to you','body'=>'Your treatment is dispensed and dispatched in discreet, secure packaging.'],
            ] as $step)
                <div class="lg:px-8 first:pl-0 last:pr-0">
                    <span class="font-display text-4xl font-bold text-lilac-200 block mb-2">{{ $step['step'] }}</span>
                    <h3 class="font-display text-lg font-bold text-plum-800 mb-2">{{ $step['title'] }}</h3>
                    <p class="text-sm text-plum-500 leading-relaxed">{{ $step['body'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── Treatment categories ─────────────────────────────────────────────────── --}}
<section class="py-20 bg-plum-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="section-heading mb-3">Treatments we offer</h2>
            <p class="section-subheading max-w-xl mx-auto">Evidence-based prescription treatments across five specialisms. All consultations are free and reviewed by UK clinicians.</p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @php
                $categoryData = [
                    'weight-loss'          => ['emoji'=>'⚖️', 'colour'=>'bg-violet-50 border-violet-200 hover:border-violet-400',  'tag_colour'=>'bg-violet-100 text-violet-800',  'products'=>['Mounjaro','Wegovy','Orlistat']],
                    'erectile-dysfunction' => ['emoji'=>'💊', 'colour'=>'bg-blue-50 border-blue-200 hover:border-blue-400',        'tag_colour'=>'bg-blue-100 text-blue-800',      'products'=>['Sildenafil','Tadalafil','Viagra Connect']],
                    'skin-health'          => ['emoji'=>'✨', 'colour'=>'bg-rose-50 border-rose-200 hover:border-rose-400',        'tag_colour'=>'bg-rose-100 text-rose-800',      'products'=>['Tretinoin','Lymecycline']],
                    'hair-loss'            => ['emoji'=>'🌿', 'colour'=>'bg-emerald-50 border-emerald-200 hover:border-emerald-400','tag_colour'=>'bg-emerald-100 text-emerald-800','products'=>['Finasteride','Minoxidil']],
                    'digestive-health'     => ['emoji'=>'🌱', 'colour'=>'bg-amber-50 border-amber-200 hover:border-amber-400',     'tag_colour'=>'bg-amber-100 text-amber-800',    'products'=>['Omeprazole','Mebeverine']],
                ];
            @endphp

            @foreach ($categories as $category)
                @php $cd = $categoryData[$category->slug] ?? ['emoji'=>'💊','colour'=>'bg-white border-plum-200 hover:border-plum-400','tag_colour'=>'bg-plum-100 text-plum-800','products'=>[]]; @endphp
                <a href="{{ route('treatments.category', $category) }}"
                   class="group card border-2 transition-all duration-200 hover:shadow-plum {{ $cd['colour'] }} p-7">
                    <div class="text-3xl mb-4">{{ $cd['emoji'] }}</div>
                    <h3 class="font-display text-xl font-bold text-plum-800 mb-2 group-hover:text-plum-900">
                        {{ $category->name }}
                    </h3>
                    <p class="text-sm text-plum-500 mb-4 leading-relaxed">{{ $category->description }}</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach(array_slice($cd['products'], 0, 3) as $prod)
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $cd['tag_colour'] }}">{{ $prod }}</span>
                        @endforeach
                    </div>
                    <div class="mt-5 flex items-center gap-1 text-sm font-medium text-plum-700 group-hover:gap-2 transition-all">
                        View treatments
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>
            @endforeach

            {{-- CTA card --}}
            <div class="card bg-plum-800 border-2 border-plum-800 p-7 flex flex-col justify-between">
                <div>
                    <p class="text-lilac-400 font-display text-2xl font-bold mb-3">Not sure where to start?</p>
                    <p class="text-lilac-500/70 text-sm leading-relaxed mb-6">Browse all our treatments or speak to our pharmacy team.</p>
                </div>
                <a href="{{ route('treatments.index') }}" class="btn-lilac self-start">Browse all treatments</a>
            </div>
        </div>
    </div>
</section>

{{-- ── Why P&Co ──────────────────────────────────────────────────────────────── --}}
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
            <div>
                <p class="text-lilac-500 font-medium text-sm mb-3 uppercase tracking-wider">Why Prescribe & Co</p>
                <h2 class="section-heading mb-6 text-balance">
                    Premium pharmacy care,<br>without the wait.
                </h2>
                <div class="space-y-5">
                    @foreach([
                        ['icon'=>'shield-check','title'=>'GPhC registered & regulated','body'=>'Every prescription is reviewed and issued by a UK-registered prescriber. Our pharmacy holds full GPhC registration.'],
                        ['icon'=>'clock','title'=>'Fast turnaround','body'=>'Most consultations are reviewed within a few hours. Approved prescriptions are dispensed the same working day.'],
                        ['icon'=>'lock','title'=>'Completely discreet','body'=>'All orders arrive in plain, unmarked packaging. Your medical information is never shared without your consent.'],
                        ['icon'=>'refresh','title'=>'Ongoing care','body'=>'Subscribe to regular treatment deliveries and manage everything — your prescriptions, orders and messages — in one place.'],
                    ] as $item)
                        <div class="flex gap-4">
                            <div class="w-9 h-9 rounded-xl bg-lilac-100 flex items-center justify-center shrink-0 mt-0.5">
                                @if($item['icon']==='shield-check')
                                    <svg class="w-4 h-4 text-plum-700" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                @elseif($item['icon']==='clock')
                                    <svg class="w-4 h-4 text-plum-700" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                                @elseif($item['icon']==='lock')
                                    <svg class="w-4 h-4 text-plum-700" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                                @else
                                    <svg class="w-4 h-4 text-plum-700" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/></svg>
                                @endif
                            </div>
                            <div>
                                <p class="font-semibold text-plum-800 mb-1">{{ $item['title'] }}</p>
                                <p class="text-sm text-plum-500 leading-relaxed">{{ $item['body'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Stats panel --}}
            <div class="grid grid-cols-2 gap-4">
                @foreach([
                    ['value'=>'48hr','label'=>'Average prescription turnaround'],
                    ['value'=>'100%','label'=>'UK-based prescribers and pharmacists'],
                    ['value'=>'GPhC','label'=>'Registered and regulated pharmacy'],
                    ['value'=>'256-bit','label'=>'SSL encrypted data and payments'],
                ] as $stat)
                    <div class="card p-6 text-center">
                        <p class="font-display text-3xl font-bold text-plum-800 mb-2">{{ $stat['value'] }}</p>
                        <p class="text-xs text-plum-500 leading-snug">{{ $stat['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ── Testimonials ─────────────────────────────────────────────────────────── --}}
<section class="py-20 bg-plum-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="font-display text-display-md font-bold text-white mb-3">What our patients say</h2>
            <p class="text-lilac-500/70">Real experiences from verified patients.</p>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
            @foreach([
                ['initials'=>'S.T.','name'=>'S.T.','treatment'=>'Weight Loss','body'=>'The consultation was straightforward and I had a response within a few hours. The process was far simpler than I expected and the whole thing felt very professional.','stars'=>5],
                ['initials'=>'M.R.','name'=>'M.R.','treatment'=>'Skin Health','body'=>'I\'d been struggling to get a tretinoin prescription from my GP for months. Prescribe & Co reviewed my consultation the same day and my prescription arrived the next morning.','stars'=>5],
                ['initials'=>'J.W.','name'=>'J.W.','treatment'=>'Hair Loss','body'=>'Really appreciated the discreet packaging and the ability to message the pharmacy team directly when I had a question. Highly recommend for anyone nervous about online pharmacies.','stars'=>5],
            ] as $review)
                <div class="bg-plum-700/50 border border-plum-600 rounded-2xl p-6">
                    <div class="flex gap-0.5 mb-4">
                        @for($i=0;$i<$review['stars'];$i++)
                            <svg class="w-4 h-4 text-lilac-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                    <p class="text-lilac-400/90 text-sm leading-relaxed mb-5">"{{ $review['body'] }}"</p>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-lilac-500/20 flex items-center justify-center">
                            <span class="text-lilac-400 text-xs font-bold">{{ $review['initials'] }}</span>
                        </div>
                        <div>
                            <p class="text-lilac-400 text-sm font-medium">{{ $review['name'] }}</p>
                            <p class="text-lilac-500/50 text-xs">{{ $review['treatment'] }} patient</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── Final CTA ────────────────────────────────────────────────────────────── --}}
<section class="py-20 bg-lilac-50">
    <div class="max-w-3xl mx-auto px-4 text-center">
        <h2 class="section-heading mb-4">Ready to get started?</h2>
        <p class="section-subheading mb-8">Choose a treatment category and complete a free online consultation. A UK prescriber will review your answers, usually within a few hours.</p>
        <a href="{{ route('treatments.index') }}" class="btn-primary btn-lg">
            Browse treatments
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </a>
    </div>
</section>

</x-app-layout>
