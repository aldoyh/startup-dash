<?php

return [
    'models' => [
        'workflow' => \App\Models\Workflow::class,
        'workflow_run' => \App\Models\WorkflowRun::class,
        'workflow_run_step' => \App\Models\WorkflowRunStep::class,
        'workflow_secret' => \App\Models\WorkflowSecret::class,
    ],

    'queue' => [
        'connection' => env('WORKFLOW_QUEUE_CONNECTION', 'default'),
        'name' => env('WORKFLOW_QUEUE_NAME', 'workflows'),
    ],

    'rate_limits' => [
        'max_concurrent_runs' => 10,
        'max_runs_per_minute' => 60,
        'max_global_runs_per_minute' => 100,
    ],

    'retry' => [
        'max_attempts' => 3,
        'backoff' => [60, 300, 900],
    ],

    'execution' => [
        'max_workflow_depth' => 5,
    ],

    'discoverable_models' => [
        app_path('Models'),
    ],
];
