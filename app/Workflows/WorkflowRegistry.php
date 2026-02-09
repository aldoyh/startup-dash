<?php

namespace App\Workflows;

use App\Workflows\Contracts\ActionContract;
use App\Workflows\Contracts\TriggerContract;

class WorkflowRegistry
{
    protected static array $triggers = [];

    protected static array $actions = [];

    public static function registerTrigger(TriggerContract $trigger): void
    {
        static::$triggers[$trigger->getKey()] = $trigger;
    }

    public static function registerAction(ActionContract $action): void
    {
        static::$actions[$action->getKey()] = $action;
    }

    public static function getTrigger(string $key): ?TriggerContract
    {
        return static::$triggers[$key] ?? null;
    }

    public static function getAction(string $key): ?ActionContract
    {
        return static::$actions[$key] ?? null;
    }

    public static function allTriggers(): array
    {
        return static::$triggers;
    }

    public static function allActions(): array
    {
        return static::$actions;
    }

    public static function triggerOptions(): array
    {
        return collect(static::$triggers)
            ->mapWithKeys(fn (TriggerContract $t) => [$t->getKey() => $t->getLabel()])
            ->toArray();
    }

    public static function actionOptions(): array
    {
        return collect(static::$actions)
            ->mapWithKeys(fn (ActionContract $a) => [$a->getKey() => $a->getLabel()])
            ->toArray();
    }
}
