<x-filament-panels::page>

{{-- ── Ambient background orbs ─────────────────────────────────────────── --}}
<div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden" aria-hidden="true">
    <div class="absolute -top-48 -left-48 size-[640px] rounded-full bg-amber-500/10 blur-[120px] animate-pulse"></div>
    <div class="absolute top-1/2 -right-48 size-[520px] rounded-full bg-violet-500/10 blur-[100px] animate-pulse [animation-delay:3s]"></div>
    <div class="absolute -bottom-48 left-1/3 size-[440px] rounded-full bg-sky-500/8 blur-[90px] animate-pulse [animation-delay:6s]"></div>
</div>

@php
    $days       = $this->getGridDays();
    $byDate     = $this->getRunsByDate();
    $today      = now()->format('Y-m-d');
    $monthName  = \Carbon\Carbon::create($year, $month)->format('F Y');
    $dayNames   = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    $selectedRuns = $this->getSelectedDayRuns();
    $health     = $this->getMonthHealth();
    $load       = $this->getWeekdayLoad();
    $years      = $this->availableYears();
    $months     = $this->availableMonths();
    $healthRing = 'conic-gradient(from 0deg, #34d399 0deg '.($health['percent'] * 3.6).'deg, rgba(255,255,255,0.08) '.($health['percent'] * 3.6).'deg 360deg)';
@endphp

<div
    x-data
    @keydown.window.left="$wire.previousMonth()"
    @keydown.window.right="$wire.nextMonth()"
    @keydown.window.t="$wire.goToToday()"
    class="space-y-6"
>

