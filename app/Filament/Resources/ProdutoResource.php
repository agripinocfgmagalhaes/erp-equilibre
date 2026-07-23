<?php
namespace App\Filament\Resources;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\ImportAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\ProdutoResource\Pages\ListProdutos;
use App\Filament\Resources\ProdutoResource\Pages\CreateProduto;
use App\Filament\Resources\ProdutoResource\Pages\EditProduto;
use App\Filament\Resources\ProdutoResource\Pages;
use App\Filament\Imports\ProdutoImporter;
use App\Models\Produto;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
class ProdutoResource extends Resource
{
    protected static ?string $model = Produto::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationLabel = 'Produtos';
    protected static string | \UnitEnum | null $navigationGroup = 'Compras';
    protected static ?int $navigationSort = 3;
    protected static ?string $slug = 'produtos';
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('codigo')->label('Código')->maxLength(30)->unique(ignoreRecord: true),
            TextInput::make('nome')->label('Nome')->required()->maxLength(150)->columnSpanFull(),
            TextInput::make('unidade')->label('Unidade')->default('UN')->maxLength(10),
            TextInput::make('categoria')->label('Categoria')->maxLength(100),
            TextInput::make('preco_referencia')->label('Preço de Referência')->numeric()->prefix('R$')->step(0.01)->default(0),
            Toggle::make('ativo')->label('Ativo')->default(true),
        ])->columns(2);
    }
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('codigo')->label('Código')->searchable()->placeholder('—'),
            TextColumn::make('nome')->label('Nome')->searchable()->weight('medium'),
            TextColumn::make('categoria')->label('Categoria')->placeholder('—'),
            TextColumn::make('unidade')->label('Unid.'),
            TextColumn::make('preco_referencia')->label('Preço Ref.')->money('BRL'),
            IconColumn::make('ativo')->label('Ativo')->boolean(),
        ])
        ->filters([TernaryFilter::make('ativo')->trueLabel('Ativos')->falseLabel('Inativos')])
        ->headerActions([ImportAction::make()->importer(ProdutoImporter::class)->label('Importar Planilha')])
        ->recordActions([EditAction::make()->slideOver(), DeleteAction::make()])
        ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
        ->defaultSort('nome')->striped();
    }
    public static function getPages(): array
    {
        return ['index' => ListProdutos::route('/'), 'create' => CreateProduto::route('/create'), 'edit' => EditProduto::route('/{record}/edit')];
    }
}
