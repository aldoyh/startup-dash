<?php

namespace Database\Factories;

use App\Models\Workflow;
use App\Models\WorkflowRun;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkflowRun>
 */
class WorkflowRunFactory extends Factory
{
    protected $model = WorkflowRun::class;

    public function definition(): array
    {
        return [
            'workflow_id' => Workflow::factory(),
            'status' => 'pending',
            'trigger_data' => [],
            'is_test' => false,
        ];
    }

    public function running(): static
    {
        return $this->state(fn () => [
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => 'completed',
            'started_at' => now()->subSeconds(5),
            'completed_at' => now(),
        ]);
    }

    public function failed(string $error = 'Execution failed'): static
    {
        return $this->state(fn () => [
            'status' => 'failed',
            'started_at' => now()->subSeconds(5),
            'completed_at' => now(),
            'error' => $error,
        ]);
    }

    public function test(): static
    {
        return $this->state(fn () => ['is_test' => true]);
    }
}
