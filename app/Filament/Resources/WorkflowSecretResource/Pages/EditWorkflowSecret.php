<?php

namespace App\Filament\Resources\WorkflowSecretResource\Pages;

use App\Filament\Resources\WorkflowSecretResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWorkflowSecret extends EditRecord
{
    protected static string $resource = WorkflowSecretResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
