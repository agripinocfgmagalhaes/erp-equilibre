<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnidadeResource\Pages\ListUnidades;
use App\Models\Projeto;
use App\Models\Unidade;
use Filament\Notifications\Notification;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
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
            TextInput::make('identificacao')->label('Identificação')->required()->maxLength(50),
            TextInput::make('tipo')->label('Tipo')->maxLength(100),
            TextInput::make('tipologia')->label('Tipologia')->maxLength(100),
            TextInput::make('andar')->label('Andar')->maxLength(100),
            TextInput::make('vaga_garagem')->label('Vaga de Garagem')->maxLength(50),
            TextInput::make('area')->label('Área Privativa (m²)')->numeric()->step(0.01),
            TextInput::make('valor_tabela')->label('Valor de Tabela')->numeric()->prefix('R$')->step(0.01),
            TextInput::make('valor_avaliado')->label('Valor Avaliado')->numeric()->prefix('R$')->step(0.01),
            Select::make('status')->label('Status')->native(false)->default('disponivel')
                ->options(['disponivel' => 'Disponível', 'reservado' => 'Reservado', 'vendido' => 'Vendido', 'indisponivel' => 'Indisponível']),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('projeto'))
            ->columns([
                TextColumn::make('identificacao')->label('Identificação')->searchable()->sortable()->weight('medium'),
                TextColumn::make('projeto.nome')->label('Projeto')->searchable()->sortable()->placeholder('—')->badge()->color(fn ($record) => $record->projeto->cor ?? 'gray'),
                TextColumn::make('andar')->label('Andar')->sortable()->placeholder('—'),
                TextColumn::make('tipologia')->label('Tipologia')->searchable()->sortable()->placeholder('—'),
                TextColumn::make('tipo')->label('Tipo')->searchable()->sortable()->placeholder('—'),
                TextColumn::make('area')->label('Área Privativa (m²)')->numeric(decimalPlaces: 2)->sortable()->placeholder('—'),
                TextColumn::make('valor_tabela')->label('Valor de Tabela')->money('BRL')->alignEnd()->sortable(),
                TextColumn::make('valor_avaliado')->label('Valor Avaliado')->money('BRL')->alignEnd()->sortable()->placeholder('—'),
                TextColumn::make('vaga_garagem')->label('Vaga')->sortable()->placeholder('—'),
                TextColumn::make('status')->label('Status')->badge()->sortable()
                    ->colors(['success' => 'disponivel', 'warning' => 'reservado', 'gray' => 'vendido', 'secondary' => 'indisponivel'])
                    ->formatStateUsing(fn ($state) => ['disponivel' => 'Disponível', 'reservado' => 'Reservado', 'vendido' => 'Vendido', 'indisponivel' => 'Indisponível'][$state] ?? $state),
            ])
            ->filters([
                SelectFilter::make('status')->options(['disponivel' => 'Disponível', 'reservado' => 'Reservado', 'vendido' => 'Vendido', 'indisponivel' => 'Indisponível']),
                SelectFilter::make('projeto_id')->label('Projeto')->options(Projeto::pluck('nome', 'id'))->searchable(),
            ])
            ->recordActions([
                EditAction::make()->slideOver()->modalWidth('4xl')->iconButton(),
                DeleteAction::make()->iconButton(),
            ])
            ->toolbarActions([
                BulkAction::make('editarLote')
                    ->label('Editar em lote')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->schema([
                        Select::make('status')->label('Status')->native(false)
                            ->options(['disponivel' => 'Disponível', 'reservado' => 'Reservado', 'vendido' => 'Vendido', 'indisponivel' => 'Indisponível']),
                        TextInput::make('andar')->label('Andar')->maxLength(100),
                        TextInput::make('tipologia')->label('Tipologia')->maxLength(100),
                        TextInput::make('vaga_garagem')->label('Vaga de Garagem')->maxLength(50),
                        TextInput::make('valor_tabela')->label('Valor de Tabela')->numeric()->prefix('R$')->step(0.01),
                        TextInput::make('valor_avaliado')->label('Valor Avaliado')->numeric()->prefix('R$')->step(0.01),
                    ])
                    ->action(function (array $data, $records) {
                        $update = array_filter($data, fn ($v) => $v !== null && $v !== '');
                        foreach ($records as $record) {
                            $record->update($update);
                        }
                        Notification::make()->title('Unidades atualizadas: '.count($records))->success()->send();
                    })
                    ->deselectRecordsAfterCompletion(),
                BulkActionGroup::make([DeleteBulkAction::make()])
            ])
            ->defaultSort('identificacao')->dragReorderableColumns()->stickableColumns();
    }

    public static function getPages(): array
    {
        // Só index: criar/editar abrem em modal lateral, como nas outras tabelas
        return ['index' => ListUnidades::route('/')];
    }
}
