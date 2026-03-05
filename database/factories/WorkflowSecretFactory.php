<?php

namespace Database\Factories;

use App\Models\WorkflowSecret;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Crypt;

/**
 * @extends Factory<WorkflowSecret>
 */
class WorkflowSecretFactory extends Factory
{
    protected $model = WorkflowSecret::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->slug(2),
            'encrypted_value' => Crypt::encryptString('test-secret-value'),
            'description' => fake()->optional()->sentence(),
        ];
    }

    public function withValue(string $value): static
    {
        return $this->state(fn () => [
            'encrypted_value' => Crypt::encryptString($value),
        ]);
    }
}
