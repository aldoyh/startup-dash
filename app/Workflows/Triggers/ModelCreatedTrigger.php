<?php

namespace App\Workflows\Triggers;

use App\Models\Workflow;
use App\Workflows\Contracts\TriggerContract;

class ModelCreatedTrigger implements TriggerContract
{
    public function getKey(): string
    {
        return 'model-created';
    }

    public function getLabel(): string
    {
        return 'Model Created';
    }

    public function getConfigSchema(): array
    {
        return [
            'model_class' => [
                'type' => 'select',
                'label' => 'Model',
                'required' => true,
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
            return $event->toArray();
        }

        return (array) $event;
    }
}
