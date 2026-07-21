<?php
namespace App\Filament\Resources;
use App\Filament\Resources\ClienteResource\Pages;
use App\Filament\Imports\ClienteImporter;
use App\Models\Cliente;
use Filament\Actions\Imports\ImportAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
class ClienteResource extends Resource
{
    protected static ?string $model = Cliente::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Clientes';
    protected static ?string $navigationGroup = 'Vendas';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'clientes';
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nome')->label('Nome')->required()->maxLength(150)->columnSpanFull(),
            Forms\Components\TextInput::make('cpf')->label('CPF')->maxLength(14)->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('email')->label('E-mail')->email()->maxLength(100),
            Forms\Components\TextInput::make('telefone')->label('Telefone')->maxLength(20),
            Forms\Components\TextInput::make('celular')->label('Celular')->maxLength(20),
            Forms\Components\TextInput::make('cep')->label('CEP')->maxLength(9),
            Forms\Components\TextInput::make('endereco')->label('Endereço')->maxLength(200)->columnSpanFull(),
            Forms\Components\TextInput::make('bairro')->label('Bairro')->maxLength(100),
            Forms\Components\TextInput::make('cidade')->label('Cidade')->maxLength(100),
            Forms\Components\TextInput::make('estado')->label('UF')->maxLength(2),
            Forms\Components\Textarea::make('observacoes')->label('Observações')->rows(2)->columnSpanFull(),
            Forms\Components\Toggle::make('ativo')->label('Ativo')->default(true),
        ])->columns(2);
    }
    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('nome')->label('Nome')->searchable()->sortable()->weight('medium'),
            Tables\Columns\TextColumn::make('cpf')->label('CPF')->searchable()->placeholder('—'),
            Tables\Columns\TextColumn::make('celular')->label('Celular')->placeholder('—'),
            Tables\Columns\TextColumn::make('email')->label('E-mail')->searchable()->placeholder('—'),
            Tables\Columns\IconColumn::make('ativo')->label('Ativo')->boolean(),
        ])
        ->filters([Tables\Filters\TernaryFilter::make('ativo')->trueLabel('Ativos')->falseLabel('Inativos')])
        ->headerActions([ImportAction::make()->importer(ClienteImporter::class)->label('Importar Planilha')])
        ->actions([Tables\Actions\EditAction::make()->slideOver(), Tables\Actions\DeleteAction::make()])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
        ->defaultSort('nome')->striped();
    }
    public static function getPages(): array
    {
        return ['index' => Pages\ListClientes::route('/'), 'create' => Pages\CreateCliente::route('/create'), 'edit' => Pages\EditCliente::route('/{record}/edit')];
    }
}
