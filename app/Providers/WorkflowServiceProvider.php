<?php

namespace App\Providers;

use App\Workflows\Actions\ConditionAction;
use App\Workflows\Actions\CreateRecordAction;
use App\Workflows\Actions\DeleteRecordAction;
use App\Workflows\Actions\HttpRequestAction;
use App\Workflows\Actions\RunWorkflowAction;
use App\Workflows\Actions\SendEmailAction;
use App\Workflows\Actions\SendNotificationAction;
use App\Workflows\Actions\TransformDataAction;
use App\Workflows\Actions\UpdateRecordAction;
use App\Workflows\Triggers\EventTrigger;
use App\Workflows\Triggers\ManualTrigger;
use App\Workflows\Triggers\ModelCreatedTrigger;
use App\Workflows\Triggers\ModelDeletedTrigger;
use App\Workflows\Triggers\ModelUpdatedTrigger;
use App\Workflows\Triggers\ScheduleTrigger;
use App\Workflows\WorkflowRegistry;
use Illuminate\Support\ServiceProvider;

class WorkflowServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(config_path('workflows.php'), 'workflows');
    }

    public function boot(): void
    {
        $this->registerTriggers();
        $this->registerActions();
    }

    protected function registerTriggers(): void
    {
        WorkflowRegistry::registerTrigger(new ManualTrigger);
        WorkflowRegistry::registerTrigger(new ScheduleTrigger);
        WorkflowRegistry::registerTrigger(new ModelCreatedTrigger);
        WorkflowRegistry::registerTrigger(new ModelUpdatedTrigger);
        WorkflowRegistry::registerTrigger(new ModelDeletedTrigger);
        WorkflowRegistry::registerTrigger(new EventTrigger);
    }

    protected function registerActions(): void
    {
        WorkflowRegistry::registerAction(new SendEmailAction);
        WorkflowRegistry::registerAction(new SendNotificationAction);
        WorkflowRegistry::registerAction(new CreateRecordAction);
        WorkflowRegistry::registerAction(new UpdateRecordAction);
        WorkflowRegistry::registerAction(new DeleteRecordAction);
        WorkflowRegistry::registerAction(new HttpRequestAction);
        WorkflowRegistry::registerAction(new ConditionAction);
        WorkflowRegistry::registerAction(new TransformDataAction);
        WorkflowRegistry::registerAction(new RunWorkflowAction);
    }
}
