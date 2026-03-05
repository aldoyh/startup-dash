<?php

namespace Database\Factories;

use App\Models\WorkflowRun;
use App\Models\WorkflowRunStep;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkflowRunStep>
 */
class WorkflowRunStepFactory extends Factory
{
    protected $model = WorkflowRunStep::class;

    public function definition(): array
    {
        return [
            'workflow_run_id' => WorkflowRun::factory(),
            'step_id' => 'step_' . fake()->unique()->numberBetween(1, 9999),
            'action_type' => 'send-email',
            'status' => 'pending',
            'input' => [],
            'created_at' => now(),
        ];
    }

    public function running(): static
    {
        return $this->state(fn () => [
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    public function completed(array $output = []): static
    {
        return $this->state(fn () => [
            'status' => 'completed',
            'output' => $output,
            'started_at' => now()->subSeconds(2),
            'completed_at' => now(),
        ]);
    }

    public function failed(string $error = 'Step failed'): static
    {
        return $this->state(fn () => [
            'status' => 'failed',
            'error' => $error,
            'started_at' => now()->subSeconds(2),
            'completed_at' => now(),
        ]);
    }

    public function skipped(): static
    {
        return $this->state(fn () => [
            'status' => 'skipped',
            'completed_at' => now(),
        ]);
    }

    public function forAction(string $actionType, array $input = []): static
    {
        return $this->state(fn () => [
            'action_type' => $actionType,
            'input' => $input,
        ]);
    }
}
