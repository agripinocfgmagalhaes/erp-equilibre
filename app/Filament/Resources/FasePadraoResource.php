<?php
namespace App\Filament\Resources;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\FasePadraoResource\Pages\ListFasesPadrao;
use App\Filament\Resources\FasePadraoResource\Pages\CreateFasePadrao;
use App\Filament\Resources\FasePadraoResource\Pages\EditFasePadrao;
use App\Filament\Resources\FasePadraoResource\Pages;
use App\Models\FasePadrao;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
class FasePadraoResource extends Resource
{
    protected static ?string $model = FasePadrao::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-queue-list';
    protected static ?string $navigationLabel = 'Fases Padrão';
    protected static string | \UnitEnum | null $navigationGroup = 'Configurações';
    protected static ?int $navigationSort = 4;
    protected static ?string $slug = 'fases-padrao';
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nome')->label('Nome')->required()->maxLength(100)->columnSpanFull(),
            TextInput::make('macro_categoria')->label('Macro Categoria')->maxLength(100),
            TextInput::make('ordem')->label('Ordem')->numeric()->default(0),
        ])->columns(2);
    }
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('ordem')->label('Ordem')->sortable(),
            TextColumn::make('nome')->label('Nome')->searchable()->weight('medium'),
            TextColumn::make('macro_categoria')->label('Macro Categoria')->placeholder('—'),
        ])
        ->recordActions([EditAction::make()->slideOver(), DeleteAction::make()])
        ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
        ->defaultSort('ordem')->striped();
    }
    public static function getPages(): array
    {
        return ['index' => ListFasesPadrao::route('/'), 'create' => CreateFasePadrao::route('/create'), 'edit' => EditFasePadrao::route('/{record}/edit')];
    }
}
