<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkflowRunResource\Pages;
use App\Models\WorkflowRun;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class WorkflowRunResource extends Resource
{
    protected static ?string $model = WorkflowRun::class;

    protected static \BackedEnum | string | null $navigationIcon = 'heroicon-o-clock';

    protected static string | \UnitEnum | null $navigationGroup = 'Automation';

    protected static ?string $navigationLabel = 'Run History';

    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('workflow.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'running' => 'info',
                        'failed' => 'danger',
                        'pending' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_test')
                    ->boolean()
                    ->label('Test'),
                Tables\Columns\TextColumn::make('steps_count')
                    ->counts('steps')
                    ->label('Steps'),
                Tables\Columns\TextColumn::make('started_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('completed_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('error')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'running' => 'Running',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ]),
                Tables\Filters\TernaryFilter::make('is_test')
                    ->label('Test Runs'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Run Details')
                    ->schema([
                        TextEntry::make('workflow.name')
                            ->label('Workflow'),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'completed' => 'success',
                                'running' => 'info',
                                'failed' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('started_at')
                            ->dateTime(),
                        TextEntry::make('completed_at')
                            ->dateTime(),
                        TextEntry::make('error')
                            ->visible(fn ($record) => $record->error !== null)
                            ->color('danger'),
                    ])->columns(2),

                Section::make('Trigger Data')
                    ->schema([
                        TextEntry::make('trigger_data')
                            ->formatStateUsing(fn ($state) => json_encode($state, JSON_PRETTY_PRINT)),
                    ])
                    ->collapsed(),

                Section::make('Execution Steps')
                    ->schema([
                        RepeatableEntry::make('steps')
                            ->schema([
                                TextEntry::make('step_id')
                                    ->label('Step'),
                                TextEntry::make('action_type')
                                    ->label('Action'),
                                TextEntry::make('status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'completed' => 'success',
                                        'running' => 'info',
                                        'failed' => 'danger',
                                        'skipped' => 'warning',
                                        default => 'gray',
                                    }),
                                TextEntry::make('started_at')
                                    ->dateTime()
                                    ->label('Started'),
                                TextEntry::make('completed_at')
                                    ->dateTime()
                                    ->label('Completed'),
                                TextEntry::make('error')
                                    ->visible(fn ($record) => $record->error !== null)
                                    ->color('danger'),
                            ])
                            ->columns(3),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkflowRuns::route('/'),
            'view' => Pages\ViewWorkflowRun::route('/{record}'),
        ];
    }
}
