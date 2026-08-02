<?php
namespace App\Filament\Resources;

use App\Models\Unidade;
use App\Models\Projeto;
use App\Filament\Resources\UnidadeResource\Pages\ListUnidades;
use App\Filament\Resources\UnidadeResource\Pages\CreateUnidade;
use App\Filament\Resources\UnidadeResource\Pages\EditUnidade;
use App\Filament\Resources\UnidadeResource\Pages;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UnidadeResource extends Resource
{
    protected static ?string $model = Unidade::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-home-modern';
    protected static ?string $navigationLabel = 'Unidades';
    protected static string | \UnitEnum | null $navigationGroup = 'Vendas';
    protected static ?int $navigationSort = 2;
    protected static ?string $slug = 'unidades';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('projeto_id')->label('Projeto')->options(Projeto::pluck('nome', 'id'))->searchable()->native(false)->required(),
            TextInput::make('identificacao')->label('Identificação')->required()->maxLength(20),
            TextInput::make('tipo')->label('Tipo')->maxLength(50),
            TextInput::make('area')->label('Área (m²)')->numeric()->step(0.01),
            TextInput::make('valor_tabela')->label('Valor de Tabela')->numeric()->prefix('R$')->step(0.01)->default(0),
            Select::make('status')->label('Status')->native(false)->default('disponivel')
                ->options(['disponivel' => 'Disponível', 'reservado' => 'Reservado', 'vendido' => 'Vendido', 'distratado' => 'Distratado']),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('projeto'))
            ->columns([
                TextColumn::make('identificacao')->label('Identificação')->searchable()->sortable()->weight('medium'),
                TextColumn::make('projeto.nome')->label('Projeto')->searchable()->sortable()->placeholder('—')->badge()->color(fn ($record) => $record->projeto->cor ?? 'gray'),
                TextColumn::make('tipo')->label('Tipo')->searchable()->sortable()->placeholder('—'),
                TextColumn::make('area')->label('Área (m²)')->numeric(decimalPlaces: 2)->sortable()->placeholder('—'),
                TextColumn::make('valor_tabela')->label('Valor de Tabela')->money('BRL')->alignEnd()->sortable(),
                TextColumn::make('status')->label('Status')->badge()->sortable()
                    ->colors(['success' => 'disponivel', 'warning' => 'reservado', 'gray' => 'vendido', 'danger' => 'distratado'])
                    ->formatStateUsing(fn ($state) => ['disponivel' => 'Disponível', 'reservado' => 'Reservado', 'vendido' => 'Vendido', 'distratado' => 'Distratado'][$state] ?? $state),
            ])
            ->filters([
                SelectFilter::make('status')->options(['disponivel' => 'Disponível', 'reservado' => 'Reservado', 'vendido' => 'Vendido', 'distratado' => 'Distratado']),
                SelectFilter::make('projeto_id')->label('Projeto')->options(Projeto::pluck('nome', 'id'))->searchable(),
            ])
            ->recordActions([
                EditAction::make()->slideOver()->iconButton(),
                DeleteAction::make()->iconButton(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('identificacao');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUnidades::route('/'),
            'create' => CreateUnidade::route('/create'),
            'edit' => EditUnidade::route('/{record}/edit'),
        ];
    }
}
