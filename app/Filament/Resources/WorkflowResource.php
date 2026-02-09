<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkflowResource\Pages;
use App\Models\Workflow;
use App\Workflows\WorkflowRegistry;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class WorkflowResource extends Resource
{
    protected static ?string $model = Workflow::class;

    protected static \BackedEnum | string | null $navigationIcon = 'heroicon-o-bolt';

    protected static string | \UnitEnum | null $navigationGroup = 'Automation';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Workflow Details')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->rows(2)
                            ->maxLength(1000),
                        Grid::make(3)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(false),
                                Toggle::make('test_mode')
                                    ->label('Test Mode')
                                    ->helperText('Dry-run without side effects'),
                            ]),
                    ]),

                Section::make('Trigger')
                    ->schema([
                        Select::make('trigger_type')
                            ->options(WorkflowRegistry::triggerOptions())
                            ->required()
                            ->live(),
                        KeyValue::make('trigger_config')
                            ->label('Trigger Configuration')
                            ->helperText('Key-value pairs for trigger settings'),
                    ]),

                Section::make('Rate Limits')
                    ->collapsed()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('max_runs_per_minute')
                                    ->numeric()
                                    ->default(60),
                                TextInput::make('max_concurrent_runs')
                                    ->numeric()
                                    ->default(10),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('trigger_type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => WorkflowRegistry::getTrigger($state)?->getLabel() ?? $state),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                Tables\Columns\IconColumn::make('test_mode')
                    ->boolean()
                    ->label('Test'),
                Tables\Columns\TextColumn::make('runs_count')
                    ->counts('runs')
                    ->label('Runs'),
                Tables\Columns\TextColumn::make('latestRun.status')
                    ->label('Last Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'running' => 'info',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\Action::make('run')
                    ->label('Run Now')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Workflow $record) {
                        \App\Jobs\ExecuteWorkflowJob::dispatch($record, ['triggered_by' => 'manual', 'triggered_at' => now()->toIso8601String()]);

                        \Filament\Notifications\Notification::make()
                            ->title('Workflow dispatched')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkflows::route('/'),
            'create' => Pages\CreateWorkflow::route('/create'),
            'edit' => Pages\EditWorkflow::route('/{record}/edit'),
            'builder' => Pages\WorkflowBuilder::route('/{record}/builder'),
        ];
    }
}
