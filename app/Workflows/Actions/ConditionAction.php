<?php

namespace App\Workflows\Actions;

use App\Models\WorkflowRunStep;
use App\Workflows\Contracts\ActionContract;

class ConditionAction implements ActionContract
{
    public function getKey(): string
    {
        return 'condition';
    }

    public function getLabel(): string
    {
        return 'Condition Branch';
    }

    public function getConfigSchema(): array
    {
        return [
            'left' => ['type' => 'text', 'label' => 'Left Value', 'required' => true],
            'operator' => [
                'type' => 'select',
                'label' => 'Operator',
                'options' => ['==', '!=', '>', '<', '>=', '<=', 'contains', 'not_contains', 'is_empty', 'is_not_empty'],
                'required' => true,
            ],
            'right' => ['type' => 'text', 'label' => 'Right Value'],
        ];
    }

    public function execute(array $input, WorkflowRunStep $step): array
    {
        $left = $input['left'] ?? '';
        $operator = $input['operator'] ?? '==';
        $right = $input['right'] ?? '';

        $result = $this->evaluate($left, $operator, $right);

        return ['result' => $result, 'left' => $left, 'operator' => $operator, 'right' => $right];
    }

    protected function evaluate(mixed $left, string $operator, mixed $right): bool
    {
        return match ($operator) {
            '==' => $left == $right,
            '!=' => $left != $right,
            '>' => $left > $right,
            '<' => $left < $right,
            '>=' => $left >= $right,
            '<=' => $left <= $right,
            'contains' => str_contains((string) $left, (string) $right),
            'not_contains' => ! str_contains((string) $left, (string) $right),
            'is_empty' => empty($left),
            'is_not_empty' => ! empty($left),
            default => false,
        };
    }
}
