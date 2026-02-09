<?php

namespace App\Workflows\Actions;

use App\Models\WorkflowRunStep;
use App\Workflows\Contracts\ActionContract;

class TransformDataAction implements ActionContract
{
    public function getKey(): string
    {
        return 'transform-data';
    }

    public function getLabel(): string
    {
        return 'Transform Data';
    }

    public function getConfigSchema(): array
    {
        return [
            'mapping' => ['type' => 'key-value', 'label' => 'Field Mapping (output_key → input_value)', 'required' => true],
        ];
    }

    public function execute(array $input, WorkflowRunStep $step): array
    {
        $mapping = $input['mapping'] ?? [];
        $output = [];

        foreach ($mapping as $key => $value) {
            $output[$key] = $value;
        }

        return $output;
    }
}
