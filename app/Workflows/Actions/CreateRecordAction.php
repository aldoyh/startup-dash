<?php

namespace App\Workflows\Actions;

use App\Models\WorkflowRunStep;
use App\Workflows\Contracts\ActionContract;

class CreateRecordAction implements ActionContract
{
    public function getKey(): string
    {
        return 'create-record';
    }

    public function getLabel(): string
    {
        return 'Create Record';
    }

    public function getConfigSchema(): array
    {
        return [
            'model_class' => ['type' => 'select', 'label' => 'Model', 'required' => true],
            'attributes' => ['type' => 'key-value', 'label' => 'Attributes', 'required' => true],
        ];
    }

    public function execute(array $input, WorkflowRunStep $step): array
    {
        $modelClass = $input['model_class'] ?? '';
        $attributes = $input['attributes'] ?? [];

        if (! class_exists($modelClass)) {
            throw new \RuntimeException("Model class not found: {$modelClass}");
        }

        $record = $modelClass::create($attributes);

        return ['created_id' => $record->getKey(), 'model' => $modelClass];
    }
}
