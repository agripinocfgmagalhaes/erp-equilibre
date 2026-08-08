<?php
namespace App\Filament\Resources;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\ServicoResource\Pages\ListServicos;
use App\Filament\Resources\ServicoResource\Pages\CreateServico;
use App\Filament\Resources\ServicoResource\Pages\EditServico;
use App\Models\Servico;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class ServicoResource extends Resource
{
    protected static ?string $model = Servico::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationLabel = 'Catálogo de Serviços';
    protected static string | \UnitEnum | null $navigationGroup = 'Configurações';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?int $navigationSort = 5;
    protected static ?string $slug = 'servicos';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nome')->label('Nome do Serviço')->required()->maxLength(150)->columnSpanFull(),
            TextInput::make('unidade_padrao')->label('Unidade Padrão')->maxLength(10)->helperText('Ex: m², m³, un, kg'),
            Toggle::make('ativo')->label('Ativo')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('nome')->sortable()->label('Nome')->searchable()->weight('medium'),
            TextColumn::make('unidade_padrao')->sortable()->label('Unidade')->placeholder('—'),
            IconColumn::make('ativo')->sortable()->label('Ativo')->boolean(),
        ])
        ->filters([TernaryFilter::make('ativo')->trueLabel('Ativos')->falseLabel('Inativos')])
        ->recordActions([EditAction::make()->slideOver(), DeleteAction::make()])
        ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
        ->defaultSort('nome')->dragReorderableColumns()->stickableColumns();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServicos::route('/'),
            'create' => CreateServico::route('/create'),
            'edit' => EditServico::route('/{record}/edit'),
        ];
    }
}
