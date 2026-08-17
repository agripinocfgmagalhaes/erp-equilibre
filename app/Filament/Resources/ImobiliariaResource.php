<?php
namespace App\Filament\Resources;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\ImobiliariaResource\Pages\ListImobiliarias;
use App\Models\Imobiliaria;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class ImobiliariaResource extends Resource
{
    protected static ?string $model = Imobiliaria::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'Imobiliárias';
    protected static string | \UnitEnum | null $navigationGroup = 'Vendas';
    protected static ?int $navigationSort = 3;
    protected static ?string $slug = 'imobiliarias';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nome')->label('Nome')->required()->maxLength(150)->columnSpanFull(),
            TextInput::make('creci')->label('CRECI')->maxLength(20),
            TextInput::make('telefone')->label('Telefone')->maxLength(20),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('nome')->label('Nome')->searchable()->sortable()->weight('medium'),
            TextColumn::make('creci')->label('CRECI')->searchable()->placeholder('—'),
            TextColumn::make('telefone')->label('Telefone')->placeholder('—'),
            TextColumn::make('corretores_count')->label('Corretores')->counts('corretores'),
        ])
        ->recordActions([EditAction::make()->slideOver(), DeleteAction::make()])
        ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
        ->defaultSort('nome');
    }

    public static function getPages(): array
    {
        return ['index' => ListImobiliarias::route('/')];
    }
}
