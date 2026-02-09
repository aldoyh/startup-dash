<?php

namespace App\Workflows\Actions;

use App\Models\Workflow;
use App\Models\WorkflowRunStep;
use App\Workflows\Contracts\ActionContract;
use App\Workflows\Engine\WorkflowExecutor;

class RunWorkflowAction implements ActionContract
{
    public function getKey(): string
    {
        return 'run-workflow';
    }

    public function getLabel(): string
    {
        return 'Run Another Workflow';
    }

    public function getConfigSchema(): array
    {
        return [
            'workflow_id' => ['type' => 'select', 'label' => 'Workflow', 'required' => true],
            'input_data' => ['type' => 'key-value', 'label' => 'Input Data'],
        ];
    }

    public function execute(array $input, WorkflowRunStep $step): array
    {
        $workflowId = $input['workflow_id'] ?? '';
        $inputData = $input['input_data'] ?? [];

        $workflow = Workflow::findOrFail($workflowId);

        $maxDepth = config('workflows.execution.max_workflow_depth', 5);
        // Depth tracking could be enhanced with a context parameter
        $executor = new WorkflowExecutor(0);
        $run = $executor->execute($workflow, $inputData);

        return [
            'child_workflow_id' => $workflow->id,
            'child_run_id' => $run->id,
            'child_run_status' => $run->status,
        ];
    }
}
