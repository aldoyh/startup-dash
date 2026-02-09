<?php

namespace App\Workflows\Engine;

use App\Models\Workflow;
use App\Models\WorkflowRun;
use App\Models\WorkflowRunStep;
use App\Workflows\WorkflowRegistry;

class WorkflowExecutor
{
    protected VariableResolver $resolver;

    protected int $currentDepth;

    public function __construct(int $depth = 0)
    {
        $this->resolver = new VariableResolver;
        $this->currentDepth = $depth;
    }

    public function execute(Workflow $workflow, array $triggerData = [], bool $isTest = false): WorkflowRun
    {
        $run = $workflow->runs()->create([
            'status' => 'pending',
            'trigger_data' => $triggerData,
            'is_test' => $isTest || $workflow->test_mode,
        ]);

        $run->markRunning();

        $context = [
            'trigger' => $triggerData,
            'steps' => [],
        ];

        try {
            $steps = $workflow->getStepsArray();
            $this->executeSteps($steps, $run, $context);
            $run->markCompleted();
        } catch (\Throwable $e) {
            $run->markFailed($e->getMessage());
        }

        return $run;
    }

    protected function executeSteps(array $steps, WorkflowRun $run, array &$context): void
    {
        foreach ($steps as $stepDef) {
            $stepId = $stepDef['id'] ?? uniqid('step_');
            $actionType = $stepDef['action_type'] ?? '';
            $config = $stepDef['config'] ?? [];

            $action = WorkflowRegistry::getAction($actionType);
            if (! $action) {
                throw new \RuntimeException("Unknown action type: {$actionType}");
            }

            $resolvedConfig = $this->resolver->resolve($config, $context);

            $stepRecord = $run->steps()->create([
                'step_id' => $stepId,
                'action_type' => $actionType,
                'status' => 'pending',
                'input' => $resolvedConfig,
                'created_at' => now(),
            ]);

            // Handle condition branching
            if ($actionType === 'condition') {
                $this->executeConditionStep($stepDef, $stepRecord, $run, $context, $resolvedConfig);

                continue;
            }

            $stepRecord->markRunning();

            try {
                if ($run->is_test) {
                    $output = ['test_mode' => true, 'would_execute' => $actionType, 'input' => $resolvedConfig];
                } else {
                    $output = $action->execute($resolvedConfig, $stepRecord);
                }

                $stepRecord->markCompleted($output);
                $context['steps'][$stepId] = $output;
            } catch (\Throwable $e) {
                $stepRecord->markFailed($e->getMessage());
                throw $e;
            }
        }
    }

    protected function executeConditionStep(
        array $stepDef,
        WorkflowRunStep $stepRecord,
        WorkflowRun $run,
        array &$context,
        array $resolvedConfig,
    ): void {
        $stepRecord->markRunning();

        $action = WorkflowRegistry::getAction('condition');
        $result = $action->execute($resolvedConfig, $stepRecord);
        $passed = $result['result'] ?? false;

        $stepRecord->markCompleted($result);
        $context['steps'][$stepDef['id'] ?? 'condition'] = $result;

        $branch = $passed ? ($stepDef['true_steps'] ?? []) : ($stepDef['false_steps'] ?? []);

        if (! empty($branch)) {
            $this->executeSteps($branch, $run, $context);
        }
    }
}
