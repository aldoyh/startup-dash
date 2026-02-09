<?php

namespace App\Workflows\Actions;

use App\Models\WorkflowRunStep;
use App\Workflows\Contracts\ActionContract;

class DeleteRecordAction implements ActionContract
{
    public function getKey(): string
    {
        return 'delete-record';
    }

    public function getLabel(): string
    {
        return 'Delete Record';
    }

    public function getConfigSchema(): array
    {
        return [
            'model_class' => ['type' => 'select', 'label' => 'Model', 'required' => true],
            'record_id' => ['type' => 'text', 'label' => 'Record ID', 'required' => true],
        ];
    }

    public function execute(array $input, WorkflowRunStep $step): array
    {
        $modelClass = $input['model_class'] ?? '';
        $recordId = $input['record_id'] ?? '';

        if (! class_exists($modelClass)) {
            throw new \RuntimeException("Model class not found: {$modelClass}");
        }

        $record = $modelClass::findOrFail($recordId);
        $record->delete();

        return ['deleted_id' => $recordId, 'model' => $modelClass];
    }
}
