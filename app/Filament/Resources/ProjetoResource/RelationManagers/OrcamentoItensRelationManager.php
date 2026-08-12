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
use App\Models\OrcamentoItem;

class OrcamentoItensRelationManager extends RelationManager
{
    protected static string $relationship = 'orcamentoItens';
    protected static ?string $title = 'Orçamento';
    protected static ?string $modelLabel = 'Item de Orçamento';

    private static function moneyInput(string $name, string $label): TextInput
    {
        return TextInput::make($name)->label($label)->prefix('R$')->required()->default(0)
            ->mask(RawJs::make('$money($input, \',\', \'.\')'))->extraInputAttributes(['type' => 'text'])
            ->dehydrateStateUsing(fn ($state) => $state !== null ? (float) str_replace(['.', ','], ['', '.'], $state) : 0)
            ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, 2, ',', '.') : '0,00');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('fase_padrao_id')->label('Fase')->native(false)->searchable()->required()
                ->options(fn () => FasePadrao::orderBy('ordem')->pluck('nome', 'id'))
                ->columnSpanFull(),
            Select::make('servico_id')->label('Serviço (catálogo)')->native(false)->searchable()
                ->options(fn () => Servico::where('ativo', true)->pluck('nome', 'id'))
                ->afterStateUpdated(function ($state, callable $set) {
                    if (! $state) return;
                    $s = Servico::find($state);
                    if ($s) { $set('descricao', $s->nome); $set('unidade', $s->unidade_padrao); }
                })->live()
                ->columnSpanFull(),
            TextInput::make('descricao')->label('Descrição do Serviço')->required()->columnSpanFull(),
            TextInput::make('unidade')->label('Unidade')->maxLength(10),
            TextInput::make('quantidade')->label('Quantidade')->numeric()->required(),
            self::moneyInput('material_unitario', 'Material (unit.)'),
            self::moneyInput('mdo_unitario', 'Mão de Obra (unit.)'),
            self::moneyInput('outros_unitario', 'Outros (unit.)'),
        ])->columns(3);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('descricao')
            ->modifyQueryUsing(fn ($query) => $query->with(['fasePadrao', 'ordemServicoItens.medicaoItens']))
            ->columns([
                TextColumn::make('fasePadrao.nome')->label('Fase')->sortable()->searchable()->placeholder('—'),
                TextColumn::make('descricao')->label('Serviço')->searchable()->sortable()->weight('medium'),
                TextColumn::make('unidade')->label('Unid.')->placeholder('—'),
                TextColumn::make('quantidade')->label('Qtd.')->numeric(decimalPlaces: 2)->alignEnd()->sortable(),
                TextColumn::make('valor_total')->label('Orçado')->money('BRL')->alignEnd()->sortable(),
                TextColumn::make('realizado')->label('Realizado')->alignEnd()
                    ->state(fn (OrcamentoItem $record) => 'R$ '.number_format($record->quantidadeMedidaAcumulada() * (float) $record->valor_unitario, 2, ',', '.')),
                TextColumn::make('percentual_execucao')->label('% Exec.')->alignEnd()->badge()
                    ->state(function (OrcamentoItem $record) {
                        if ((float) $record->valor_total <= 0) return '—';
                        $realizado = $record->quantidadeMedidaAcumulada() * (float) $record->valor_unitario;
                        return round(($realizado / (float) $record->valor_total) * 100, 1).'%';
                    })
                    ->color(function (OrcamentoItem $record) {
                        if ((float) $record->valor_total <= 0) return 'gray';
                        $realizado = $record->quantidadeMedidaAcumulada() * (float) $record->valor_unitario;
                        $pct = ($realizado / (float) $record->valor_total) * 100;
                        return match (true) { $pct >= 100 => 'success', $pct >= 50 => 'info', $pct > 0 => 'warning', default => 'gray' };
                    }),
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
