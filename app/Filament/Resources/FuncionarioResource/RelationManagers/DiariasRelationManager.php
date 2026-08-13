<?php
namespace App\Filament\Resources\FuncionarioResource\RelationManagers;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
class DiariasRelationManager extends RelationManager
{
    protected static string $relationship = 'diarias';
    protected static ?string $title = 'Diárias';
    protected static ?string $modelLabel = 'Diária';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('valor_diaria')->label('Valor da Diária')->numeric()->prefix('R$')->required(),
            DatePicker::make('vigente_desde')->label('Vigente Desde')->required()->native(false)->displayFormat('d/m/Y'),
            DatePicker::make('vigente_ate')->label('Vigente Até')->native(false)->displayFormat('d/m/Y'),
        ])->columns(3);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('valor_diaria')
            ->columns([
                TextColumn::make('valor_diaria')->label('Valor')->money('BRL')->sortable(),
                TextColumn::make('vigente_desde')->label('Desde')->date('d/m/Y')->sortable(),
                TextColumn::make('vigente_ate')->label('Até')->date('d/m/Y')->placeholder('Atual'),
            ])
            ->headerActions([CreateAction::make()->label('+ Diária')])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('vigente_desde', 'desc');
    }
}
