<?php

namespace App\Workflows\Triggers;

use App\Models\Workflow;
use App\Workflows\Contracts\TriggerContract;

class ScheduleTrigger implements TriggerContract
{
    public function getKey(): string
    {
        return 'schedule';
    }

    public function getLabel(): string
    {
        return 'Schedule (Cron)';
    }

    public function getConfigSchema(): array
    {
        return [
            'cron_expression' => [
                'type' => 'text',
                'label' => 'Cron Expression',
                'placeholder' => '*/5 * * * *',
                'required' => true,
            ],
        ];
    }

    public function register(Workflow $workflow): void
    {
        // Scheduled triggers are registered via the Laravel scheduler in the service provider
    }

    public function resolve(mixed $event): array
    {
        return [
            'triggered_by' => 'schedule',
            'triggered_at' => now()->toIso8601String(),
        ];
    }
}
