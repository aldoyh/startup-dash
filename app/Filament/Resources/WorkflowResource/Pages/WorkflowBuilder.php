<?php

namespace App\Filament\Resources\WorkflowResource\Pages;

use App\Filament\Resources\WorkflowResource;
use App\Models\Workflow;
use Filament\Actions;
use Filament\Resources\Pages\Page;

class WorkflowBuilder extends Page
{
    protected static string $resource = WorkflowResource::class;

    protected string $view = 'filament.pages.workflow-builder';

    public Workflow $record;

    public function getTitle(): string
    {
        return 'Builder: '.$this->record->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('edit')
                ->label('Edit Settings')
                ->icon('heroicon-o-cog-6-tooth')
                ->url(fn () => WorkflowResource::getUrl('edit', ['record' => $this->record])),
            Actions\Action::make('run')
                ->label('Test Run')
                ->icon('heroicon-o-play')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () {
                    \App\Jobs\ExecuteWorkflowJob::dispatch(
                        $this->record,
                        ['triggered_by' => 'manual', 'triggered_at' => now()->toIso8601String()],
                        true
                    );

                    \Filament\Notifications\Notification::make()
                        ->title('Test run dispatched')
                        ->success()
                        ->send();
                }),
        ];
    }
}
