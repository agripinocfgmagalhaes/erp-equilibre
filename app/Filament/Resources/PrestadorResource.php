<?php
namespace App\Filament\Resources;
use App\Filament\Resources\PrestadorResource\Pages;
use App\Filament\Imports\PrestadorImporter;
use App\Models\Prestador;
use Filament\Actions\ImportAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
class PrestadorResource extends Resource
{
    protected static ?string $model = Prestador::class;
    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationLabel = 'Prestadores';
    protected static ?string $navigationGroup = 'Compras';
    protected static ?int $navigationSort = 2;
    protected static ?string $slug = 'prestadores';
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nome')->label('Nome')->required()->maxLength(150)->columnSpanFull(),
            Forms\Components\TextInput::make('cpf_cnpj')->label('CPF/CNPJ')->maxLength(18)->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('especialidade')->label('Especialidade')->maxLength(100),
            Forms\Components\TextInput::make('email')->label('E-mail')->email()->maxLength(100),
            Forms\Components\TextInput::make('telefone')->label('Telefone')->maxLength(20),
            Forms\Components\Textarea::make('observacoes')->label('Observações')->rows(2)->columnSpanFull(),
            Forms\Components\Toggle::make('ativo')->label('Ativo')->default(true),
        ])->columns(2);
    }
    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('nome')->label('Nome')->searchable()->sortable()->weight('medium'),
            Tables\Columns\TextColumn::make('especialidade')->label('Especialidade')->placeholder('—'),
            Tables\Columns\TextColumn::make('telefone')->label('Telefone')->placeholder('—'),
            Tables\Columns\IconColumn::make('ativo')->label('Ativo')->boolean(),
        ])
        ->filters([Tables\Filters\TernaryFilter::make('ativo')->trueLabel('Ativos')->falseLabel('Inativos')])
        ->headerActions([ImportAction::make()->importer(PrestadorImporter::class)->label('Importar Planilha')])
        ->actions([Tables\Actions\EditAction::make()->slideOver(), Tables\Actions\DeleteAction::make()])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
        ->defaultSort('nome')->striped();
    }
    public static function getPages(): array
    {
        return ['index' => Pages\ListPrestadores::route('/'), 'create' => Pages\CreatePrestador::route('/create'), 'edit' => Pages\EditPrestador::route('/{record}/edit')];
    }
}
