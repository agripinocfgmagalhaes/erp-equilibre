<?php
namespace App\Filament\Resources\ProjetoResource\RelationManagers;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class FasesObraRelationManager extends RelationManager
{
    protected static string $relationship = 'fasesObra';
    protected static ?string $title = 'Fases da Obra';
    protected static ?string $modelLabel = 'Fase';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nome')->label('Nome')->required()->maxLength(100),
            TextInput::make('ordem')->label('Ordem')->numeric()->default(0),
            TextInput::make('peso')->label('Peso')->numeric()->step(0.01)->default(0)->suffix('%')
                ->helperText('Peso da fase no avanço físico total. Some 100% entre as fases.'),
            TextInput::make('percentual')->label('% Conclusão')->numeric()->step(0.01)->default(0)->minValue(0)->maxValue(100)->suffix('%'),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nome')
            ->columns([
                TextColumn::make('ordem')->label('Ordem')->sortable()->width('80px'),
                TextColumn::make('nome')->label('Fase')->searchable()->weight('medium'),
                TextColumn::make('peso')->label('Peso')->suffix('%')->alignEnd()->sortable(),
                TextColumn::make('percentual')->label('% Conclusão')->suffix('%')->alignEnd()->sortable()
                    ->badge()->color(fn ($state) => match (true) { $state >= 100 => 'success', $state >= 50 => 'info', $state > 0 => 'warning', default => 'gray' }),
            ])
            ->headerActions([CreateAction::make()->label('+ Fase')->slideOver()])
            ->recordActions([
                EditAction::make()->slideOver()->iconButton(),
                DeleteAction::make()->iconButton(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->reorderable('ordem')
            ->defaultSort('ordem');
    }
}
