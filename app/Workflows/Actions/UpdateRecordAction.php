<?php

namespace App\Workflows\Actions;

use App\Models\WorkflowRunStep;
use App\Workflows\Contracts\ActionContract;

class UpdateRecordAction implements ActionContract
{
    public function getKey(): string
    {
        return 'update-record';
    }

    public function getLabel(): string
    {
        return 'Update Record';
    }

    public function getConfigSchema(): array
    {
        return [
            'model_class' => ['type' => 'select', 'label' => 'Model', 'required' => true],
            'record_id' => ['type' => 'text', 'label' => 'Record ID', 'required' => true],
            'attributes' => ['type' => 'key-value', 'label' => 'Attributes', 'required' => true],
        ];
    }

    public function execute(array $input, WorkflowRunStep $step): array
    {
        $modelClass = $input['model_class'] ?? '';
        $recordId = $input['record_id'] ?? '';
        $attributes = $input['attributes'] ?? [];

        if (! class_exists($modelClass)) {
            throw new \RuntimeException("Model class not found: {$modelClass}");
        }

        $record = $modelClass::findOrFail($recordId);
        $record->update($attributes);

        return ['updated_id' => $record->getKey(), 'model' => $modelClass, 'attributes' => $attributes];
    }
}
