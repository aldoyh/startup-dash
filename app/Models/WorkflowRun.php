<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowRun extends Model
{
    use HasUlids;

    protected $fillable = [
        'workflow_id',
        'status',
        'trigger_data',
        'started_at',
        'completed_at',
        'error',
        'is_test',
    ];

    protected $casts = [
        'trigger_data' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_test' => 'boolean',
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(WorkflowRunStep::class);
    }

    public function markRunning(): void
    {
        $this->update(['status' => 'running', 'started_at' => now()]);
    }

    public function markCompleted(): void
    {
        $this->update(['status' => 'completed', 'completed_at' => now()]);
    }

    public function markFailed(string $error): void
    {
        $this->update(['status' => 'failed', 'completed_at' => now(), 'error' => $error]);
    }
}
