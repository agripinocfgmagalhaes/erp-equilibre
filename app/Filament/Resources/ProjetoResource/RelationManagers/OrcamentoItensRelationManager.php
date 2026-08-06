<?php
namespace App\Filament\Resources\ProjetoResource\RelationManagers;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\RawJs;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use App\Models\FasePadrao;
use App\Models\Servico;

class OrcamentoItensRelationManager extends RelationManager
{
    protected static string $relationship = 'orcamentoItens';
    protected static ?string $title = 'Orçamento';
    protected static ?string $modelLabel = 'Item de Orçamento';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('fase_padrao_id')->label('Fase')->native(false)->searchable()->required()
                ->options(fn () => FasePadrao::orderBy('ordem')->pluck('nome', 'id')),
            Select::make('servico_id')->label('Serviço (catálogo)')->native(false)->searchable()
                ->options(fn () => Servico::where('ativo', true)->pluck('nome', 'id'))
                ->afterStateUpdated(function ($state, callable $set) {
                    if (! $state) return;
                    $s = Servico::find($state);
                    if ($s) { $set('descricao', $s->nome); $set('unidade', $s->unidade_padrao); }
                })->live(),
            TextInput::make('descricao')->label('Descrição do Serviço')->required()->columnSpanFull(),
            TextInput::make('unidade')->label('Unidade')->maxLength(10),
            TextInput::make('quantidade')->label('Quantidade')->numeric()->required(),
            TextInput::make('valor_unitario')->label('Valor Unitário')->prefix('R$')->required()
                ->mask(RawJs::make('$money($input, \',\', \'.\')'))->extraInputAttributes(['type' => 'text'])
                ->dehydrateStateUsing(fn ($state) => $state !== null ? (float) str_replace(['.', ','], ['', '.'], $state) : null)
                ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, 2, ',', '.') : null),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('descricao')
            ->modifyQueryUsing(fn ($query) => $query->with('fasePadrao'))
            ->columns([
                TextColumn::make('fasePadrao.nome')->label('Fase')->sortable()->searchable()->placeholder('—'),
                TextColumn::make('descricao')->label('Serviço')->searchable()->sortable()->weight('medium'),
                TextColumn::make('unidade')->label('Unid.')->placeholder('—'),
                TextColumn::make('quantidade')->label('Quantidade')->numeric(decimalPlaces: 2)->alignEnd()->sortable(),
                TextColumn::make('valor_unitario')->label('Valor Unit.')->money('BRL')->alignEnd()->sortable(),
                TextColumn::make('valor_total')->label('Total')->money('BRL')->alignEnd()->sortable()->weight('medium'),
            ])
            ->filters([SelectFilter::make('fase_padrao_id')->label('Fase')->options(fn () => FasePadrao::orderBy('ordem')->pluck('nome', 'id'))])
            ->headerActions([CreateAction::make()->label('+ Item')->slideOver()])
            ->recordActions([
                EditAction::make()->slideOver()->iconButton(),
                DeleteAction::make()->iconButton(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('id');
    }
}
