<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowRunStep extends Model
{
    use HasUlids;

    public $timestamps = false;

    protected $fillable = [
        'workflow_run_id',
        'step_id',
        'action_type',
        'status',
        'input',
        'output',
        'error',
        'started_at',
        'completed_at',
        'created_at',
    ];

    protected $casts = [
        'input' => 'array',
        'output' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(WorkflowRun::class, 'workflow_run_id');
    }

    public function markRunning(): void
    {
        $this->update(['status' => 'running', 'started_at' => now()]);
    }

    public function markCompleted(array $output = []): void
    {
        $this->update(['status' => 'completed', 'output' => $output, 'completed_at' => now()]);
    }

    public function markFailed(string $error): void
    {
        $this->update(['status' => 'failed', 'error' => $error, 'completed_at' => now()]);
    }

    public function markSkipped(): void
    {
        $this->update(['status' => 'skipped', 'completed_at' => now()]);
    }
}
