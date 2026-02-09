<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkflowSecretResource\Pages;
use App\Models\WorkflowSecret;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Crypt;

class WorkflowSecretResource extends Resource
{
    protected static ?string $model = WorkflowSecret::class;

    protected static \BackedEnum | string | null $navigationIcon = 'heroicon-o-key';

    protected static string | \UnitEnum | null $navigationGroup = 'Automation';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->helperText('Use this name in workflows as {{secret.name}}'),
                TextInput::make('value')
                    ->label('Secret Value')
                    ->password()
                    ->revealable()
                    ->required()
                    ->dehydrateStateUsing(fn (string $state): string => Crypt::encryptString($state))
                    ->dehydrated(fn (?string $state): bool => filled($state)),
                TextInput::make('description')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Copied!')
                    ->copyMessageDuration(1500)
                    ->formatStateUsing(fn (string $state): string => "{{secret.{$state}}}"),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkflowSecrets::route('/'),
            'create' => Pages\CreateWorkflowSecret::route('/create'),
            'edit' => Pages\EditWorkflowSecret::route('/{record}/edit'),
        ];
    }
}
