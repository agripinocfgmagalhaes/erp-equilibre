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
use App\Filament\Resources\PrestadorResource\Pages\ListPrestadores;
use App\Filament\Resources\PrestadorResource\Pages\CreatePrestador;
use App\Filament\Resources\PrestadorResource\Pages\EditPrestador;
use App\Filament\Resources\PrestadorResource\Pages;
use App\Filament\Imports\PrestadorImporter;
use App\Models\Prestador;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
class PrestadorResource extends Resource
{
    protected static ?string $model = Prestador::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationLabel = 'Prestadores';
    protected static string | \UnitEnum | null $navigationGroup = 'Compras';
    protected static ?int $navigationSort = 2;
    protected static ?string $slug = 'prestadores';
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nome')->label('Nome')->required()->maxLength(150)->columnSpanFull(),
            TextInput::make('cpf_cnpj')->label('CPF/CNPJ')->maxLength(18)->unique(ignoreRecord: true),
            TextInput::make('especialidade')->label('Especialidade')->maxLength(100),
            TextInput::make('email')->label('E-mail')->email()->maxLength(100),
            TextInput::make('telefone')->label('Telefone')->maxLength(20),
            Textarea::make('observacoes')->label('Observações')->rows(2)->columnSpanFull(),
            Toggle::make('ativo')->label('Ativo')->default(true),
        ])->columns(2);
    }
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('nome')->label('Nome')->searchable()->sortable()->weight('medium'),
            TextColumn::make('especialidade')->label('Especialidade')->placeholder('—'),
            TextColumn::make('telefone')->label('Telefone')->placeholder('—'),
            IconColumn::make('ativo')->label('Ativo')->boolean(),
        ])
        ->filters([TernaryFilter::make('ativo')->trueLabel('Ativos')->falseLabel('Inativos')])
        ->headerActions([ImportAction::make()->importer(PrestadorImporter::class)->label('Importar Planilha')])
        ->recordActions([EditAction::make()->slideOver(), DeleteAction::make()])
        ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
        ->defaultSort('nome')->striped();
    }
    public static function getPages(): array
    {
        return ['index' => ListPrestadores::route('/'), 'create' => CreatePrestador::route('/create'), 'edit' => EditPrestador::route('/{record}/edit')];
    }
}
