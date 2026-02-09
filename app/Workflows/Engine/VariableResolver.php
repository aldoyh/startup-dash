<?php

namespace App\Workflows\Engine;

use App\Models\WorkflowSecret;

class VariableResolver
{
    public function resolve(mixed $value, array $context): mixed
    {
        if (is_array($value)) {
            return array_map(fn ($v) => $this->resolve($v, $context), $value);
        }

        if (! is_string($value)) {
            return $value;
        }

        return preg_replace_callback('/\{\{(.+?)\}\}/', function ($matches) use ($context) {
            $path = trim($matches[1]);

            return $this->resolvePath($path, $context);
        }, $value);
    }

    protected function resolvePath(string $path, array $context): string
    {
        // {{now}} - current timestamp
        if ($path === 'now') {
            return now()->toIso8601String();
        }

        // {{secret.name}} - encrypted secrets
        if (str_starts_with($path, 'secret.')) {
            $name = substr($path, 7);
            $secret = WorkflowSecret::where('name', $name)->first();

            return $secret?->getValue() ?? '';
        }

        // {{trigger.field}} or {{trigger.relation.field}}
        if (str_starts_with($path, 'trigger.')) {
            $field = substr($path, 8);

            return data_get($context['trigger'] ?? [], $field, '');
        }

        // {{step.stepId.output.key}}
        if (str_starts_with($path, 'step.')) {
            $parts = explode('.', substr($path, 5), 3);
            $stepId = $parts[0] ?? '';
            $remainder = $parts[1] ?? '';

            if ($remainder === 'output') {
                $key = $parts[2] ?? null;
                $stepOutput = $context['steps'][$stepId] ?? [];

                return $key ? data_get($stepOutput, $key, '') : json_encode($stepOutput);
            }

            return data_get($context['steps'][$stepId] ?? [], $remainder, '');
        }

        return $context[$path] ?? '';
    }
}
