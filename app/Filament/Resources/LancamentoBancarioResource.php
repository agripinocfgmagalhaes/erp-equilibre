<?php
namespace App\Filament\Resources;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\LancamentoBancarioResource\Pages\ListLancamentosBancarios;
use App\Filament\Resources\LancamentoBancarioResource\Pages\CreateLancamentoBancario;
use App\Filament\Resources\LancamentoBancarioResource\Pages\EditLancamentoBancario;
use App\Filament\Resources\LancamentoBancarioResource\Pages;
use App\Models\LancamentoBancario;
use App\Models\ContaBancaria;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
class LancamentoBancarioResource extends Resource
{
    protected static ?string $model = LancamentoBancario::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Extrato Bancário';
    protected static string | \UnitEnum | null $navigationGroup = 'Financeiro';
    protected static ?int $navigationSort = 3;
    protected static ?string $slug = 'extrato-bancario';
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('conta_bancaria_id')->label('Conta Bancária')->options(ContaBancaria::where('ativo', true)->pluck('nome', 'id'))->searchable()->native(false)->required(),
            Select::make('tipo')->label('Tipo')->native(false)->required()->options(['entrada' => 'Entrada', 'saida' => 'Saída']),
            TextInput::make('descricao')->label('Descrição')->required()->maxLength(200)->columnSpanFull(),
            TextInput::make('valor')->label('Valor')->numeric()->prefix('R$')->step(0.01)->required(),
            DatePicker::make('data')->label('Data')->native(false)->displayFormat('d/m/Y')->default(now())->required(),
            Textarea::make('observacoes')->label('Observações')->rows(2)->columnSpanFull(),
        ])->columns(2);
    }
    public static function table(Table $table): Table
    {
        return $table->modifyQueryUsing(fn (Builder $query) => $query->with('contaBancaria'))
            ->columns([
                TextColumn::make('data')->label('Data')->date('d/m/Y')->sortable(),
                TextColumn::make('contaBancaria.nome')->label('Conta')->sortable(),
                TextColumn::make('descricao')->label('Descrição')->searchable()->limit(50),
                TextColumn::make('origem')->label('Origem')->badge()
                    ->colors(['gray' => 'manual', 'warning' => 'conta_pagar', 'success' => 'conta_receber'])
                    ->formatStateUsing(fn ($state) => match($state) { 'manual' => 'Manual', 'conta_pagar' => 'CP', 'conta_receber' => 'CR', default => $state }),
                TextColumn::make('tipo')->label('Tipo')->badge()
                    ->colors(['success' => 'entrada', 'danger' => 'saida'])
                    ->formatStateUsing(fn ($state) => $state === 'entrada' ? '▲ Entrada' : '▼ Saída'),
                TextColumn::make('valor')->label('Valor')->money('BRL')->sortable()->color(fn ($record) => $record->tipo === 'entrada' ? 'success' : 'danger'),
                TextColumn::make('saldo_acumulado')->label('Saldo')
                    ->getStateUsing(function ($record, $livewire) {
                        static $saldos = null;
                        static $lastKey = null;
                        $pageKey = $livewire->getTablePage().'_'.md5(json_encode($livewire->tableFilters ?? []));
                        if ($saldos === null || $lastKey !== $pageKey) {
                            $lastKey = $pageKey;
                            $filters = $livewire->tableFilters ?? [];
                            $contaId = $filters['conta_bancaria_id']['value'] ?? null;
                            $saldoInicial = $contaId ? (float) ContaBancaria::find($contaId)?->saldo_inicial : 0;
                            $query = LancamentoBancario::query()->orderBy('data')->orderBy('id');
                            if ($contaId) $query->where('conta_bancaria_id', $contaId);
                            $acumulado = $saldoInicial;
                            $saldos = [];
                            foreach ($query->get() as $l) {
                                $acumulado += $l->tipo === 'entrada' ? (float) $l->valor : -(float) $l->valor;
                                $saldos[$l->id] = $acumulado;
                            }
                        }
                        return isset($saldos[$record->id]) ? 'R$ '.number_format($saldos[$record->id], 2, ',', '.') : '—';
                    })
                    ->color(function ($record, $livewire) {
                        static $saldos2 = null;
                        $filters = $livewire->tableFilters ?? [];
                        $contaId = $filters['conta_bancaria_id']['value'] ?? null;
                        if ($saldos2 === null) {
                            $saldoInicial = $contaId ? (float) ContaBancaria::find($contaId)?->saldo_inicial : 0;
                            $query = LancamentoBancario::query()->orderBy('data')->orderBy('id');
                            if ($contaId) $query->where('conta_bancaria_id', $contaId);
                            $acumulado = $saldoInicial; $saldos2 = [];
                            foreach ($query->get() as $l) { $acumulado += $l->tipo === 'entrada' ? (float) $l->valor : -(float) $l->valor; $saldos2[$l->id] = $acumulado; }
                        }
                        return ($saldos2[$record->id] ?? 0) >= 0 ? 'success' : 'danger';
                    }),
            ])
            ->filters([
                SelectFilter::make('conta_bancaria_id')->label('Conta Bancária')->options(ContaBancaria::pluck('nome', 'id'))->searchable()->default(fn () => ContaBancaria::where('ativo', true)->value('id')),
                SelectFilter::make('tipo')->options(['entrada' => 'Entrada', 'saida' => 'Saída']),
                Filter::make('periodo')->schema([
                    DatePicker::make('data_de')->label('De')->native(false)->displayFormat('d/m/Y'),
                    DatePicker::make('data_ate')->label('Até')->native(false)->displayFormat('d/m/Y'),
                ])->query(fn ($query, array $data) => $query->when($data['data_de'], fn ($q, $v) => $q->whereDate('data', '>=', $v))->when($data['data_ate'], fn ($q, $v) => $q->whereDate('data', '<=', $v)))->columns(2),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->recordActions([
                EditAction::make()->slideOver()->visible(fn (LancamentoBancario $record) => $record->origem === 'manual'),
                DeleteAction::make()->visible(fn (LancamentoBancario $record) => $record->origem === 'manual'),
            ])
            ->toolbarActions([])->defaultSort('data', 'asc');
    }
    public static function getPages(): array
    {
        return ['index' => ListLancamentosBancarios::route('/'), 'create' => CreateLancamentoBancario::route('/create'), 'edit' => EditLancamentoBancario::route('/{record}/edit')];
    }
}
