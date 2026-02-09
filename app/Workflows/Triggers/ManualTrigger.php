<?php

namespace App\Workflows\Triggers;

use App\Models\Workflow;
use App\Workflows\Contracts\TriggerContract;

class ManualTrigger implements TriggerContract
{
    public function getKey(): string
    {
        return 'manual';
    }

    public function getLabel(): string
    {
        return 'Manual Trigger';
    }

    public function getConfigSchema(): array
    {
        return [];
    }

    public function register(Workflow $workflow): void
    {
        // Manual triggers don't auto-register - they are invoked via the UI
    }

    public function resolve(mixed $event): array
    {
        return [
            'triggered_by' => 'manual',
            'triggered_at' => now()->toIso8601String(),
        ];
    }
}
