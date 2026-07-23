<?php
namespace App\Filament\Resources;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
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
use App\Filament\Resources\ClienteResource\Pages\ListClientes;
use App\Filament\Resources\ClienteResource\Pages\CreateCliente;
use App\Filament\Resources\ClienteResource\Pages\EditCliente;
use App\Filament\Resources\ClienteResource\Pages;
use App\Filament\Imports\ClienteImporter;
use App\Models\Cliente;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
class ClienteResource extends Resource
{
    protected static ?string $model = Cliente::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Clientes';
    protected static string | \UnitEnum | null $navigationGroup = 'Vendas';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'clientes';
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Dados Pessoais')->schema([
                TextInput::make('nome')->label('Nome')->required()->maxLength(150)->columnSpanFull(),
                TextInput::make('cpf')->label('CPF')->maxLength(14)->unique(ignoreRecord: true),
                TextInput::make('email')->label('E-mail')->email()->maxLength(100),
                TextInput::make('telefone')->label('Telefone')->maxLength(20),
                TextInput::make('whatsapp')->label('WhatsApp')->maxLength(20),
                TextInput::make('profissao')->label('Profissão')->maxLength(100),
                TextInput::make('renda_familiar')->label('Renda Familiar')->numeric()->prefix('R$'),
                Select::make('estado_civil')->label('Estado Civil')->options(Cliente::ESTADOS_CIVIS)->native(false)->live(),
            ])->columns(2),
            Section::make('Dados do Cônjuge')->schema([
                TextInput::make('conjuge_nome')->label('Nome do Cônjuge')->maxLength(100),
                TextInput::make('conjuge_cpf')->label('CPF do Cônjuge')->maxLength(14),
                TextInput::make('conjuge_profissao')->label('Profissão do Cônjuge')->maxLength(100),
                TextInput::make('conjuge_email')->label('E-mail do Cônjuge')->email()->maxLength(100),
                TextInput::make('conjuge_telefone')->label('Telefone do Cônjuge')->maxLength(20),
                TextInput::make('conjuge_renda')->label('Renda do Cônjuge')->numeric()->prefix('R$'),
            ])->columns(3)->visible(fn (callable $get) => in_array($get('estado_civil'), ['casado', 'uniao_estavel'])),
            Section::make('Endereço')->schema([
                TextInput::make('cep')->label('CEP')->maxLength(9),
                TextInput::make('logradouro')->label('Logradouro')->maxLength(150)->columnSpan(2),
                TextInput::make('numero')->label('Número')->maxLength(20),
                TextInput::make('complemento')->label('Complemento')->maxLength(100),
                TextInput::make('bairro')->label('Bairro')->maxLength(100),
                TextInput::make('cidade')->label('Cidade')->maxLength(100),
                TextInput::make('estado')->label('UF')->maxLength(2),
            ])->columns(4),
            Textarea::make('observacoes')->label('Observações')->rows(2)->columnSpanFull(),
            Toggle::make('ativo')->label('Ativo')->default(true),
        ]);
    }
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('nome')->label('Nome')->searchable()->sortable()->weight('medium'),
            TextColumn::make('cpf')->label('CPF')->searchable()->placeholder('—'),
            TextColumn::make('whatsapp')->label('WhatsApp')->placeholder('—'),
            TextColumn::make('email')->label('E-mail')->searchable()->placeholder('—'),
            TextColumn::make('estado_civil')->label('Estado Civil')->formatStateUsing(fn ($state) => Cliente::ESTADOS_CIVIS[$state] ?? '—')->placeholder('—'),
            IconColumn::make('ativo')->label('Ativo')->boolean(),
        ])
        ->filters([TernaryFilter::make('ativo')->trueLabel('Ativos')->falseLabel('Inativos')])
        ->headerActions([ImportAction::make()->importer(ClienteImporter::class)->label('Importar Planilha')])
        ->recordActions([EditAction::make()->slideOver(), DeleteAction::make()])
        ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
        ->defaultSort('nome')->striped();
    }
    public static function getPages(): array
    {
        return ['index' => ListClientes::route('/'), 'create' => CreateCliente::route('/create'), 'edit' => EditCliente::route('/{record}/edit')];
    }
}
