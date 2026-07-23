<?php
namespace App\Filament\Resources;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\ImportAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\FornecedorResource\Pages\ListFornecedores;
use App\Filament\Resources\FornecedorResource\Pages\CreateFornecedor;
use App\Filament\Resources\FornecedorResource\Pages\EditFornecedor;
use App\Filament\Resources\FornecedorResource\Pages;
use App\Filament\Imports\FornecedorImporter;
use App\Models\Fornecedor;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
class FornecedorResource extends Resource
{
    protected static ?string $model = Fornecedor::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Fornecedores';
    protected static string | \UnitEnum | null $navigationGroup = 'Compras';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'fornecedores';
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nome')->label('Nome')->required()->maxLength(150)->columnSpanFull(),
            TextInput::make('cnpj')->label('CNPJ')->maxLength(18)->unique(ignoreRecord: true),
            TextInput::make('email')->label('E-mail')->email()->maxLength(100),
            TextInput::make('telefone')->label('Telefone')->maxLength(20),
            TextInput::make('contato')->label('Contato')->maxLength(100),
            Textarea::make('observacoes')->label('Observações')->rows(2)->columnSpanFull(),
            Toggle::make('ativo')->label('Ativo')->default(true),
        ])->columns(2);
    }
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('nome')->label('Nome')->searchable()->sortable()->weight('medium'),
            TextColumn::make('cnpj')->label('CNPJ')->placeholder('—'),
            TextColumn::make('telefone')->label('Telefone')->placeholder('—'),
            TextColumn::make('contato')->label('Contato')->placeholder('—'),
            IconColumn::make('ativo')->label('Ativo')->boolean(),
        ])
        ->filters([TernaryFilter::make('ativo')->trueLabel('Ativos')->falseLabel('Inativos')])
        ->headerActions([ImportAction::make()->importer(FornecedorImporter::class)->label('Importar Planilha')])
        ->recordActions([EditAction::make()->slideOver(), DeleteAction::make()])
        ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
        ->defaultSort('nome')->striped();
    }
    public static function getPages(): array
    {
        return ['index' => ListFornecedores::route('/'), 'create' => CreateFornecedor::route('/create'), 'edit' => EditFornecedor::route('/{record}/edit')];
    }
}
