<?php

namespace App\Workflows\Contracts;

use App\Models\Workflow;

interface TriggerContract
{
    public function getKey(): string;

    public function getLabel(): string;

    public function getConfigSchema(): array;

    public function register(Workflow $workflow): void;

    public function resolve(mixed $event): array;
}
