<?php

namespace App\Workflows\Contracts;

use App\Models\WorkflowRunStep;

interface ActionContract
{
    public function getKey(): string;

    public function getLabel(): string;

    public function getConfigSchema(): array;

    public function execute(array $input, WorkflowRunStep $step): array;
}
