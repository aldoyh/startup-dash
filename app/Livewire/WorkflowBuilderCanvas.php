<?php

namespace App\Livewire;

use App\Models\Workflow;
use App\Workflows\WorkflowRegistry;
use Livewire\Component;

class WorkflowBuilderCanvas extends Component
{
    public Workflow $workflow;

    public array $steps = [];

    public ?int $selectedStepIndex = null;

    public string $selectedActionType = '';

    public array $stepConfig = [];

    // Condition branch fields
    public array $trueSteps = [];

    public array $falseSteps = [];

    public function mount(Workflow $workflow): void
    {
        $this->workflow = $workflow;
        $this->steps = $workflow->steps ?? [];
    }

    public function addStep(): void
    {
        if (empty($this->selectedActionType)) {
            return;
        }

        $action = WorkflowRegistry::getAction($this->selectedActionType);
        if (! $action) {
            return;
        }

        $newStep = [
            'id' => 'step_'.uniqid(),
            'action_type' => $this->selectedActionType,
            'label' => $action->getLabel(),
            'config' => [],
        ];

        if ($this->selectedActionType === 'condition') {
            $newStep['true_steps'] = [];
            $newStep['false_steps'] = [];
        }

        $this->steps[] = $newStep;
        $this->selectedActionType = '';
        $this->saveSteps();
    }

    public function removeStep(int $index): void
    {
        unset($this->steps[$index]);
        $this->steps = array_values($this->steps);

        if ($this->selectedStepIndex === $index) {
            $this->selectedStepIndex = null;
            $this->stepConfig = [];
        }

        $this->saveSteps();
    }

    public function selectStep(int $index): void
    {
        $this->selectedStepIndex = $index;
        $this->stepConfig = $this->steps[$index]['config'] ?? [];

        if ($this->steps[$index]['action_type'] === 'condition') {
            $this->trueSteps = $this->steps[$index]['true_steps'] ?? [];
            $this->falseSteps = $this->steps[$index]['false_steps'] ?? [];
        }
    }

    public function updateStepConfig(): void
    {
        if ($this->selectedStepIndex === null) {
            return;
        }

        $this->steps[$this->selectedStepIndex]['config'] = $this->stepConfig;

        if ($this->steps[$this->selectedStepIndex]['action_type'] === 'condition') {
            $this->steps[$this->selectedStepIndex]['true_steps'] = $this->trueSteps;
            $this->steps[$this->selectedStepIndex]['false_steps'] = $this->falseSteps;
        }

        $this->saveSteps();
    }

    public function moveStepUp(int $index): void
    {
        if ($index <= 0) {
            return;
        }

        [$this->steps[$index - 1], $this->steps[$index]] = [$this->steps[$index], $this->steps[$index - 1]];
        $this->saveSteps();
    }

    public function moveStepDown(int $index): void
    {
        if ($index >= count($this->steps) - 1) {
            return;
        }

        [$this->steps[$index], $this->steps[$index + 1]] = [$this->steps[$index + 1], $this->steps[$index]];
        $this->saveSteps();
    }

    protected function saveSteps(): void
    {
        $this->workflow->update(['steps' => $this->steps]);
    }

    public function getActionOptionsProperty(): array
    {
        return WorkflowRegistry::actionOptions();
    }

    public function getSelectedStepSchemaProperty(): array
    {
        if ($this->selectedStepIndex === null) {
            return [];
        }

        $actionType = $this->steps[$this->selectedStepIndex]['action_type'] ?? '';
        $action = WorkflowRegistry::getAction($actionType);

        return $action?->getConfigSchema() ?? [];
    }

    public function render()
    {
        return view('livewire.workflow-builder-canvas');
    }
}
