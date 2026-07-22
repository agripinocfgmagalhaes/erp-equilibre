<?php
namespace App\Filament\Resources;
use App\Filament\Resources\ProdutoResource\Pages;
use App\Filament\Imports\ProdutoImporter;
use App\Models\Produto;
use Filament\Tables\Actions\ImportAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
class ProdutoResource extends Resource
{
    protected static ?string $model = Produto::class;
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationLabel = 'Produtos';
    protected static ?string $navigationGroup = 'Compras';
    protected static ?int $navigationSort = 3;
    protected static ?string $slug = 'produtos';
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('codigo')->label('Código')->maxLength(30)->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('nome')->label('Nome')->required()->maxLength(150)->columnSpanFull(),
            Forms\Components\TextInput::make('unidade')->label('Unidade')->default('UN')->maxLength(10),
            Forms\Components\TextInput::make('categoria')->label('Categoria')->maxLength(100),
            Forms\Components\TextInput::make('preco_referencia')->label('Preço de Referência')->numeric()->prefix('R$')->step(0.01)->default(0),
            Forms\Components\Toggle::make('ativo')->label('Ativo')->default(true),
        ])->columns(2);
    }
    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('codigo')->label('Código')->searchable()->placeholder('—'),
            Tables\Columns\TextColumn::make('nome')->label('Nome')->searchable()->weight('medium'),
            Tables\Columns\TextColumn::make('categoria')->label('Categoria')->placeholder('—'),
            Tables\Columns\TextColumn::make('unidade')->label('Unid.'),
            Tables\Columns\TextColumn::make('preco_referencia')->label('Preço Ref.')->money('BRL'),
            Tables\Columns\IconColumn::make('ativo')->label('Ativo')->boolean(),
        ])
        ->filters([Tables\Filters\TernaryFilter::make('ativo')->trueLabel('Ativos')->falseLabel('Inativos')])
        ->headerActions([ImportAction::make()->importer(ProdutoImporter::class)->label('Importar Planilha')])
        ->actions([Tables\Actions\EditAction::make()->slideOver(), Tables\Actions\DeleteAction::make()])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
        ->defaultSort('nome')->striped();
    }
    public static function getPages(): array
    {
        return ['index' => Pages\ListProdutos::route('/'), 'create' => Pages\CreateProduto::route('/create'), 'edit' => Pages\EditProduto::route('/{record}/edit')];
    }
}
