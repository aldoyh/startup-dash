<?php

namespace App\Filament\Resources\WorkflowSecretResource\Pages;

use App\Filament\Resources\WorkflowSecretResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorkflowSecrets extends ListRecords
{
    protected static string $resource = WorkflowSecretResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
