<?php
namespace App\Filament\Resources;
use App\Filament\Resources\FornecedorResource\Pages;
use App\Models\Fornecedor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
class FornecedorResource extends Resource
{
    protected static ?string $model = Fornecedor::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Fornecedores';
    protected static ?string $navigationGroup = 'Compras';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'fornecedores';
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nome')->label('Nome')->required()->maxLength(150)->columnSpanFull(),
            Forms\Components\TextInput::make('cnpj')->label('CNPJ')->maxLength(18)->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('email')->label('E-mail')->email()->maxLength(100),
            Forms\Components\TextInput::make('telefone')->label('Telefone')->maxLength(20),
            Forms\Components\TextInput::make('contato')->label('Contato')->maxLength(100),
            Forms\Components\Textarea::make('observacoes')->label('Observações')->rows(2)->columnSpanFull(),
            Forms\Components\Toggle::make('ativo')->label('Ativo')->default(true),
        ])->columns(2);
    }
    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('nome')->label('Nome')->searchable()->sortable()->weight('medium'),
            Tables\Columns\TextColumn::make('cnpj')->label('CNPJ')->placeholder('—'),
            Tables\Columns\TextColumn::make('telefone')->label('Telefone')->placeholder('—'),
            Tables\Columns\TextColumn::make('contato')->label('Contato')->placeholder('—'),
            Tables\Columns\IconColumn::make('ativo')->label('Ativo')->boolean(),
        ])
        ->filters([Tables\Filters\TernaryFilter::make('ativo')->trueLabel('Ativos')->falseLabel('Inativos')])
        ->actions([Tables\Actions\EditAction::make()->slideOver(), Tables\Actions\DeleteAction::make()])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
        ->defaultSort('nome')->striped();
    }
    public static function getPages(): array
    {
        return ['index' => Pages\ListFornecedores::route('/'), 'create' => Pages\CreateFornecedor::route('/create'), 'edit' => Pages\EditFornecedor::route('/{record}/edit')];
    }
}
