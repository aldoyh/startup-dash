<?php

namespace App\Filament\Pages;

use App\Models\WorkflowRun;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class CalendarPage extends Page
{
    protected static BackedEnum|string|null $navigationIcon  = 'heroicon-o-calendar-days';
    protected static ?string               $navigationLabel = 'Calendar';
    protected static ?string               $title           = 'Calendar';
    protected static ?string               $slug            = 'calendar';
    protected static ?int                  $navigationSort  = 2;
    protected string                       $view            = 'filament.pages.calendar-page';

    public int $year;
    public int $month;
    public ?string $selectedDate = null;

    public function mount(): void
    {
        $this->year  = now()->year;
        $this->month = now()->month;
    }

    public function previousMonth(): void
    {
        $d = Carbon::create($this->year, $this->month)->subMonth();
        $this->year  = $d->year;
        $this->month = $d->month;
        $this->selectedDate = null;
    }

    public function nextMonth(): void
    {
        $d = Carbon::create($this->year, $this->month)->addMonth();
        $this->year  = $d->year;
        $this->month = $d->month;
        $this->selectedDate = null;
    }

    public function goToToday(): void
    {
        $this->year  = now()->year;
        $this->month = now()->month;
        $this->selectedDate = null;
    }

    public function selectDate(string $date): void
    {
        $this->selectedDate = $this->selectedDate === $date ? null : $date;
    }

    /** Returns the 35–42 day grid for the displayed month. */
    public function getGridDays(): array
    {
        $first = Carbon::create($this->year, $this->month, 1);
        $last  = $first->copy()->endOfMonth();

        $start = $first->copy()->startOfWeek(Carbon::SUNDAY);
        $end   = $last->copy()->endOfWeek(Carbon::SATURDAY);

        $days = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $days[] = $d->copy();
        }

        return $days;
    }

    /** Runs grouped by Y-m-d, for the full grid window. */
    public function getRunsByDate(): Collection
    {
        $first = Carbon::create($this->year, $this->month, 1);
        $last  = $first->copy()->endOfMonth();
        $start = $first->copy()->startOfWeek(Carbon::SUNDAY);
        $end   = $last->copy()->endOfWeek(Carbon::SATURDAY)->endOfDay();

        return WorkflowRun::with('workflow:id,name')
            ->whereBetween('created_at', [$start, $end])
            ->orderByRaw("CASE status WHEN 'failed' THEN 0 WHEN 'running' THEN 1 WHEN 'completed' THEN 2 ELSE 3 END")
            ->get()
            ->groupBy(fn ($run) => $run->created_at->format('Y-m-d'));
    }

    /** All runs for the selected date (used in the detail panel). */
    public function getSelectedDayRuns(): Collection
    {
        if (! $this->selectedDate) {
            return collect();
        }

        return WorkflowRun::with('workflow:id,name')
            ->whereDate('created_at', $this->selectedDate)
            ->orderByRaw("CASE status WHEN 'failed' THEN 0 WHEN 'running' THEN 1 WHEN 'completed' THEN 2 ELSE 3 END")
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /** Pill CSS classes per status (static strings so Tailwind scans them). */
    public function pillClasses(string $status): string
    {
        return match ($status) {
            'completed' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
            'failed'    => 'bg-rose-500/20    text-rose-300    border-rose-500/30',
            'running'   => 'bg-blue-500/20    text-blue-300    border-blue-500/30',
            'pending'   => 'bg-slate-500/15   text-slate-400   border-slate-500/20',
            default     => 'bg-gray-500/15    text-gray-400    border-gray-500/20',
        };
    }

    public function dotClasses(string $status): string
    {
        return match ($status) {
            'completed' => 'bg-emerald-400',
            'failed'    => 'bg-rose-400',
            'running'   => 'bg-blue-400 animate-pulse',
            'pending'   => 'bg-slate-500',
            default     => 'bg-gray-500',
        };
    }

    /** Ambient tint on cells with notable activity. */
    public function cellTint(Collection $runs): string
    {
        if ($runs->isEmpty()) {
            return '';
        }
        if ($runs->where('status', 'failed')->isNotEmpty()) {
            return 'bg-rose-500/5';
        }
        if ($runs->where('status', 'running')->isNotEmpty()) {
            return 'bg-blue-500/5';
        }
        if ($runs->where('status', 'completed')->count() === $runs->count()) {
            return 'bg-emerald-500/5';
        }

        return '';
    }
}
