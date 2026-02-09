<?php

namespace App\Workflows\Triggers;

use App\Models\Workflow;
use App\Workflows\Contracts\TriggerContract;

class ModelUpdatedTrigger implements TriggerContract
{
    public function getKey(): string
    {
        return 'model-updated';
    }

    public function getLabel(): string
    {
        return 'Model Updated';
    }

    public function getConfigSchema(): array
    {
        return [
            'model_class' => [
                'type' => 'select',
                'label' => 'Model',
                'required' => true,
            ],
            'watched_fields' => [
                'type' => 'tags',
                'label' => 'Watched Fields',
                'helperText' => 'Leave empty to trigger on any change',
            ],
        ];
    }

    public function register(Workflow $workflow): void
    {
        // Model observers are registered in the WorkflowServiceProvider
    }

    public function resolve(mixed $event): array
    {
        if (is_object($event)) {
            return array_merge($event->toArray(), [
                'changed_fields' => $event->getDirty(),
            ]);
        }

        return (array) $event;
    }
}
