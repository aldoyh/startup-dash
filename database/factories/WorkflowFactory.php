<?php

namespace Database\Factories;

use App\Models\Workflow;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Workflow>
 */
class WorkflowFactory extends Factory
{
    protected $model = Workflow::class;

    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'is_active' => false,
            'trigger_type' => 'manual',
            'trigger_config' => [],
            'steps' => [],
            'max_runs_per_minute' => 60,
            'max_concurrent_runs' => 10,
            'test_mode' => false,
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => ['is_active' => true]);
    }

    public function testMode(): static
    {
        return $this->state(fn () => ['test_mode' => true]);
    }

    public function withSteps(array $steps): static
    {
        return $this->state(fn () => ['steps' => $steps]);
    }

    public function withTrigger(string $type, array $config = []): static
    {
        return $this->state(fn () => [
            'trigger_type' => $type,
            'trigger_config' => $config,
        ]);
    }
}
