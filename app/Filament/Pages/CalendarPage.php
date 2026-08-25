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

    public function jumpToMonth(): void
    {
        // Triggered by wire:model.live on $year / $month selects — no-op body,
        // Livewire re-renders with the new bound values automatically.
        $this->selectedDate = null;
    }

    public function selectDate(string $date): void
    {
        $this->selectedDate = $this->selectedDate === $date ? null : $date;
    }

    public function availableYears(): array
    {
        $current = now()->year;

        return range($current - 3, $current + 1);
    }

    public function availableMonths(): array
    {
        return collect(range(1, 12))
            ->mapWithKeys(fn ($m) => [$m => Carbon::create(2000, $m, 1)->format('F')])
            ->all();
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
        [$start, $end] = $this->gridWindow();

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

    /** Relative daily volume per weekday column (0-100), for the header load strip. */
    public function getWeekdayLoad(): array
    {
        $byDate = $this->getRunsByDate();
        $totals = array_fill(0, 7, 0);

        foreach ($byDate as $dateKey => $runs) {
            $dow = Carbon::parse($dateKey)->dayOfWeek;
            $totals[$dow] += $runs->count();
        }

        $max = max($totals) ?: 1;

        return array_map(fn ($t) => (int) round(($t / $max) * 100), $totals);
    }

    /** Aggregate health for the whole visible month: counts + completion percent. */
    public function getMonthHealth(): array
    {
        $runs = $this->getRunsByDate()->flatten();

        $completed = $runs->where('status', 'completed')->count();
        $failed    = $runs->where('status', 'failed')->count();
        $running   = $runs->where('status', 'running')->count();
        $pending   = $runs->where('status', 'pending')->count();
        $total     = $runs->count();
        $finished  = $completed + $failed;

        return [
            'completed' => $completed,
            'failed'    => $failed,
            'running'   => $running,
            'pending'   => $pending,
            'total'     => $total,
            'percent'   => $finished > 0 ? (int) round(($completed / $finished) * 100) : 100,
        ];
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

    public function ringColor(string $status): string
    {
        return match ($status) {
            'completed' => '#34d399',
            'failed'    => '#fb7185',
            'running'   => '#60a5fa',
            'pending'   => '#64748b',
            default     => '#6b7280',
        };
    }

    /** Ambient heat-tint on cells, intensity scaled by run volume, hue driven by dominant status. */
    public function cellTint(Collection $runs): string
    {
        if ($runs->isEmpty()) {
            return '';
        }

        $intensity = match (true) {
            $runs->count() >= 5 => '/10',
            $runs->count() >= 3 => '/7',
            default             => '/5',
        };

        if ($runs->where('status', 'failed')->isNotEmpty()) {
            return 'bg-rose-500'.$intensity;
        }
        if ($runs->where('status', 'running')->isNotEmpty()) {
            return 'bg-blue-500'.$intensity;
        }
        if ($runs->where('status', 'completed')->count() === $runs->count()) {
            return 'bg-emerald-500'.$intensity;
        }

        return 'bg-white'.$intensity;
    }

    /** A conic-gradient CSS value for a per-day completion ring around the date number. */
    public function dayRingGradient(Collection $runs): ?string
    {
        if ($runs->isEmpty()) {
            return null;
        }

        $total    = $runs->count();
        $failed   = $runs->where('status', 'failed')->count();
        $running  = $runs->where('status', 'running')->count();
        $pending  = $runs->where('status', 'pending')->count();
        $complete = $total - $failed - $running - $pending;

        $segments = [
            ['count' => $complete, 'color' => '#34d399'],
            ['count' => $failed,   'color' => '#fb7185'],
            ['count' => $running,  'color' => '#60a5fa'],
            ['count' => $pending,  'color' => '#64748b'],
        ];

        $deg = 0;
        $stops = [];
        foreach ($segments as $seg) {
            if ($seg['count'] <= 0) {
                continue;
            }
            $sweep = ($seg['count'] / $total) * 360;
            $stops[] = "{$seg['color']} {$deg}deg ".($deg + $sweep).'deg';
            $deg += $sweep;
        }

        if (empty($stops)) {
            return null;
        }

        return 'conic-gradient(from 0deg, '.implode(', ', $stops).')';
    }

    private function gridWindow(): array
    {
        $first = Carbon::create($this->year, $this->month, 1);
        $last  = $first->copy()->endOfMonth();
        $start = $first->copy()->startOfWeek(Carbon::SUNDAY);
        $end   = $last->copy()->endOfWeek(Carbon::SATURDAY)->endOfDay();

        return [$start, $end];
    }
}
