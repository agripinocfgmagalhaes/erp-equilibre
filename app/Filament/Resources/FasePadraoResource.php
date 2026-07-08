<?php
namespace App\Filament\Resources;
use App\Filament\Resources\FasePadraoResource\Pages;
use App\Models\FasePadrao;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
class FasePadraoResource extends Resource
{
    protected static ?string $model = FasePadrao::class;
    protected static ?string $navigationIcon = 'heroicon-o-queue-list';
    protected static ?string $navigationLabel = 'Fases Padrão';
    protected static ?string $navigationGroup = 'Configurações';
    protected static ?int $navigationSort = 4;
    protected static ?string $slug = 'fases-padrao';
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nome')->label('Nome')->required()->maxLength(100)->columnSpanFull(),
            Forms\Components\TextInput::make('macro_categoria')->label('Macro Categoria')->maxLength(100),
            Forms\Components\TextInput::make('ordem')->label('Ordem')->numeric()->default(0),
        ])->columns(2);
    }
    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('ordem')->label('Ordem')->sortable(),
            Tables\Columns\TextColumn::make('nome')->label('Nome')->searchable()->weight('medium'),
            Tables\Columns\TextColumn::make('macro_categoria')->label('Macro Categoria')->placeholder('—'),
        ])
        ->actions([Tables\Actions\EditAction::make()->slideOver(), Tables\Actions\DeleteAction::make()])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
        ->defaultSort('ordem')->striped();
    }
    public static function getPages(): array
    {
        return ['index' => Pages\ListFasesPadrao::route('/'), 'create' => Pages\CreateFasePadrao::route('/create'), 'edit' => Pages\EditFasePadrao::route('/{record}/edit')];
    }
}
