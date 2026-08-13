<?php
namespace App\Filament\Resources;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\FuncionarioResource\Pages\ListFuncionarios;
use App\Filament\Resources\FuncionarioResource\Pages\CreateFuncionario;
use App\Filament\Resources\FuncionarioResource\Pages\EditFuncionario;
use App\Filament\Resources\FuncionarioResource\RelationManagers\DiariasRelationManager;
use App\Models\Funcionario;
use Filament\Resources\Resource;
use Filament\Tables\Table;
class FuncionarioResource extends Resource
{
    protected static ?string $model = Funcionario::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Funcionários';
    protected static string | \UnitEnum | null $navigationGroup = 'Pessoal';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'funcionarios';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nome')->label('Nome')->required()->maxLength(150)->columnSpanFull(),
            TextInput::make('cpf')->label('CPF')->required()->maxLength(14)->unique(ignoreRecord: true),
            TextInput::make('telefone')->label('Telefone')->maxLength(20),
            Select::make('tipo_chave_pix')->label('Tipo Chave Pix')->options(['cpf' => 'CPF', 'cnpj' => 'CNPJ', 'telefone' => 'Telefone', 'email' => 'E-mail', 'aleatoria' => 'Aleatória'])->required(),
            TextInput::make('chave_pix')->label('Chave Pix')->required()->maxLength(150),
            TextInput::make('funcao')->label('Função')->maxLength(100),
            Select::make('projeto_id')->label('Obra')->relationship('projeto', 'nome')->searchable()->preload(),
            DatePicker::make('data_entrada')->label('Data de Entrada')->required()->native(false)->displayFormat('d/m/Y'),
            DatePicker::make('data_saida')->label('Data de Saída')->native(false)->displayFormat('d/m/Y'),
            Select::make('status')->label('Status')->options(['pendente' => 'Pendente', 'ativo' => 'Ativo', 'inativo' => 'Inativo'])->default('pendente')->required(),
            FileUpload::make('foto_documento_path')->label('Foto do Documento')->disk('local')->directory('documentos-funcionarios')->visibility('private')->image()->columnSpanFull(),
            Textarea::make('comentarios')->label('Comentários')->rows(2)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('nome')->label('Nome')->searchable()->sortable()->weight('medium'),
            TextColumn::make('cpf')->label('CPF')->searchable(),
            TextColumn::make('funcao')->label('Função')->placeholder('—'),
            TextColumn::make('projeto.nome')->label('Obra')->placeholder('—'),
            TextColumn::make('status')->label('Status')->badge()
                ->colors(['warning' => 'pendente', 'success' => 'ativo', 'gray' => 'inativo'])
                ->formatStateUsing(fn ($s) => ['pendente' => 'Pendente', 'ativo' => 'Ativo', 'inativo' => 'Inativo'][$s] ?? $s),
            TextColumn::make('data_entrada')->label('Entrada')->date('d/m/Y')->sortable(),
        ])
        ->filters([SelectFilter::make('status')->options(['pendente' => 'Pendente', 'ativo' => 'Ativo', 'inativo' => 'Inativo'])])
        ->recordActions([
            Action::make('aprovar')->label('Aprovar')->icon('heroicon-o-check')->color('success')->iconButton()
                ->visible(fn ($record) => $record->status === 'pendente')
                ->requiresConfirmation()
                ->action(fn ($record) => $record->update(['status' => 'ativo'])),
            EditAction::make()->slideOver(),
            DeleteAction::make(),
        ])
        ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
        ->defaultSort('nome');
    }

    public static function getRelations(): array { return [DiariasRelationManager::class]; }

    public static function getPages(): array
    {
        return [
            'index' => ListFuncionarios::route('/'),
            'create' => CreateFuncionario::route('/create'),
            'edit' => EditFuncionario::route('/{record}/edit'),
        ];
    }
}
