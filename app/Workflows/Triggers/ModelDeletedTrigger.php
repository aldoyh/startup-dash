<?php

namespace App\Workflows\Triggers;

use App\Models\Workflow;
use App\Workflows\Contracts\TriggerContract;

class ModelDeletedTrigger implements TriggerContract
{
    public function getKey(): string
    {
        return 'model-deleted';
    }

    public function getLabel(): string
    {
        return 'Model Deleted';
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

    public function register(Workflow $workflow): void {}

    public function resolve(mixed $event): array
    {
        if (is_object($event)) {
            return $event->toArray();
        }

        return (array) $event;
    }
}
