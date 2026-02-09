<?php

namespace App\Jobs;

use App\Models\Workflow;
use App\Workflows\Engine\WorkflowExecutor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\RateLimiter;

class ExecuteWorkflowJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries;

    public array $backoff;

    public function __construct(
        public Workflow $workflow,
        public array $triggerData = [],
        public bool $isTest = false,
        public int $depth = 0,
    ) {
        $this->queue = config('workflows.queue.name', 'workflows');
        $this->connection = config('workflows.queue.connection', 'default');
        $this->tries = config('workflows.retry.max_attempts', 3);
        $this->backoff = config('workflows.retry.backoff', [60, 300, 900]);
    }

    public function handle(): void
    {
        $rateLimitKey = 'workflow:'.$this->workflow->id;

        $maxPerMinute = $this->workflow->max_runs_per_minute;
        $maxConcurrent = $this->workflow->max_concurrent_runs;

        if (RateLimiter::tooManyAttempts($rateLimitKey, $maxPerMinute)) {
            $this->release(60);

            return;
        }

        RateLimiter::hit($rateLimitKey, 60);

        $executor = new WorkflowExecutor($this->depth);
        $executor->execute($this->workflow, $this->triggerData, $this->isTest);
    }
}
