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
use Filament\Tables\Grouping\Group;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
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
            DatePicker::make('data')->label('Data')->displayFormat('d/m/Y')->default(now())->required(),
            Textarea::make('observacoes')->label('Observações')->rows(2)->columnSpanFull(),
        ])->columns(2);
    }
    public static function table(Table $table): Table
    {
        $saldosPorContaEData = LancamentoBancario::query()
            ->orderBy('conta_bancaria_id')->orderBy('data')->orderBy('id')
            ->get()
            ->groupBy('conta_bancaria_id')
            ->flatMap(function ($lancamentos, $contaId) {
                $acumulado = (float) (ContaBancaria::find($contaId)?->saldo_inicial ?? 0);
                $porDia = [];
                foreach ($lancamentos as $l) {
                    $acumulado += $l->tipo === 'entrada' ? (float) $l->valor : -(float) $l->valor;
                    $porDia[$contaId.'|'.$l->data->toDateString()] = $acumulado;
                }
                return $porDia;
            })->toArray();
        return $table->modifyQueryUsing(fn (Builder $query) => $query->with('contaBancaria'))
            ->columns([
                TextColumn::make('contaBancaria.nome')->label('Conta')->sortable(),
                TextColumn::make('descricao')->sortable()->label('Descrição')->searchable()->limit(50),
                TextColumn::make('origem')->sortable()->label('Origem')->badge()
                    ->colors(['gray' => 'manual', 'warning' => 'conta_pagar', 'success' => 'conta_receber', 'info' => 'transferencia'])
                    ->formatStateUsing(fn ($state) => match($state) { 'manual' => 'Manual', 'conta_pagar' => 'CP', 'conta_receber' => 'CR', 'transferencia' => 'Transferência', default => $state }),
                TextColumn::make('tipo')->sortable()->label('Tipo')->badge()
                    ->colors(['success' => 'entrada', 'danger' => 'saida'])
                    ->formatStateUsing(fn ($state) => $state === 'entrada' ? '▲ Entrada' : '▼ Saída'),
                TextColumn::make('valor')->label('Valor')->money('BRL')->alignEnd()->sortable()->color(fn ($record) => $record->tipo === 'entrada' ? 'success' : 'danger')
                    ->summarize(
                        Summarizer::make()->label('Saldo do dia')
                            ->using(function ($query) use ($saldosPorContaEData) {
                                $first = (clone $query)->first();
                                if (! $first) return null;
                                $key = $first->conta_bancaria_id.'|'.$first->data->toDateString();
                                return $saldosPorContaEData[$key] ?? 0;
                            })
                            ->formatStateUsing(fn ($state) => 'R$ '.number_format((float) $state, 2, ',', '.'))
                    ),
            ])
            ->groups([
                Group::make('data')->label('Data')
                    ->getTitleFromRecordUsing(fn ($record) => $record->data->format('d/m/Y')),
            ])
            ->defaultGroup('data')
            ->filters([
                SelectFilter::make('conta_bancaria_id')->label('Conta Bancária')->options(ContaBancaria::pluck('nome', 'id'))->searchable()->default(fn () => ContaBancaria::where('ativo', true)->value('id')),
                SelectFilter::make('tipo')->options(['entrada' => 'Entrada', 'saida' => 'Saída']),
                Filter::make('periodo')->schema([
                    DatePicker::make('data_de')->label('De')->displayFormat('d/m/Y'),
                    DatePicker::make('data_ate')->label('Até')->displayFormat('d/m/Y'),
                ])->query(fn ($query, array $data) => $query->when($data['data_de'], fn ($q, $v) => $q->whereDate('data', '>=', $v))->when($data['data_ate'], fn ($q, $v) => $q->whereDate('data', '<=', $v)))->columns(2),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->recordActions([
                EditAction::make()->slideOver()->iconButton()->visible(fn (LancamentoBancario $record) => $record->origem === 'manual'),
                DeleteAction::make()->iconButton()->visible(fn (LancamentoBancario $record) => $record->origem === 'manual'),
            ])
            ->toolbarActions([])->defaultSort('data', 'asc')->dragReorderableColumns()->stickableColumns();
    }
    public static function getPages(): array
    {
        return ['index' => ListLancamentosBancarios::route('/')];
    }
}