{{-- ── Calendar card ────────────────────────────────────────────────────── --}}
<div class="relative overflow-hidden rounded-2xl
            bg-gray-900/60 backdrop-blur-xl
            border border-white/10
            shadow-[0_8px_48px_rgba(0,0,0,0.5)]">

    {{-- Top shimmer line --}}
    <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-amber-500/50 to-transparent"></div>

    {{-- ── Header ──────────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center justify-between gap-4 px-6 py-5 border-b border-white/8">

        {{-- Month / Year nav + quick jump --}}
        <div class="flex items-center gap-3">
            <button wire:click="previousMonth"
                    class="inline-flex items-center justify-center size-9 rounded-xl
                           bg-white/6 hover:bg-white/12 border border-white/10 hover:border-white/20
                           text-gray-400 hover:text-white
                           active:scale-95 transition-all duration-150
                           focus:outline-none focus:ring-2 focus:ring-amber-500/50"
                    aria-label="Previous month">
                <x-heroicon-m-chevron-left class="size-4" />
            </button>

            <div class="flex items-center gap-1.5">
                <select wire:model.live="month" wire:change="jumpToMonth"
                        class="appearance-none cursor-pointer rounded-lg bg-white/6 hover:bg-white/10 border border-white/10
                               text-white text-sm font-bold tracking-tight py-1.5 pl-3 pr-7
                               focus:outline-none focus:ring-2 focus:ring-amber-500/50 transition-colors duration-150"
                        style="background-image:url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke-width=%222%22 stroke=%22%23a1a1aa%22><path stroke-linecap=%22round%22 stroke-linejoin=%22round%22 d=%22M19.5 8.25l-7.5 7.5-7.5-7.5%22 /></svg>'); background-repeat:no-repeat; background-position:right 0.4rem center; background-size:1rem;">
                    @foreach($months as $num => $label)
                        <option value="{{ $num }}" class="bg-gray-900 text-white">{{ $label }}</option>
                    @endforeach
                </select>

                <select wire:model.live="year" wire:change="jumpToMonth"
                        class="appearance-none cursor-pointer rounded-lg bg-white/6 hover:bg-white/10 border border-white/10
                               text-white text-sm font-bold tracking-tight py-1.5 pl-3 pr-7
                               focus:outline-none focus:ring-2 focus:ring-amber-500/50 transition-colors duration-150"
                        style="background-image:url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke-width=%222%22 stroke=%22%23a1a1aa%22><path stroke-linecap=%22round%22 stroke-linejoin=%22round%22 d=%22M19.5 8.25l-7.5 7.5-7.5-7.5%22 /></svg>'); background-repeat:no-repeat; background-position:right 0.4rem center; background-size:1rem;">
                    @foreach($years as $y)
                        <option value="{{ $y }}" class="bg-gray-900 text-white">{{ $y }}</option>
                    @endforeach
                </select>
            </div>

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

        {{-- Right controls: health ring + status pills + today --}}
        <div class="flex items-center gap-4">

            {{-- Month health donut --}}
            @if($health['total'] > 0)
            <div class="hidden md:flex items-center gap-2.5" title="{{ $health['percent'] }}% completed vs failed">
                <div class="relative size-9 rounded-full" style="background:{{ $healthRing }}">
                    <div class="absolute inset-[3px] rounded-full bg-gray-900 flex items-center justify-center">
                        <span class="text-[10px] font-bold text-white">{{ $health['percent'] }}%</span>
                    </div>
                </div>
                <div class="text-xs leading-tight">
                    <p class="font-semibold text-gray-300">Health</p>
                    <p class="text-gray-500">{{ $health['total'] }} runs</p>
                </div>
            </div>
            <div class="hidden md:block w-px h-8 bg-white/10"></div>
            @endif

            <div class="hidden sm:flex items-center gap-2 text-xs font-medium">
                @if($health['completed'])
                <span class="flex items-center gap-1 rounded-full px-2.5 py-1 bg-emerald-500/15 text-emerald-400 border border-emerald-500/25">
                    <span class="size-1.5 rounded-full bg-emerald-400"></span>{{ $health['completed'] }}
                </span>
                @endif
                @if($health['failed'])
                <span class="flex items-center gap-1 rounded-full px-2.5 py-1 bg-rose-500/15 text-rose-400 border border-rose-500/25">
                    <span class="size-1.5 rounded-full bg-rose-400"></span>{{ $health['failed'] }}
                </span>
                @endif
                @if($health['running'])
                <span class="flex items-center gap-1 rounded-full px-2.5 py-1 bg-blue-500/15 text-blue-400 border border-blue-500/25">
                    <span class="size-1.5 rounded-full bg-blue-400 animate-pulse"></span>{{ $health['running'] }}
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

    <div class="overflow-x-auto">
    <div class="min-w-[720px]">

    {{-- ── Day-of-week headers + weekday load strip ───────────────────────── --}}
    <div class="grid grid-cols-7 border-b border-white/8 bg-white/[0.03]">
        @foreach($dayNames as $i => $name)
        <div class="py-2.5 text-center">
            <p class="text-xs font-bold uppercase tracking-widest mb-1.5
                      {{ in_array($i,[0,6]) ? 'text-amber-400/70' : 'text-gray-300' }}">
                {{ $name }}
            </p>
            {{-- Relative volume bar for this weekday across the visible month --}}
            <div class="mx-auto h-1 w-8 rounded-full bg-white/8 overflow-hidden">
                <div class="h-full rounded-full bg-gradient-to-r from-amber-500/70 to-amber-400/70 transition-all duration-500"
                     style="width: {{ $load[$i] }}%"></div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ── Day grid ─────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-7 divide-x divide-y divide-white/[0.04]">
        @foreach($days as $index => $day)
        @php
            $dateKey   = $day->format('Y-m-d');
            $isToday   = $dateKey === $today;
            $isCurrent = $day->month === $month;
            $isSelected= $dateKey === $selectedDate;
            $dayRuns   = $byDate->get($dateKey, collect());
            $shown     = $dayRuns->take(3);
            $overflow  = $dayRuns->count() - 3;
            $tint      = $this->cellTint($dayRuns);
            $ring      = $this->dayRingGradient($dayRuns);
            $isWeekend = in_array($day->dayOfWeek, [0, 6]);
        @endphp

        <button
            wire:click="selectDate('{{ $dateKey }}')"
            x-data="{ shown: false }"
            x-init="setTimeout(() => shown = true, {{ min($index * 8, 200) }})"
            x-bind:class="shown ? 'opacity-100 scale-100' : 'opacity-0 scale-95'"
            class="group relative min-h-[110px] p-2.5 text-left
                   transition-all duration-300
                   {{ $tint }}
                   {{ $isWeekend && !$isToday ? 'bg-white/[0.01]' : '' }}
                   {{ $isSelected ? 'bg-amber-500/8 ring-1 ring-inset ring-amber-500/40' : 'hover:bg-white/[0.04]' }}
                   {{ !$isCurrent ? 'opacity-35' : '' }}
                   focus:outline-none focus:ring-1 focus:ring-inset focus:ring-amber-500/30"
            aria-label="{{ $day->format('F j, Y') }}{{ $dayRuns->count() ? ', '.$dayRuns->count().' runs' : '' }}"
        >
            {{-- Date number, wrapped in a conic-gradient completion ring when it has runs --}}
            <div class="flex items-start justify-between mb-2">
                @if($isToday)
                    <span class="inline-flex items-center justify-center size-7 rounded-full
                                 bg-amber-500 text-gray-950 font-bold text-sm shadow-[0_0_12px_rgba(251,191,36,0.6)]">
                        {{ $day->day }}
                    </span>
                @elseif($ring)
                    <span class="relative inline-flex items-center justify-center size-7 rounded-full p-[2px]" style="background: {{ $ring }}">
                        <span class="flex items-center justify-center size-full rounded-full bg-gray-900/90
                                     text-sm font-medium {{ $isCurrent ? 'text-gray-200' : 'text-gray-600' }}">
                            {{ $day->day }}
                        </span>
                    </span>
                @else
                    <span class="inline-flex items-center justify-center size-7 text-sm font-medium
                                 {{ $isCurrent ? 'text-gray-300' : 'text-gray-600' }}">
                        {{ $day->day }}
                    </span>
                @endif

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
    </div>

    {{-- ── Legend ──────────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center gap-x-5 gap-y-2 px-6 py-3.5 border-t border-white/8 bg-white/[0.03] text-xs font-medium text-gray-300">
        <span class="font-bold text-gray-200 uppercase tracking-wider text-[10px]">Legend</span>
        <span class="flex items-center gap-1.5"><span class="size-2 rounded-full bg-emerald-400"></span>Completed</span>
        <span class="flex items-center gap-1.5"><span class="size-2 rounded-full bg-rose-400"></span>Failed</span>
        <span class="flex items-center gap-1.5"><span class="size-2 rounded-full bg-blue-400"></span>Running</span>
        <span class="flex items-center gap-1.5"><span class="size-2 rounded-full bg-slate-400"></span>Pending</span>
        <span class="ml-auto hidden sm:inline text-gray-400">← → change month · T today</span>
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

</div>

</x-filament-panels::page>
