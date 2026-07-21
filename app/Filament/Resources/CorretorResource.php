<?php
namespace App\Filament\Resources;
use App\Filament\Resources\CorretorResource\Pages;
use App\Filament\Imports\CorretorImporter;
use App\Models\Corretor;
use Filament\Actions\Imports\ImportAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
class CorretorResource extends Resource
{
    protected static ?string $model = Corretor::class;
    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationLabel = 'Corretores';
    protected static ?string $navigationGroup = 'Vendas';
    protected static ?int $navigationSort = 2;
    protected static ?string $slug = 'corretores';
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nome')->label('Nome')->required()->maxLength(150)->columnSpanFull(),
            Forms\Components\TextInput::make('cpf_cnpj')->label('CPF/CNPJ')->maxLength(18)->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('creci')->label('CRECI')->maxLength(20),
            Forms\Components\TextInput::make('email')->label('E-mail')->email()->maxLength(100),
            Forms\Components\TextInput::make('telefone')->label('Telefone')->maxLength(20),
            Forms\Components\TextInput::make('celular')->label('Celular')->maxLength(20),
            Forms\Components\Textarea::make('observacoes')->label('Observações')->rows(2)->columnSpanFull(),
            Forms\Components\Toggle::make('ativo')->label('Ativo')->default(true),
        ])->columns(2);
    }
    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('nome')->label('Nome')->searchable()->sortable()->weight('medium'),
            Tables\Columns\TextColumn::make('creci')->label('CRECI')->placeholder('—'),
            Tables\Columns\TextColumn::make('celular')->label('Celular')->placeholder('—'),
            Tables\Columns\IconColumn::make('ativo')->label('Ativo')->boolean(),
        ])
        ->filters([Tables\Filters\TernaryFilter::make('ativo')->trueLabel('Ativos')->falseLabel('Inativos')])
        ->headerActions([ImportAction::make()->importer(CorretorImporter::class)->label('Importar Planilha')])
        ->actions([Tables\Actions\EditAction::make()->slideOver(), Tables\Actions\DeleteAction::make()])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
        ->defaultSort('nome')->striped();
    }
    public static function getPages(): array
    {
        return ['index' => Pages\ListCorretores::route('/'), 'create' => Pages\CreateCorretor::route('/create'), 'edit' => Pages\EditCorretor::route('/{record}/edit')];
    }
}
