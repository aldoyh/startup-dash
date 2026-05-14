<x-filament-panels::page>

{{-- ── Ambient background orbs ─────────────────────────────────────────── --}}
<div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden" aria-hidden="true">
    <div class="absolute -top-48 -left-48 size-[640px] rounded-full bg-amber-500/10 blur-[120px] animate-pulse"></div>
    <div class="absolute top-1/2 -right-48 size-[520px] rounded-full bg-violet-500/10 blur-[100px] animate-pulse [animation-delay:3s]"></div>
    <div class="absolute -bottom-48 left-1/3 size-[440px] rounded-full bg-sky-500/8 blur-[90px] animate-pulse [animation-delay:6s]"></div>
</div>

@php
    $days      = $this->getGridDays();
    $byDate    = $this->getRunsByDate();
    $today     = now()->format('Y-m-d');
    $monthName = \Carbon\Carbon::create($year, $month)->format('F Y');
    $dayNames  = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    $selectedRuns = $this->getSelectedDayRuns();
@endphp

{{-- ── Calendar card ────────────────────────────────────────────────────── --}}
<div class="relative overflow-hidden rounded-2xl
            bg-gray-900/60 backdrop-blur-xl
            border border-white/10
            shadow-[0_8px_48px_rgba(0,0,0,0.5)]">

    {{-- Top shimmer line --}}
    <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-amber-500/50 to-transparent"></div>

    {{-- ── Header ──────────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between px-6 py-5 border-b border-white/8">

        {{-- Month / Year --}}
        <div class="flex items-center gap-4">
            <button wire:click="previousMonth"
                    class="inline-flex items-center justify-center size-9 rounded-xl
                           bg-white/6 hover:bg-white/12 border border-white/10 hover:border-white/20
                           text-gray-400 hover:text-white
                           active:scale-95 transition-all duration-150
                           focus:outline-none focus:ring-2 focus:ring-amber-500/50"
                    aria-label="Previous month">
                <x-heroicon-m-chevron-left class="size-4" />
            </button>

            <h2 class="text-xl font-bold text-white tracking-tight min-w-[180px] text-center">
                {{ $monthName }}
            </h2>

            <button wire:click="nextMonth"
                    class="inline-flex items-center justify-center size-9 rounded-xl
                           bg-white/6 hover:bg-white/12 border border-white/10 hover:border-white/20
                           text-gray-400 hover:text-white
                           active:scale-95 transition-all duration-150
                           focus:outline-none focus:ring-2 focus:ring-amber-500/50"
                    aria-label="Next month">
                <x-heroicon-m-chevron-right class="size-4" />
            </button>
        </div>

        {{-- Right controls --}}
        <div class="flex items-center gap-3">
            {{-- Mini stats for month --}}
            @php
                $monthRuns = $byDate->flatten();
                $counts = [
                    'completed' => $monthRuns->where('status','completed')->count(),
                    'failed'    => $monthRuns->where('status','failed')->count(),
                    'running'   => $monthRuns->where('status','running')->count(),
                    'pending'   => $monthRuns->where('status','pending')->count(),
                ];
            @endphp
            <div class="hidden sm:flex items-center gap-2 text-xs font-medium">
                @if($counts['completed'])
                <span class="flex items-center gap-1 rounded-full px-2.5 py-1 bg-emerald-500/15 text-emerald-400 border border-emerald-500/25">
                    <span class="size-1.5 rounded-full bg-emerald-400"></span>{{ $counts['completed'] }}
                </span>
                @endif
                @if($counts['failed'])
                <span class="flex items-center gap-1 rounded-full px-2.5 py-1 bg-rose-500/15 text-rose-400 border border-rose-500/25">
                    <span class="size-1.5 rounded-full bg-rose-400"></span>{{ $counts['failed'] }}
                </span>
                @endif
                @if($counts['running'])
                <span class="flex items-center gap-1 rounded-full px-2.5 py-1 bg-blue-500/15 text-blue-400 border border-blue-500/25">
                    <span class="size-1.5 rounded-full bg-blue-400 animate-pulse"></span>{{ $counts['running'] }}
                </span>
                @endif
            </div>

            <button wire:click="goToToday"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold
                           bg-amber-500/15 hover:bg-amber-500/25 text-amber-400 hover:text-amber-300
                           border border-amber-500/30 hover:border-amber-500/50
                           shadow-[0_0_16px_rgba(251,191,36,0.1)] hover:shadow-[0_0_20px_rgba(251,191,36,0.25)]
                           active:scale-95 transition-all duration-150
                           focus:outline-none focus:ring-2 focus:ring-amber-500/50">
                <x-heroicon-m-calendar class="size-3.5" />
                Today
            </button>
        </div>
    </div>

    {{-- ── Day-of-week headers ──────────────────────────────────────────── --}}
    <div class="grid grid-cols-7 border-b border-white/8">
        @foreach($dayNames as $i => $name)
        <div class="py-3 text-center text-xs font-semibold uppercase tracking-widest
                    {{ in_array($i,[0,6]) ? 'text-gray-600' : 'text-gray-500' }}">
            {{ $name }}
        </div>
        @endforeach
    </div>

    {{-- ── Day grid ─────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-7 divide-x divide-y divide-white/[0.04]">
        @foreach($days as $day)
        @php
            $dateKey   = $day->format('Y-m-d');
            $isToday   = $dateKey === $today;
            $isCurrent = $day->month === $month;
            $isSelected= $dateKey === $selectedDate;
            $dayRuns   = $byDate->get($dateKey, collect());
            $shown     = $dayRuns->take(3);
            $overflow  = $dayRuns->count() - 3;
            $tint      = $this->cellTint($dayRuns);
            $isWeekend = in_array($day->dayOfWeek, [0, 6]);
        @endphp

        <button
            wire:click="selectDate('{{ $dateKey }}')"
            class="group relative min-h-[110px] p-2.5 text-left
                   transition-all duration-150
                   {{ $tint }}
                   {{ $isWeekend && !$isToday ? 'bg-white/[0.01]' : '' }}
                   {{ $isSelected ? 'bg-amber-500/8 ring-1 ring-inset ring-amber-500/40' : 'hover:bg-white/[0.04]' }}
                   {{ !$isCurrent ? 'opacity-35' : '' }}
                   focus:outline-none focus:ring-1 focus:ring-inset focus:ring-amber-500/30"
            aria-label="{{ $day->format('F j, Y') }}{{ $dayRuns->count() ? ', '.$dayRuns->count().' runs' : '' }}"
        >
            {{-- Date number --}}
            <div class="flex items-start justify-between mb-2">
                <span class="inline-flex items-center justify-center
                             {{ $isToday
                                 ? 'size-7 rounded-full bg-amber-500 text-gray-950 font-bold text-sm shadow-[0_0_12px_rgba(251,191,36,0.6)]'
                                 : 'size-7 text-sm font-medium '.($isCurrent ? 'text-gray-300' : 'text-gray-600') }}">
                    {{ $day->day }}
                </span>

                {{-- Overflow badge --}}
                @if($overflow > 0)
                <span class="text-[10px] font-semibold text-gray-500 bg-white/8 rounded-md px-1.5 py-0.5 border border-white/8">
                    +{{ $overflow }}
                </span>
                @endif
            </div>

            {{-- Event pills --}}
            <div class="space-y-1">
                @foreach($shown as $run)
                @php $pill = $this->pillClasses($run->status); $dot = $this->dotClasses($run->status); @endphp
                <div class="flex items-center gap-1.5 rounded-md px-1.5 py-0.5
                            border text-[11px] font-medium leading-tight
                            truncate {{ $pill }}"
                     title="{{ $run->workflow?->name }} — {{ $run->status }}">
                    <span class="shrink-0 size-1.5 rounded-full {{ $dot }}"></span>
                    <span class="truncate">{{ $run->workflow?->name ?? 'Unknown' }}</span>
                </div>
                @endforeach
            </div>
        </button>
        @endforeach
    </div>
</div>

{{-- ── Selected-day detail panel ─────────────────────────────────────────── --}}
@if($selectedDate)
<div
    x-data
    x-init="$el.scrollIntoView({ behavior: 'smooth', block: 'nearest' })"
    class="relative overflow-hidden rounded-2xl
           bg-gray-900/70 backdrop-blur-xl
           border border-white/10
           shadow-[0_8px_32px_rgba(0,0,0,0.4)]"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 -translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
>
    {{-- Shimmer top --}}
    <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>

    {{-- Panel header --}}
    <div class="flex items-center justify-between px-6 py-4 border-b border-white/8">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-500">
                {{ \Carbon\Carbon::parse($selectedDate)->format('l') }}
            </p>
            <h3 class="text-lg font-bold text-white">
                {{ \Carbon\Carbon::parse($selectedDate)->format('F j, Y') }}
            </h3>
        </div>
        <div class="flex items-center gap-2 text-xs font-medium">
            <span class="text-gray-500">{{ $selectedRuns->count() }} {{ Str::plural('run', $selectedRuns->count()) }}</span>
            <button wire:click="selectDate('{{ $selectedDate }}')"
                    class="inline-flex items-center justify-center size-7 rounded-lg
                           bg-white/6 hover:bg-white/12 border border-white/10
                           text-gray-400 hover:text-white transition-all duration-150
                           focus:outline-none focus:ring-1 focus:ring-white/20"
                    aria-label="Close detail">
                <x-heroicon-m-x-mark class="size-4" />
            </button>
        </div>
    </div>

    {{-- Run list --}}
    <div class="divide-y divide-white/[0.04]">
        @forelse($selectedRuns as $run)
        @php $pill = $this->pillClasses($run->status); $dot = $this->dotClasses($run->status); @endphp
        <div class="flex items-start gap-4 px-6 py-4 hover:bg-white/[0.025] transition-colors duration-150">

            {{-- Status dot + vertical line --}}
            <div class="relative flex flex-col items-center shrink-0 pt-0.5">
                <span class="size-2.5 rounded-full {{ $dot }}"></span>
                @if(!$loop->last)
                <div class="w-px flex-1 mt-1.5 bg-gradient-to-b from-white/15 to-transparent min-h-[24px]"></div>
                @endif
            </div>

            {{-- Content --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2 flex-wrap">
                    <p class="text-sm font-semibold text-white truncate">
                        {{ $run->workflow?->name ?? 'Unknown Workflow' }}
                    </p>
                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium border {{ $pill }}">
                        <span class="size-1.5 rounded-full {{ $dot }}"></span>
                        {{ ucfirst($run->status) }}
                    </span>
                </div>

                <div class="mt-1 flex items-center gap-3 text-xs text-gray-500">
                    <span class="flex items-center gap-1">
                        <x-heroicon-m-clock class="size-3" />
                        {{ $run->created_at->format('H:i:s') }}
                    </span>
                    @if($run->started_at && $run->completed_at)
                    <span class="flex items-center gap-1">
                        <x-heroicon-m-bolt class="size-3" />
                        {{ number_format($run->started_at->diffInMilliseconds($run->completed_at)) }}ms
                    </span>
                    @endif
                    @if($run->is_test)
                    <span class="text-amber-500/70 flex items-center gap-1">
                        <x-heroicon-m-beaker class="size-3" />
                        Test
                    </span>
                    @endif
                </div>

                @if($run->error)
                <div class="mt-2 rounded-lg bg-rose-500/8 border border-rose-500/20 px-3 py-2">
                    <p class="text-xs font-mono text-rose-400 break-all">{{ Str::limit($run->error, 180) }}</p>
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="flex flex-col items-center justify-center py-12 text-center">
            <div class="size-12 rounded-xl bg-white/6 border border-white/10 flex items-center justify-center mb-3">
                <x-heroicon-o-calendar class="size-6 text-gray-600" />
            </div>
            <p class="text-sm font-medium text-gray-500">No runs on this day</p>
        </div>
        @endforelse
    </div>
</div>
@endif

</x-filament-panels::page>
