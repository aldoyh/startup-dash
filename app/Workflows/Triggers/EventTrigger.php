<?php

namespace App\Workflows\Triggers;

use App\Models\Workflow;
use App\Workflows\Contracts\TriggerContract;

class EventTrigger implements TriggerContract
{
    public function getKey(): string
    {
        return 'event';
    }

    public function getLabel(): string
    {
        return 'Laravel Event';
    }

    public function getConfigSchema(): array
    {
        return [
            'event_class' => [
                'type' => 'text',
                'label' => 'Event Class',
                'placeholder' => 'App\\Events\\OrderPlaced',
                'required' => true,
            ],
        ];
    }

    public function register(Workflow $workflow): void {}

    public function resolve(mixed $event): array
    {
        if (is_object($event) && method_exists($event, 'toArray')) {
            return $event->toArray();
        }

        return (array) $event;
    }
}
