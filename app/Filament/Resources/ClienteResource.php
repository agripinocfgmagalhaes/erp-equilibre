<?php
namespace App\Filament\Resources;
use App\Filament\Resources\ClienteResource\Pages;
use App\Filament\Imports\ClienteImporter;
use App\Models\Cliente;
use Filament\Actions\ImportAction;
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
            Forms\Components\Section::make('Dados Pessoais')->schema([
                Forms\Components\TextInput::make('nome')->label('Nome')->required()->maxLength(150)->columnSpanFull(),
                Forms\Components\TextInput::make('cpf')->label('CPF')->maxLength(14)->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('email')->label('E-mail')->email()->maxLength(100),
                Forms\Components\TextInput::make('telefone')->label('Telefone')->maxLength(20),
                Forms\Components\TextInput::make('whatsapp')->label('WhatsApp')->maxLength(20),
                Forms\Components\TextInput::make('profissao')->label('Profissão')->maxLength(100),
                Forms\Components\TextInput::make('renda_familiar')->label('Renda Familiar')->numeric()->prefix('R$'),
                Forms\Components\Select::make('estado_civil')->label('Estado Civil')->options(Cliente::ESTADOS_CIVIS)->native(false)->live(),
            ])->columns(2),
            Forms\Components\Section::make('Dados do Cônjuge')->schema([
                Forms\Components\TextInput::make('conjuge_nome')->label('Nome do Cônjuge')->maxLength(100),
                Forms\Components\TextInput::make('conjuge_cpf')->label('CPF do Cônjuge')->maxLength(14),
                Forms\Components\TextInput::make('conjuge_renda')->label('Renda do Cônjuge')->numeric()->prefix('R$'),
            ])->columns(3)->visible(fn (callable $get) => in_array($get('estado_civil'), ['casado', 'uniao_estavel'])),
            Forms\Components\Section::make('Endereço')->schema([
                Forms\Components\TextInput::make('cep')->label('CEP')->maxLength(9),
                Forms\Components\TextInput::make('logradouro')->label('Logradouro')->maxLength(150)->columnSpan(2),
                Forms\Components\TextInput::make('numero')->label('Número')->maxLength(20),
                Forms\Components\TextInput::make('complemento')->label('Complemento')->maxLength(100),
                Forms\Components\TextInput::make('bairro')->label('Bairro')->maxLength(100),
                Forms\Components\TextInput::make('cidade')->label('Cidade')->maxLength(100),
                Forms\Components\TextInput::make('estado')->label('UF')->maxLength(2),
            ])->columns(4),
            Forms\Components\Textarea::make('observacoes')->label('Observações')->rows(2)->columnSpanFull(),
            Forms\Components\Toggle::make('ativo')->label('Ativo')->default(true),
        ]);
    }
    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('nome')->label('Nome')->searchable()->sortable()->weight('medium'),
            Tables\Columns\TextColumn::make('cpf')->label('CPF')->searchable()->placeholder('—'),
            Tables\Columns\TextColumn::make('whatsapp')->label('WhatsApp')->placeholder('—'),
            Tables\Columns\TextColumn::make('email')->label('E-mail')->searchable()->placeholder('—'),
            Tables\Columns\TextColumn::make('estado_civil')->label('Estado Civil')->formatStateUsing(fn ($state) => Cliente::ESTADOS_CIVIS[$state] ?? '—')->placeholder('—'),
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
